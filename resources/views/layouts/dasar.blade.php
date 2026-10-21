<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F4C4A">
    <title>@yield('judul', 'Beranda') | SIHADIR Diskominfo Kota Tomohon</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tomohon.png') }}">
    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans-latin-700-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/sihadir.css') }}?v=1">
    @stack('gaya')
</head>
<body class="@yield('kelas-badan')">
    @yield('badan')

    @stack('vendor')
    <script src="{{ asset('js/sihadir.js') }}?v=1"></script>
    @stack('skrip')
</body>
</html>
