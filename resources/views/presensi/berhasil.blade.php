@extends('layouts.dasar')

@section('judul', 'Presensi Berhasil')

@section('badan')
<div class="presensi">
    <div class="presensi-wadah">
        <div class="presensi-kop">
            <img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon">
            <div><b>SIHADIR</b><small>{{ config('sihadir.instansi') }}</small></div>
        </div>

        <div class="card hasil-kartu muncul">
            <div class="hasil-ikon ok"><x-icon name="check-circle" :size="44" /></div>
            <h1>Kehadiran tercatat</h1>
            <p>Terima kasih, <b>{{ $presensi['nama'] }}</b>. Kehadiran Anda pada kegiatan berikut sudah disimpan.</p>

            <div style="margin-top:22px;padding:16px 18px;border-radius:16px;background:var(--hijau-50);text-align:left">
                <b style="color:var(--hijau-900)">{{ $kegiatan->nama_kegiatan }}</b>
                <div class="teks-kecil teks-mute" style="margin-top:4px">
                    {{ $kegiatan->lokasi }}<br>
                    Waktu hadir: <b>{{ $presensi['waktu'] }} WITA</b>
                </div>
            </div>

            <div style="margin-top:22px">
                <a href="{{ route('beranda') }}" class="btn btn-utama"><x-icon name="home" :size="16" /> Selesai</a>
            </div>
        </div>
    </div>
</div>
@endsection
