@extends('layouts.dasar')

@section('judul', 'Masuk')

@section('badan')
<div class="masuk">
    <aside class="masuk-foto">
        <img class="latar" src="{{ asset('images/kegiatan-apel.jpg') }}" alt="">
        <div class="masuk-foto-isi">
            <a href="{{ route('beranda') }}" class="merek" data-tanpa-tunda>
                <img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon">
                <span><b>SIHADIR</b><small>{{ config('sihadir.instansi') }}</small></span>
            </a>
            <div>
                <h2>Daftar hadir kegiatan, <em>digital &amp; praktis.</em></h2>
                <p>Kelola kegiatan, bagikan QR Code, pantau kehadiran peserta, dan cetak laporan dalam satu aplikasi.</p>
            </div>
            <small style="color:rgba(255,255,255,.7)">&copy; {{ date('Y') }} {{ config('sihadir.pemerintah') }}</small>
        </div>
    </aside>

    <main class="masuk-form">
        <a href="{{ route('beranda') }}" class="kembali"><x-icon name="arrow-right" :size="18" /> Beranda</a>

        <div class="masuk-kartu muncul">
            <h1>Selamat datang</h1>
            <p>Masuk dengan akun SIHADIR Anda untuk melanjutkan.</p>

            @if ($errors->any())
                <div class="alert alert-galat" role="alert"><x-icon name="alert-triangle" :size="20" /><div>{{ $errors->first() }}</div></div>
            @endif

            <form method="POST" action="{{ route('login.proses') }}" novalidate>
                @csrf
                <div class="field">
                    <label for="username">Nama pengguna</label>
                    <div class="input-ikon">
                        <x-icon name="user" :size="18" />
                        <input type="text" id="username" name="username" value="{{ old('username') }}" autocomplete="username" autofocus required>
                    </div>
                </div>
                <div class="field">
                    <label for="password">Kata sandi</label>
                    <div class="input-ikon">
                        <x-icon name="lock" :size="18" />
                        <input type="password" id="password" name="password" autocomplete="current-password" required>
                        <button type="button" class="lihat" data-lihat-sandi="password" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="18" /></button>
                    </div>
                </div>
                <label class="cek" style="margin-bottom:20px"><input type="checkbox" name="ingat" value="1"> Ingat saya di perangkat ini</label>
                <button type="submit" class="btn btn-utama btn-besar btn-blok"><x-icon name="log-in" :size="18" /> Masuk</button>
            </form>

            @if (app()->environment('local'))
                <div class="akun-demo">
                    <b>Akun contoh (mode lokal)</b><br>
                    <button type="button" onclick="isiAkun('admin','admin123')">admin</button> ·
                    <button type="button" onclick="isiAkun('operator','operator123')">operator</button> ·
                    <button type="button" onclick="isiAkun('pimpinan','pimpinan123')">pimpinan</button>
                    <div style="margin-top:6px">Klik nama untuk mengisi otomatis. Ganti kata sandi setelah login pertama.</div>
                </div>
            @endif
        </div>
    </main>
</div>
@endsection

@push('skrip')
<script>
    function isiAkun(u, p) {
        document.getElementById('username').value = u;
        document.getElementById('password').value = p;
    }
</script>
@endpush
