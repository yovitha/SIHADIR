@extends('layouts.dasar')

@section('badan')
@php
    $pengguna = auth()->user();
@endphp
<div class="app">
    <aside class="sisi" id="sisi">
        <a class="merek" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon">
            <span><b>SIHADIR</b><small>Diskominfo Kota Tomohon</small></span>
        </a>

        <nav>
            <h6>Menu utama</h6>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'aktif' : '' }}"><x-icon name="grid" /> Dashboard</a>
            <a href="{{ route('kegiatan.index') }}" class="{{ request()->routeIs('kegiatan.*') && ! request()->routeIs('kegiatan.pantau*') ? 'aktif' : '' }}"><x-icon name="calendar" /> Kegiatan</a>
            <a href="{{ route('kegiatan.index', ['status' => 'aktif']) }}" class="{{ request()->routeIs('kegiatan.pantau*') ? 'aktif' : '' }}"><x-icon name="activity" /> Pantau Kehadiran</a>
            @if ($pengguna->bisaMengelola())
                <a href="{{ route('peserta.index') }}" class="{{ request()->routeIs('peserta.*') ? 'aktif' : '' }}"><x-icon name="users" /> Data Peserta</a>
                <a href="{{ route('instansi.index') }}" class="{{ request()->routeIs('instansi.*') ? 'aktif' : '' }}"><x-icon name="briefcase" /> Instansi</a>
            @endif
            <a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan.*') ? 'aktif' : '' }}"><x-icon name="file-text" /> Laporan</a>

            @if ($pengguna->isAdmin())
                <h6>Administrasi</h6>
                <a href="{{ route('kategori.index') }}" class="{{ request()->routeIs('kategori.*') ? 'aktif' : '' }}"><x-icon name="tag" /> Kategori Kegiatan</a>
                <a href="{{ route('pengguna.index') }}" class="{{ request()->routeIs('pengguna.*') ? 'aktif' : '' }}"><x-icon name="shield" /> Pengguna</a>
                <a href="{{ route('log.index') }}" class="{{ request()->routeIs('log.*') ? 'aktif' : '' }}"><x-icon name="clock" /> Log Aktivitas</a>
            @endif
        </nav>

        <div class="sisi-bawah">
            <a href="{{ route('profil') }}" class="pengguna-kartu">
                <span class="avatar">{{ $pengguna->inisial }}</span>
                <span><b>{{ $pengguna->nama_lengkap }}</b><span>{{ $pengguna->role_label }}</span></span>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:10px">
                @csrf
                <button type="submit" class="btn btn-blok btn-kecil btn-keluar"><x-icon name="log-out" :size="16" /> Keluar</button>
            </form>
        </div>
    </aside>
    <div class="latar-gelap"></div>

    <div class="utama">
        <header class="bar-atas">
            <div style="display:flex;align-items:center;gap:12px;min-width:0">
                <button type="button" class="btn btn-putih btn-ikon tombol-sisi" aria-label="Buka menu"><x-icon name="menu" /></button>
                <div style="min-width:0">
                    <h1>@yield('judul')</h1>
                    <div class="remah">@yield('remah', 'Sistem Informasi Daftar Hadir Kegiatan')</div>
                </div>
            </div>
            <div class="bar-kanan">
                <span class="tgl-hari">{{ now()->translatedFormat('l, d F Y') }}</span>
                @yield('aksi-atas')
            </div>
        </header>

        <main class="isi">
            @include('partials.pesan')
            @yield('konten')
        </main>
    </div>
</div>
@endsection
