<?php

namespace App\Http\Controllers;

use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kehadiran;
use App\Models\LogAktivitas;
use App\Models\Peserta;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        Kegiatan::sinkronStatus();
        $user = $request->user();

        $kegiatan = Kegiatan::dapatDilihat($user)
            ->with('kategori')
            ->denganJumlahHadir()
            ->whereIn('status', ['aktif', 'selesai'])
            ->orderByDesc('tanggal')->orderByDesc('jam_mulai')
            ->limit(12)->get();

        $kategori = KategoriKegiatan::orderBy('nama_kategori')->get();
        $peserta = Peserta::with('instansi')->orderBy('nama')->get();

        return view('laporan.index', compact('kegiatan', 'kategori', 'peserta'));
    }

    /* ----------------------- 1. Laporan per kegiatan ----------------------- */

    public function kegiatan(Request $request, Kegiatan $kegiatan)
    {
        abort_unless($kegiatan->bisaDilihatOleh($request->user()), 403);

        $kegiatan->load('kategori', 'user');
        $kehadiran = $kegiatan->kehadiran()
            ->where('status', 'tercatat')
            ->with('peserta.instansi')
            ->orderBy('waktu_hadir')
            ->get();

        $data = compact('kegiatan', 'kehadiran') + ['dicetakOleh' => $request->user()->nama_lengkap];
        $berkas = 'laporan-kegiatan-'.$kegiatan->id_kegiatan;

        LogAktivitas::catat('Membuka laporan kegiatan "'.$kegiatan->nama_kegiatan.'" ('.$request->query('format', 'html').')');

        return match ($request->query('format', 'html')) {
            'pdf' => $this->keluarkanPdf(view('laporan.kegiatan', $data + ['pdf' => true])->render(), $berkas.'.pdf'),
            'xlsx' => $this->excel($berkas.'.xlsx', 'Daftar Hadir', [
                ['DAFTAR HADIR KEGIATAN'],
                [config('sihadir.instansi')],
                [],
                ['Kegiatan', $kegiatan->nama_kegiatan],
                ['Kategori', $kegiatan->kategori->nama_kategori],
                ['Tanggal', $kegiatan->tanggal->translatedFormat('d F Y')],
                ['Waktu', $kegiatan->jam_mulai_hm.' - '.$kegiatan->jam_selesai_hm.' WITA'],
                ['Lokasi', $kegiatan->lokasi],
                ['Jumlah hadir', $kehadiran->count()],
                [],
            ], ['No', 'Nama', 'Instansi', 'Jabatan', 'Waktu Hadir', 'Keterangan'],
                $kehadiran->values()->map(fn (Kehadiran $k, $i) => [
                    $i + 1, $k->nama_tampil, $k->instansi_tampil, $k->jabatan_tampil,
                    $k->waktu_hadir->format('d/m/Y H:i:s'), $k->keterangan ?? '',
                ])->all()),
            default => view('laporan.kegiatan', $data + ['pdf' => false]),
        };
    }

    /* ----------------------- 2. Laporan per periode ------------------------ */

    public function periode(Request $request)
    {
        $user = $request->user();
        $v = $request->validate([
            'dari' => ['required', 'date'],
            'sampai' => ['required', 'date', 'after_or_equal:dari'],
            'id_kategori' => ['nullable', 'exists:kategori_kegiatan,id_kategori'],
        ]);

        $daftar = Kegiatan::dapatDilihat($user)
            ->with('kategori')
            ->denganJumlahHadir()
            ->whereIn('status', ['aktif', 'selesai'])
            ->whereBetween('tanggal', [$v['dari'], $v['sampai']])
            ->when(! empty($v['id_kategori']), fn ($q) => $q->where('id_kategori', $v['id_kategori']))
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->get();

        $totalHadir = (int) $daftar->sum('hadir_count');
        $rekapKategori = $daftar->groupBy(fn ($k) => $k->kategori->nama_kategori)
            ->map(fn ($g) => ['kegiatan' => $g->count(), 'hadir' => (int) $g->sum('hadir_count')])
            ->sortKeys();

        $kategoriDipilih = ! empty($v['id_kategori']) ? KategoriKegiatan::find($v['id_kategori'])?->nama_kategori : null;

        $data = [
            'daftar' => $daftar, 'totalHadir' => $totalHadir, 'rekapKategori' => $rekapKategori,
            'dari' => \Carbon\Carbon::parse($v['dari']), 'sampai' => \Carbon\Carbon::parse($v['sampai']),
            'kategoriDipilih' => $kategoriDipilih, 'dicetakOleh' => $user->nama_lengkap,
        ];
        $berkas = 'laporan-periode-'.$v['dari'].'-sd-'.$v['sampai'];

        LogAktivitas::catat("Membuka laporan periode {$v['dari']} s.d. {$v['sampai']}");

        return match ($request->query('format', 'html')) {
            'pdf' => $this->keluarkanPdf(view('laporan.periode', $data + ['pdf' => true])->render(), $berkas.'.pdf'),
            'xlsx' => $this->excel($berkas.'.xlsx', 'Rekap Periode', [
                ['REKAPITULASI KEHADIRAN KEGIATAN'],
                [config('sihadir.instansi')],
                ['Periode', $data['dari']->translatedFormat('d F Y').' s.d. '.$data['sampai']->translatedFormat('d F Y')],
                ['Kategori', $kategoriDipilih ?? 'Semua kategori'],
                ['Jumlah kegiatan', $daftar->count()],
                ['Total kehadiran', $totalHadir],
                [],
            ], ['No', 'Tanggal', 'Kegiatan', 'Kategori', 'Lokasi', 'Status', 'Jumlah Hadir'],
                $daftar->values()->map(fn (Kegiatan $k, $i) => [
                    $i + 1, $k->tanggal->format('d/m/Y'), $k->nama_kegiatan, $k->kategori->nama_kategori,
                    $k->lokasi, $k->status_label, $k->hadir_count,
                ])->all()),
            default => view('laporan.periode', $data + ['pdf' => false]),
        };
    }

    /* ----------------------- 3. Laporan per peserta ------------------------ */

    public function peserta(Request $request)
    {
        $user = $request->user();
        $v = $request->validate([
            'id_peserta' => ['required', 'exists:peserta,id_peserta'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $peserta = Peserta::with('instansi')->findOrFail($v['id_peserta']);

        $riwayat = Kehadiran::query()
            ->with('kegiatan.kategori')
            ->where('id_peserta', $peserta->id_peserta)
            ->where('status', 'tercatat')
            ->whereHas('kegiatan', function ($q) use ($user, $v) {
                $q->dapatDilihat($user)
                    ->when(! empty($v['dari']), fn ($w) => $w->whereDate('tanggal', '>=', $v['dari']))
                    ->when(! empty($v['sampai']), fn ($w) => $w->whereDate('tanggal', '<=', $v['sampai']));
            })
            ->get()
            ->sortBy(fn ($k) => $k->kegiatan->tanggal->format('Y-m-d').$k->kegiatan->jam_mulai)
            ->values();

        $data = [
            'peserta' => $peserta, 'riwayat' => $riwayat,
            'dari' => ! empty($v['dari']) ? \Carbon\Carbon::parse($v['dari']) : null,
            'sampai' => ! empty($v['sampai']) ? \Carbon\Carbon::parse($v['sampai']) : null,
            'dicetakOleh' => $user->nama_lengkap,
        ];
        $berkas = 'laporan-peserta-'.$peserta->id_peserta;

        LogAktivitas::catat('Membuka laporan peserta "'.$peserta->nama.'"');

        return match ($request->query('format', 'html')) {
            'pdf' => $this->keluarkanPdf(view('laporan.peserta', $data + ['pdf' => true])->render(), $berkas.'.pdf'),
            'xlsx' => $this->excel($berkas.'.xlsx', 'Riwayat Peserta', [
                ['RIWAYAT KEHADIRAN PESERTA'],
                [config('sihadir.instansi')],
                ['Nama', $peserta->nama],
                ['Jabatan', $peserta->jabatan],
                ['Instansi', $peserta->instansi->nama_instansi],
                ['Jumlah kegiatan dihadiri', $riwayat->count()],
                [],
            ], ['No', 'Tanggal', 'Kegiatan', 'Kategori', 'Lokasi', 'Waktu Hadir'],
                $riwayat->map(fn (Kehadiran $k, $i) => [
                    $i + 1, $k->kegiatan->tanggal->format('d/m/Y'), $k->kegiatan->nama_kegiatan,
                    $k->kegiatan->kategori->nama_kategori, $k->kegiatan->lokasi, $k->waktu_hadir->format('H:i:s'),
                ])->all()),
            default => view('laporan.peserta', $data + ['pdf' => false]),
        };
    }

    /* ------------------------------ Pembantu ------------------------------- */

    private function keluarkanPdf(string $html, string $nama, string $orientasi = 'portrait')
    {
        $opsi = new Options();
        $opsi->set('defaultFont', 'DejaVu Sans');
        $opsi->set('isRemoteEnabled', false);

        $pdf = new Dompdf($opsi);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', $orientasi);
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$nama.'"',
        ]);
    }

    /**
     * @param  array<int, array<int, mixed>>  $kepala  Baris judul/identitas di atas tabel
     * @param  array<int, string>  $kolom  Judul kolom tabel
     * @param  array<int, array<int, mixed>>  $baris  Isi tabel
     */
    private function excel(string $nama, string $judulLembar, array $kepala, array $kolom, array $baris)
    {
        $sp = new Spreadsheet();
        $lembar = $sp->getActiveSheet()->setTitle(substr($judulLembar, 0, 31));

        $r = 1;
        foreach ($kepala as $b) {
            if ($b !== []) {
                $lembar->fromArray($b, null, 'A'.$r);
            }
            $r++;
        }
        $lembar->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $lembar->fromArray($kolom, null, 'A'.$r);
        $akhir = chr(64 + count($kolom));
        $lembar->getStyle("A{$r}:{$akhir}{$r}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $lembar->getStyle("A{$r}:{$akhir}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F4C4A');
        $lembar->getStyle("A{$r}:{$akhir}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r++;
        foreach ($baris as $b) {
            $lembar->fromArray(array_map(fn ($v) => (string) $v, $b), null, 'A'.$r);
            $r++;
        }

        foreach (range('A', $akhir) as $k) {
            $lembar->getColumnDimension($k)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($sp) {
            (new Xlsx($sp))->save('php://output');
        }, $nama, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
