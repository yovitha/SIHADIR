<?php

namespace App\Http\Controllers;

use App\Models\Instansi;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class InstansiController extends Controller
{
    public function index()
    {
        $instansi = Instansi::withCount('peserta')->orderBy('nama_instansi')->paginate(15);

        return view('master.instansi', compact('instansi'));
    }

    public function store(Request $request)
    {
        $instansi = Instansi::create($this->validasi($request));
        LogAktivitas::catat('Menambah instansi: '.$instansi->nama_instansi);

        return back()->with('sukses', 'Instansi berhasil ditambahkan.');
    }

    public function edit(Instansi $instansi)
    {
        return view('master.instansi-edit', compact('instansi'));
    }

    public function update(Request $request, Instansi $instansi)
    {
        $instansi->update($this->validasi($request));
        LogAktivitas::catat('Mengubah instansi: '.$instansi->nama_instansi);

        return redirect()->route('instansi.index')->with('sukses', 'Instansi berhasil diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        if ($instansi->peserta()->exists()) {
            return back()->with('galat', 'Instansi tidak dapat dihapus karena masih dipakai oleh data peserta.');
        }

        $nama = $instansi->nama_instansi;
        $instansi->delete();
        LogAktivitas::catat('Menghapus instansi: '.$nama);

        return back()->with('sukses', 'Instansi berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama_instansi' => ['required', 'string', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:200'],
        ]);
    }
}
