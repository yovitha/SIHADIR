@extends('layouts.dasar')

@section('judul', 'QR Code '.$kegiatan->nama_kegiatan)
@section('kelas-badan', 'qr-layar-badan')

@section('badan')
<div class="qr-layar">
    <div class="atas-kiri tidak-cetak">
        <a href="{{ route('kegiatan.show', $kegiatan) }}" class="btn btn-putih btn-kecil"><x-icon name="arrow-right" :size="14" class="" /> Kembali</a>
        <button type="button" class="btn btn-utama btn-kecil" data-layar-penuh><x-icon name="maximize" :size="14" /> Layar penuh</button>
    </div>

    <div class="kartu-putih muncul">
        <div class="presensi-kop">
            <img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon">
            <div><b>SIHADIR</b><small>{{ config('sihadir.instansi') }}</small></div>
        </div>
        <h1>{{ $kegiatan->nama_kegiatan }}</h1>
        <p class="teks-mute">{{ $kegiatan->tanggal->translatedFormat('l, d F Y') }} · {{ $kegiatan->jam_mulai_hm }}–{{ $kegiatan->jam_selesai_hm }} WITA · {{ $kegiatan->lokasi }}</p>

        <div class="qr-besar" data-qr="{{ $kegiatan->url_presensi }}" aria-label="QR Code presensi"></div>

        <p><b>Pindai QR Code untuk mengisi daftar hadir</b></p>
        <p class="tautan-teks" style="margin-top:8px">{{ $kegiatan->url_presensi }}</p>
        <div class="kode-besar">{{ $kegiatan->kode_unik }}</div>
        @if ($kegiatan->status !== 'aktif')
            <div class="alert alert-peringatan" style="margin:18px 0 0;text-align:left"><x-icon name="info" :size="18" /><div>Status kegiatan saat ini <b>{{ $kegiatan->status_label }}</b>. Presensi baru diterima setelah kegiatan diaktifkan.</div></div>
        @endif
    </div>
</div>
@endsection

@push('vendor')
<script src="{{ asset('vendor/qrcode.js') }}"></script>
@endpush
