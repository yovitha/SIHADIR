<?php

namespace App\Http\Controllers;

use App\Models\Instansi;
use App\Models\Kegiatan;
use App\Models\Kehadiran;
use App\Models\LogAktivitas;
use App\Models\Peserta;
use App\Services\PresensiService;
use Illuminate\Http\Request;

class KehadiranController extends Controller
{
    public function pantau(Request $request, Kegiatan $kegiatan)
    {
        Kegiatan::sinkronStatus();
        $kegiatan->refresh();
        abort_unless($kegiatan->bisaDilihatOleh($request->user()), 403);

        $kegiatan->load('kategori');
        $kehadiran = $this->daftar($kegiatan);
        $bisaKelola = $kegiatan->bisaDikelolaOleh($request->user());

        $peserta = $bisaKelola
            ? Peserta::with('instansi')->orderBy('nama')->get()
            : collect();

        return view('kegiatan.pantau', compact('kegiatan', 'kehadiran', 'bisaKelola', 'peserta'));
    }

    /** Dipanggil berkala oleh halaman pantau agar daftar hadir diperbarui otomatis (KF-10). */
    public function data(Request $request, Kegiatan $kegiatan)
    {
        abort_unless($kegiatan->bisaDilihatOleh($request->user()), 403);

        $kehadiran = $this->daftar($kegiatan);
        $bisaKelola = $kegiatan->bisaDikelolaOleh($request->user());

        return response()->json([
            'hadir' => $kehadiran->where('status', 'tercatat')->count(),
            'status' => $kegiatan->status_label,
            'html' => view('kegiatan._baris-kehadiran', compact('kegiatan', 'kehadiran', 'bisaKelola'))->render(),
        ]);
    }

    /** Operator menambah kehadiran secara manual bagi peserta yang tidak dapat memakai ponsel. */
    public function tambahManual(Request $request, Kegiatan $kegiatan, PresensiService $layanan)
    {
        abort_unless($kegiatan->bisaDikelolaOleh($request->user()), 403);

        if ($kegiatan->status === 'dibatalkan') {
            return back()->with('galat', 'Kegiatan yang dibatalkan tidak dapat menerima kehadiran.');
        }

        $data = $request->validate([
            'id_peserta' => ['nullable', 'exists:peserta,id_peserta'],
            'nama' => ['required_without:id_peserta', 'nullable', 'string', 'max:100'],
            'instansi' => ['required_without:id_peserta', 'nullable', 'string', 'max:150'],
            'jabatan' => ['required_without:id_peserta', 'nullable', 'string', 'max:100'],
        ], [
            'nama.required_without' => 'Pilih peserta terdaftar atau isi nama tamu.',
            'instansi.required_without' => 'Instansi wajib diisi untuk tamu.',
            'jabatan.required_without' => 'Jabatan wajib diisi untuk tamu.',
        ]);

        $idPeserta = ! empty($data['id_peserta']) ? (int) $data['id_peserta'] : null;

        if ($layanan->cariGanda($kegiatan, $idPeserta, $data['nama'] ?? null, $data['instansi'] ?? null)) {
            return back()->withInput()->with('galat', 'Peserta tersebut sudah tercatat hadir pada kegiatan ini.');
        }

        $kehadiran = $layanan->catat($kegiatan, [
            'id_peserta' => $idPeserta,
            'nama' => $data['nama'] ?? null,
            'instansi' => $data['instansi'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'ip' => $request->ip(),
            'keterangan' => 'Ditambahkan manual oleh '.$request->user()->nama_lengkap,
        ]);

        LogAktivitas::catat('Menambah kehadiran manual "'.$kehadiran->nama_tampil.'" pada kegiatan "'.$kegiatan->nama_kegiatan.'"');

        return back()->with('sukses', 'Kehadiran '.$kehadiran->nama_tampil.' berhasil ditambahkan.');
    }

    public function batalkan(Request $request, Kehadiran $kehadiran)
    {
        $kegiatan = $kehadiran->kegiatan;
        abort_unless($kegiatan->bisaDikelolaOleh($request->user()), 403);

        $kehadiran->update([
            'status' => 'dibatalkan',
            'keterangan' => 'Dibatalkan oleh '.$request->user()->nama_lengkap,
        ]);

        LogAktivitas::catat('Membatalkan kehadiran "'.$kehadiran->nama_tampil.'" pada kegiatan "'.$kegiatan->nama_kegiatan.'"');

        return back()->with('sukses', 'Kehadiran '.$kehadiran->nama_tampil.' dibatalkan.');
    }

    private function daftar(Kegiatan $kegiatan)
    {
        return $kegiatan->kehadiran()
            ->with('peserta.instansi')
            ->orderByDesc('waktu_hadir')
            ->get();
    }
}
