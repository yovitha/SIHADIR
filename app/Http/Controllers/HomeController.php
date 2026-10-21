<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('beranda');
    }

    /** Peserta yang tidak memindai QR dapat mengetik kode kegiatan secara manual. */
    public function cariKode(Request $request)
    {
        $kode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->query('kode')));

        if ($kode === '') {
            return redirect()->route('beranda')->withErrors(['kode' => 'Masukkan kode kegiatan terlebih dahulu.']);
        }

        return redirect()->route('presensi.form', $kode);
    }
}
