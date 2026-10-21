<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Kehadiran;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Kegiatan::sinkronStatus();

        $user = $request->user();

        $totalKegiatan = Kegiatan::dapatDilihat($user)->count();
        $kegiatanHariIni = Kegiatan::dapatDilihat($user)->whereDate('tanggal', today())->count();
        $kegiatanBerjalan = Kegiatan::dapatDilihat($user)->whereIn('status', ['aktif', 'selesai'])->count();

        $totalKehadiran = Kehadiran::query()
            ->where('status', 'tercatat')
            ->whereHas('kegiatan', fn ($q) => $q->dapatDilihat($user))
            ->count();

        $rataRata = $kegiatanBerjalan > 0 ? round($totalKehadiran / $kegiatanBerjalan, 1) : 0;

        // Grafik batang: kehadiran dan jumlah kegiatan 6 bulan terakhir
        $awal = today()->startOfMonth()->subMonths(5);
        $akhir = today()->endOfMonth();

        $barisHadir = Kehadiran::query()
            ->join('kegiatan', 'kegiatan.id_kegiatan', '=', 'kehadiran.id_kegiatan')
            ->where('kehadiran.status', 'tercatat')
            ->whereBetween('kegiatan.tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->when($user->isOperator(), fn ($q) => $q->where('kegiatan.id_user', $user->id_user))
            ->pluck('kegiatan.tanggal');

        $barisKegiatan = Kegiatan::dapatDilihat($user)
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->whereIn('status', ['aktif', 'selesai'])
            ->pluck('tanggal');

        $grafik = ['label' => [], 'hadir' => [], 'kegiatan' => []];
        for ($i = 0; $i < 6; $i++) {
            $bulan = $awal->copy()->addMonths($i);
            $kunci = $bulan->format('Y-m');
            $grafik['label'][] = $bulan->translatedFormat('M Y');
            $grafik['hadir'][] = $barisHadir->filter(fn ($t) => substr((string) $t, 0, 7) === $kunci)->count();
            $grafik['kegiatan'][] = $barisKegiatan->filter(fn ($t) => substr((string) $t, 0, 7) === $kunci)->count();
        }

        $kegiatanAktif = Kegiatan::dapatDilihat($user)
            ->with('kategori')
            ->denganJumlahHadir()
            ->where('status', 'aktif')
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->limit(5)->get();

        $kegiatanTerbaru = Kegiatan::dapatDilihat($user)
            ->with('kategori')
            ->denganJumlahHadir()
            ->orderByDesc('tanggal')->orderByDesc('jam_mulai')
            ->limit(6)->get();

        return view('dashboard', compact(
            'totalKegiatan', 'totalKehadiran', 'kegiatanHariIni', 'rataRata',
            'grafik', 'kegiatanAktif', 'kegiatanTerbaru'
        ));
    }
}
