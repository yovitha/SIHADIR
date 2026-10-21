{{-- Pembuka dokumen laporan. Variabel: $judulHalaman, $pdf, $kembali --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judulHalaman }} | SIHADIR</title>
    @unless ($pdf)
        <link rel="icon" type="image/png" href="{{ asset('images/logo-tomohon.png') }}">
        <link rel="stylesheet" href="{{ asset('css/sihadir.css') }}?v=1">
    @endunless
    <style>
        @include('laporan._gaya')
    </style>
</head>
<body class="{{ $pdf ? '' : 'laporan-latar' }}">
@unless ($pdf)
    <div class="toolbar-cetak">
        <b>{{ $judulHalaman }}</b>
        <div class="tombol-grup">
            <a href="{{ $kembali }}" class="btn btn-putih btn-kecil" data-tanpa-tunda>Kembali</a>
            <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" class="btn btn-lembut btn-kecil" data-tanpa-tunda><x-icon name="download" :size="14" /> PDF</a>
            <a href="{{ request()->fullUrlWithQuery(['format' => 'xlsx']) }}" class="btn btn-lembut btn-kecil" data-tanpa-tunda><x-icon name="download" :size="14" /> Excel</a>
            <button type="button" class="btn btn-utama btn-kecil" onclick="window.print()"><x-icon name="printer" :size="14" /> Cetak</button>
        </div>
    </div>
@endunless
<div class="{{ $pdf ? '' : 'kertas' }}">
<div class="lap">
