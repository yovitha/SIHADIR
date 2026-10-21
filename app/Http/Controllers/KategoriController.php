<?php

namespace App\Http\Controllers;

use App\Models\KategoriKegiatan;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = KategoriKegiatan::withCount('kegiatan')->orderBy('nama_kategori')->get();

        return view('master.kategori', compact('kategori'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:50', 'unique:kategori_kegiatan,nama_kategori'],
        ]);

        KategoriKegiatan::create($data);
        LogAktivitas::catat('Menambah kategori kegiatan: '.$data['nama_kategori']);

        return back()->with('sukses', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, KategoriKegiatan $kategori)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:50', Rule::unique('kategori_kegiatan', 'nama_kategori')->ignore($kategori->id_kategori, 'id_kategori')],
        ]);

        $kategori->update($data);
        LogAktivitas::catat('Mengubah kategori kegiatan: '.$data['nama_kategori']);

        return back()->with('sukses', 'Kategori berhasil diperbarui.');
    }

    public function destroy(KategoriKegiatan $kategori)
    {
        if ($kategori->kegiatan()->exists()) {
            return back()->with('galat', 'Kategori tidak dapat dihapus karena masih dipakai oleh kegiatan.');
        }

        $nama = $kategori->nama_kategori;
        $kategori->delete();
        LogAktivitas::catat('Menghapus kategori kegiatan: '.$nama);

        return back()->with('sukses', 'Kategori berhasil dihapus.');
    }
}
