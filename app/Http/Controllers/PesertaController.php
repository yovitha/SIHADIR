<?php

namespace App\Http\Controllers;

use App\Models\Instansi;
use App\Models\LogAktivitas;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PesertaController extends Controller
{
    public function index(Request $request)
    {
        $peserta = Peserta::with('instansi')
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = '%'.addcslashes($request->q, '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $kata)->orWhere('nip_nik', 'like', $kata)->orWhere('jabatan', 'like', $kata));
            })
            ->when($request->filled('instansi'), fn ($q) => $q->where('id_instansi', $request->instansi))
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString();

        $instansi = Instansi::orderBy('nama_instansi')->get();

        return view('peserta.index', compact('peserta', 'instansi'));
    }

    public function create()
    {
        return view('peserta.form', [
            'peserta' => new Peserta(),
            'instansi' => Instansi::orderBy('nama_instansi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $peserta = Peserta::create($this->validasi($request));
        LogAktivitas::catat('Menambah peserta: '.$peserta->nama);

        return redirect()->route('peserta.index')->with('sukses', 'Data peserta berhasil ditambahkan.');
    }

    public function edit(Peserta $peserta)
    {
        return view('peserta.form', [
            'peserta' => $peserta,
            'instansi' => Instansi::orderBy('nama_instansi')->get(),
        ]);
    }

    public function update(Request $request, Peserta $peserta)
    {
        $peserta->update($this->validasi($request, $peserta));
        LogAktivitas::catat('Mengubah peserta: '.$peserta->nama);

        return redirect()->route('peserta.index')->with('sukses', 'Data peserta berhasil diperbarui.');
    }

    public function destroy(Peserta $peserta)
    {
        if ($peserta->kehadiran()->exists()) {
            return back()->with('galat', 'Peserta tidak dapat dihapus karena sudah memiliki riwayat kehadiran.');
        }

        $nama = $peserta->nama;
        $peserta->delete();
        LogAktivitas::catat('Menghapus peserta: '.$nama);

        return back()->with('sukses', 'Data peserta berhasil dihapus.');
    }

    public function formImpor()
    {
        return view('peserta.impor');
    }

    /** Impor dari Excel/CSV: baris tidak valid dilaporkan tanpa membatalkan baris yang valid (KF-11). */
    public function impor(Request $request)
    {
        $request->validate([
            'berkas' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:2048'],
        ]);

        try {
            $lembar = IOFactory::load($request->file('berkas')->getRealPath())->getActiveSheet();
            $baris = $lembar->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return back()->with('galat', 'Berkas tidak dapat dibaca. Gunakan templat yang disediakan.');
        }

        array_shift($baris); // baris judul

        $berhasil = 0;
        $galat = [];

        foreach ($baris as $i => $kolom) {
            $noBaris = $i + 2;
            $kolom = array_map(fn ($v) => trim((string) $v), array_pad($kolom, 6, ''));
            [$nip, $nama, $jabatan, $namaInstansi, $noHp, $email] = $kolom;

            if (implode('', $kolom) === '') {
                continue;
            }

            $v = Validator::make([
                'nip_nik' => $nip ?: null, 'nama' => $nama, 'jabatan' => $jabatan,
                'instansi' => $namaInstansi, 'no_hp' => $noHp ?: null, 'email' => $email ?: null,
            ], [
                'nip_nik' => ['nullable', 'string', 'max:25'],
                'nama' => ['required', 'string', 'max:100'],
                'jabatan' => ['required', 'string', 'max:100'],
                'instansi' => ['required', 'string', 'max:150'],
                'no_hp' => ['nullable', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:100'],
            ]);

            if ($v->fails()) {
                $galat[] = "Baris {$noBaris}: ".$v->errors()->first();

                continue;
            }

            if ($nip !== '' && Peserta::where('nip_nik', $nip)->exists()) {
                $galat[] = "Baris {$noBaris}: NIP/NIK {$nip} sudah terdaftar.";

                continue;
            }

            $instansi = Instansi::whereRaw('LOWER(nama_instansi) = ?', [mb_strtolower($namaInstansi)])->first()
                ?? Instansi::create(['nama_instansi' => $namaInstansi]);

            Peserta::create([
                'id_instansi' => $instansi->id_instansi,
                'nip_nik' => $nip ?: null,
                'nama' => $nama,
                'jabatan' => $jabatan,
                'no_hp' => $noHp ?: null,
                'email' => $email ?: null,
            ]);
            $berhasil++;
        }

        LogAktivitas::catat("Impor peserta dari berkas: {$berhasil} berhasil, ".count($galat).' gagal');

        return redirect()->route('peserta.impor')
            ->with($berhasil > 0 ? 'sukses' : 'galat', "{$berhasil} peserta berhasil diimpor, ".count($galat).' baris dilewati.')
            ->with('galat_impor', $galat);
    }

    public function templat()
    {
        $sp = new Spreadsheet();
        $lembar = $sp->getActiveSheet()->setTitle('Peserta');
        $lembar->fromArray([
            ['NIP/NIK', 'Nama', 'Jabatan', 'Instansi', 'No. HP', 'Email'],
            ['198501012010011001', 'Contoh Nama Pegawai', 'Staf Pelaksana', 'Dinas Komunikasi dan Informatika Kota Tomohon', '081234567890', 'contoh@tomohon.go.id'],
        ], null, 'A1');

        foreach (['A', 'E'] as $kol) {
            $lembar->getStyle($kol.'2:'.$kol.'1000')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        }
        $lembar->getStyle('A1:F1')->getFont()->setBold(true);
        foreach (range('A', 'F') as $kol) {
            $lembar->getColumnDimension($kol)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($sp) {
            (new Xlsx($sp))->save('php://output');
        }, 'templat-peserta-sihadir.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function validasi(Request $request, ?Peserta $peserta = null): array
    {
        return $request->validate([
            'id_instansi' => ['required', 'exists:instansi,id_instansi'],
            'nip_nik' => ['nullable', 'string', 'max:25', 'unique:peserta,nip_nik'.($peserta ? ','.$peserta->id_peserta.',id_peserta' : '')],
            'nama' => ['required', 'string', 'max:100'],
            'jabatan' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
        ]);
    }
}
