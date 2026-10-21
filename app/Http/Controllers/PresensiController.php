<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Peserta;
use App\Services\PresensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Halaman presensi publik: peserta tidak perlu login (KF-06). */
class PresensiController extends Controller
{
    public function form(Request $request, string $kode)
    {
        $kegiatan = $this->cariKegiatan($kode);

        if ($pesan = $this->alasanTutup($kegiatan)) {
            return response()->view('presensi.tutup', compact('kegiatan', 'pesan'));
        }

        $sudahHadir = config('sihadir.batasi_perangkat') && $request->cookie($this->namaCookie($kegiatan));

        return view('presensi.form', compact('kegiatan', 'sudahHadir'));
    }

    public function simpan(Request $request, string $kode, PresensiService $layanan)
    {
        $kegiatan = $this->cariKegiatan($kode);

        if ($pesan = $this->alasanTutup($kegiatan)) {
            return response()->view('presensi.tutup', compact('kegiatan', 'pesan'));
        }

        if (config('sihadir.batasi_perangkat') && $request->cookie($this->namaCookie($kegiatan))) {
            return back()->withErrors(['nama' => 'Perangkat ini sudah dipakai untuk mengisi daftar hadir kegiatan ini.'])->withInput();
        }

        $data = $request->validate([
            'id_peserta' => ['nullable', 'exists:peserta,id_peserta'],
            'nama' => ['required', 'string', 'max:100'],
            'instansi' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'tanda_tangan' => [Rule::requiredIf($kegiatan->tanda_tangan_wajib), 'nullable', 'string'],
        ], [
            'tanda_tangan.required' => 'Silakan bubuhkan tanda tangan pada kotak yang tersedia.',
            'no_hp.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, +, - dan tanda kurung.',
        ]);

        // Peserta terdaftar: data diambil dari basis data agar penulisan seragam.
        $idPeserta = null;
        if (! empty($data['id_peserta'])) {
            $peserta = Peserta::with('instansi')->find($data['id_peserta']);
            if ($peserta) {
                $idPeserta = $peserta->id_peserta;
                $data['nama'] = $peserta->nama;
                $data['instansi'] = $peserta->instansi->nama_instansi;
                $data['jabatan'] = $peserta->jabatan;
            }
        }

        if ($layanan->cariGanda($kegiatan, $idPeserta, $data['nama'], $data['instansi'])) {
            return back()->withErrors(['nama' => 'Anda sudah tercatat hadir pada kegiatan ini.'])->withInput();
        }

        $pathTtd = null;
        if (! empty($data['tanda_tangan'])) {
            $biner = $this->bacaTandaTangan($data['tanda_tangan']);
            if ($biner === null) {
                return back()->withErrors(['tanda_tangan' => 'Tanda tangan tidak valid atau melebihi '.config('sihadir.maks_tanda_tangan_kb').' KB. Silakan ulangi.'])->withInput();
            }

            $pathTtd = 'tanda-tangan/'.$kegiatan->id_kegiatan.'/'.Str::uuid().'.png';
            Storage::disk('public')->put($pathTtd, $biner);
        }

        $kehadiran = $layanan->catat($kegiatan, [
            'id_peserta' => $idPeserta,
            'nama' => $data['nama'],
            'instansi' => $data['instansi'],
            'jabatan' => $data['jabatan'],
            'no_hp' => $data['no_hp'] ?? null,
            'tanda_tangan' => $pathTtd,
            'ip' => $request->ip(),
        ]);

        $respons = redirect()->route('presensi.berhasil', $kegiatan->kode_unik)->with('presensi', [
            'nama' => $kehadiran->nama_tampil,
            'waktu' => $kehadiran->waktu_hadir->translatedFormat('d F Y, H:i:s'),
        ]);

        if (config('sihadir.batasi_perangkat')) {
            $respons->withCookie(cookie($this->namaCookie($kegiatan), '1', 60 * 24));
        }

        return $respons;
    }

    public function berhasil(string $kode)
    {
        $kegiatan = $this->cariKegiatan($kode);
        $presensi = session('presensi');

        if (! $presensi) {
            return redirect()->route('presensi.form', $kegiatan->kode_unik);
        }

        return view('presensi.berhasil', compact('kegiatan', 'presensi'));
    }

    /** Pencarian peserta terdaftar untuk melengkapi formulir; hanya nama, jabatan, dan instansi yang dikirim. */
    public function cariPeserta(Request $request, string $kode)
    {
        $this->cariKegiatan($kode);

        $kata = trim((string) $request->query('q'));
        if (mb_strlen($kata) < 2) {
            return response()->json([]);
        }

        $kata = addcslashes($kata, '%_\\');

        return response()->json(
            Peserta::with('instansi')
                ->where('nama', 'like', '%'.$kata.'%')
                ->orderBy('nama')
                ->limit(8)
                ->get()
                ->map(fn (Peserta $p) => [
                    'id' => $p->id_peserta,
                    'nama' => $p->nama,
                    'jabatan' => $p->jabatan,
                    'instansi' => $p->instansi->nama_instansi,
                ])
        );
    }

    private function cariKegiatan(string $kode): Kegiatan
    {
        Kegiatan::sinkronStatus();

        return Kegiatan::with('kategori')
            ->where('kode_unik', strtoupper($kode))
            ->firstOrFail();
    }

    /** Mengembalikan alasan presensi ditolak, atau null jika presensi boleh dilakukan (AB-02, AB-03, AB-09). */
    private function alasanTutup(Kegiatan $kegiatan): ?string
    {
        return match (true) {
            $kegiatan->status === 'dibatalkan' => 'Kegiatan ini dibatalkan sehingga tidak menerima presensi.',
            $kegiatan->status === 'selesai' => 'Kegiatan ini sudah selesai dan presensi telah ditutup.',
            $kegiatan->status !== 'aktif' => 'Presensi untuk kegiatan ini belum dibuka oleh penyelenggara.',
            ! $kegiatan->dalamRentangWaktu() => 'Presensi hanya dapat diisi pada '.$kegiatan->tanggal->translatedFormat('d F Y')
                .', pukul '.$kegiatan->jam_mulai_hm.' – '.$kegiatan->jam_selesai_hm.' WITA.',
            default => null,
        };
    }

    private function namaCookie(Kegiatan $kegiatan): string
    {
        return 'hadir_'.$kegiatan->kode_unik;
    }

    /** Memvalidasi gambar tanda tangan (PNG data URI, maksimal 200 KB — AB-12). */
    private function bacaTandaTangan(string $dataUri): ?string
    {
        if (! preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#', $dataUri, $cocok)) {
            return null;
        }

        $biner = base64_decode($cocok[1], true);
        if ($biner === false || strlen($biner) < 300 || strlen($biner) > config('sihadir.maks_tanda_tangan_kb') * 1024) {
            return null;
        }

        $info = @getimagesizefromstring($biner);

        return ($info && $info[2] === IMAGETYPE_PNG) ? $biner : null;
    }
}
