<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('kode') @yield('judul') | SIHADIR</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tomohon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/sihadir.css') }}?v=1">
</head>
<body>
<div class="galat-halaman">
    <div class="muncul" style="max-width:460px">
        <img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon" style="width:72px;margin:0 auto 18px">
        <div class="kode">@yield('kode')</div>
        <h1 style="font-size:26px;margin:10px 0 8px">@yield('judul')</h1>
        <p class="teks-mute" style="margin-bottom:24px">@yield('pesan')</p>
        <div class="tombol-grup" style="justify-content:center">
            <a href="{{ url('/') }}" class="btn btn-utama" data-tanpa-tunda>Ke Beranda</a>
            <a href="javascript:history.back()" class="btn btn-putih">Kembali</a>
        </div>
    </div>
</div>
<script src="{{ asset('js/sihadir.js') }}?v=1"></script>
</body>
</html>
