@extends('layouts.dasar')

@section('judul', 'Presensi Tidak Tersedia')

@section('badan')
<div class="presensi">
    <div class="presensi-wadah">
        <div class="presensi-kop">
            <img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon">
            <div><b>SIHADIR</b><small>{{ config('sihadir.instansi') }}</small></div>
        </div>

        <div class="card hasil-kartu muncul">
            <div class="hasil-ikon tutup"><x-icon name="clock" :size="40" /></div>
            <h1>Presensi belum dapat diisi</h1>
            <p>{{ $pesan }}</p>

            <div style="margin-top:22px;padding-top:18px;border-top:1px solid var(--garis-2);text-align:left">
                <b style="color:var(--hijau-900)">{{ $kegiatan->nama_kegiatan }}</b>
                <div class="teks-kecil teks-mute" style="margin-top:4px">
                    {{ $kegiatan->tanggal->translatedFormat('l, d F Y') }} · {{ $kegiatan->jam_mulai_hm }}–{{ $kegiatan->jam_selesai_hm }} WITA<br>
                    {{ $kegiatan->lokasi }}
                </div>
            </div>

            <div style="margin-top:22px">
                <a href="{{ route('beranda') }}" class="btn btn-putih"><x-icon name="home" :size="16" /> Ke Beranda</a>
            </div>
        </div>
    </div>
</div>
@endsection
