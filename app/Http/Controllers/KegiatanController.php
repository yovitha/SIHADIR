<?php

namespace App\Http\Controllers;

use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    /** Perpindahan status yang diizinkan (AB-02, AB-09). */
    private const ALUR_STATUS = [
        'draft' => ['aktif', 'dibatalkan'],
        'aktif' => ['selesai', 'dibatalkan', 'draft'],
        'selesai' => ['aktif'],
        'dibatalkan' => ['draft'],
    ];

    public function index(Request $request)
    {
        Kegiatan::sinkronStatus();

        $user = $request->user();

        $kegiatan = Kegiatan::dapatDilihat($user)
            ->with(['kategori', 'user'])
            ->denganJumlahHadir()
            ->when($request->filled('q'), fn ($q) => $q->where('nama_kegiatan', 'like', '%'.$request->q.'%'))
            ->when($request->filled('kategori'), fn ($q) => $q->where('id_kategori', $request->kategori))
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('tanggal', $request->tanggal))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('tanggal')->orderByDesc('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        $kategori = KategoriKegiatan::orderBy('nama_kategori')->get();

        return view('kegiatan.index', compact('kegiatan', 'kategori'));
    }

    public function create()
    {
        return view('kegiatan.form', [
            'kegiatan' => new Kegiatan(['tanda_tangan_wajib' => true, 'penyelenggara' => config('sihadir.instansi')]),
            'kategori' => KategoriKegiatan::orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $kegiatan = Kegiatan::create($data + [
            'id_user' => $request->user()->id_user,
            'kode_unik' => Kegiatan::buatKodeUnik(),
            'status' => 'draft',
        ]);

        LogAktivitas::catat('Membuat kegiatan: '.$kegiatan->nama_kegiatan);

        return redirect()->route('kegiatan.show', $kegiatan)
            ->with('sukses', 'Kegiatan berhasil dibuat. Aktifkan presensi agar peserta dapat mengisi daftar hadir.');
    }

    public function show(Request $request, Kegiatan $kegiatan)
    {
        Kegiatan::sinkronStatus();
        $kegiatan->refresh();
        abort_unless($kegiatan->bisaDilihatOleh($request->user()), 403);

        $kegiatan->load(['kategori', 'user']);

        $kehadiran = $kegiatan->kehadiran()
            ->with('peserta.instansi')
            ->orderByDesc('waktu_hadir')
            ->get();

        $jumlahHadir = $kehadiran->where('status', 'tercatat')->count();
        $bisaKelola = $kegiatan->bisaDikelolaOleh($request->user());

        return view('kegiatan.show', compact('kegiatan', 'kehadiran', 'jumlahHadir', 'bisaKelola'));
    }

    public function edit(Request $request, Kegiatan $kegiatan)
    {
        abort_unless($kegiatan->bisaDikelolaOleh($request->user()), 403);

        return view('kegiatan.form', [
            'kegiatan' => $kegiatan,
            'kategori' => KategoriKegiatan::orderBy('nama_kategori')->get(),
        ]);
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        abort_unless($kegiatan->bisaDikelolaOleh($request->user()), 403);

        $kegiatan->update($this->validasi($request));
        LogAktivitas::catat('Mengubah kegiatan: '.$kegiatan->nama_kegiatan);

        return redirect()->route('kegiatan.show', $kegiatan)->with('sukses', 'Data kegiatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Kegiatan $kegiatan)
    {
        abort_unless($kegiatan->bisaDikelolaOleh($request->user()), 403);

        // Kegiatan yang sudah memiliki kehadiran tidak dihapus permanen, tetapi dibatalkan.
        if ($kegiatan->kehadiran()->exists()) {
            $kegiatan->update(['status' => 'dibatalkan']);
            LogAktivitas::catat('Membatalkan kegiatan: '.$kegiatan->nama_kegiatan);

            return redirect()->route('kegiatan.index')
                ->with('sukses', 'Kegiatan sudah memiliki data kehadiran sehingga statusnya diubah menjadi Dibatalkan.');
        }

        $nama = $kegiatan->nama_kegiatan;
        $kegiatan->delete();
        LogAktivitas::catat('Menghapus kegiatan: '.$nama);

        return redirect()->route('kegiatan.index')->with('sukses', 'Kegiatan berhasil dihapus.');
    }

    public function ubahStatus(Request $request, Kegiatan $kegiatan)
    {
        abort_unless($kegiatan->bisaDikelolaOleh($request->user()), 403);

        $baru = $request->validate(['status' => ['required', 'in:'.implode(',', Kegiatan::STATUS)]])['status'];

        if (! in_array($baru, self::ALUR_STATUS[$kegiatan->status] ?? [], true)) {
            return back()->with('galat', 'Status kegiatan tidak dapat diubah dari '.$kegiatan->status_label.' ke '.ucfirst($baru).'.');
        }

        if ($baru === 'aktif' && $kegiatan->selesaiPada()->addMinutes((int) config('sihadir.toleransi_menit'))->lt(now())) {
            return back()->with('galat', 'Waktu kegiatan sudah lewat. Ubah tanggal atau jam kegiatan terlebih dahulu sebelum mengaktifkan presensi.');
        }

        $kegiatan->update(['status' => $baru]);
        LogAktivitas::catat('Mengubah status kegiatan "'.$kegiatan->nama_kegiatan.'" menjadi '.$baru);

        $pesan = match ($baru) {
            'aktif' => 'Presensi dibuka. Peserta sekarang dapat mengisi daftar hadir.',
            'selesai' => 'Presensi ditutup. Kegiatan ditandai selesai.',
            'dibatalkan' => 'Kegiatan dibatalkan.',
            default => 'Status kegiatan dikembalikan ke Draft.',
        };

        return back()->with('sukses', $pesan);
    }

    /** Tampilan QR Code layar penuh untuk proyektor. */
    public function qr(Request $request, Kegiatan $kegiatan)
    {
        abort_unless($kegiatan->bisaDilihatOleh($request->user()), 403);

        return view('kegiatan.qr', compact('kegiatan'));
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:150'],
            'id_kategori' => ['required', 'exists:kategori_kegiatan,id_kategori'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi' => ['required', 'string', 'max:150'],
            'penyelenggara' => ['nullable', 'string', 'max:100'],
        ], [
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $data['tanda_tangan_wajib'] = $request->boolean('tanda_tangan_wajib');

        return $data;
    }
}
