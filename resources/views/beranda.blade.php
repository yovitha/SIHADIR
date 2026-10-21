@extends('layouts.dasar')

@section('judul', 'Daftar Hadir Kegiatan Digital')
@section('kelas-badan', 'lp')

@section('badan')
<div class="lp-bg"></div>

{{-- ============================== NAVIGASI ============================== --}}
<header class="navbar">
    <div class="wadah">
        <div class="navbar-isi">
            <a href="{{ route('beranda') }}" class="merek" data-tanpa-tunda>
                <img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon">
                <span>
                    <b>SIHADIR</b>
                    <small>{{ config('sihadir.instansi') }}</small>
                </span>
            </a>

            <button type="button" class="tombol-menu" aria-label="Buka menu"><x-icon name="menu" /></button>

            <nav class="menu-atas" aria-label="Navigasi utama">
                <a class="tautan" href="#beranda">Beranda</a>
                <a class="tautan" href="#tentang">Tentang SIHADIR</a>
                <a class="tautan" href="#panduan">Panduan</a>
                <a class="tautan" href="#kontak">Kontak</a>
                @auth
                    <a class="btn btn-garis" href="{{ route('dashboard') }}"><x-icon name="grid" :size="18" /> Dashboard</a>
                @else
                    <a class="btn btn-garis" href="{{ route('login') }}"><x-icon name="log-in" :size="18" /> Log in</a>
                @endauth
            </nav>
        </div>
    </div>
</header>

{{-- ================================ HERO ================================ --}}
<section class="hero" id="beranda">
    <div class="wadah hero-grid">
        <div>
            <span class="lencana muncul"><i></i> Aplikasi Resmi Daftar Hadir Kegiatan</span>
            <h1 class="muncul t2">Daftar Hadir Kegiatan <em>Digital &amp; Praktis</em></h1>
            <p class="lead muncul t3">
                Buat kegiatan, bagikan QR Code, pantau kehadiran peserta secara langsung, dan cetak laporan
                dalam satu aplikasi. Dikelola oleh Dinas Komunikasi dan Informatika Kota Tomohon.
            </p>
            <div class="hero-aksi muncul t4">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-utama btn-besar">Buka Dashboard <x-icon name="arrow-right" :size="18" /></a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-utama btn-besar">Mulai Sekarang</a>
                @endauth
                <a href="#panduan" class="btn btn-putih btn-besar">Lihat Panduan</a>
            </div>
            <div class="hero-fakta muncul t5">
                <div><b>&lt; 1 menit</b><span>presensi per peserta</span></div>
                <div><b>QR Code</b><span>unik untuk tiap kegiatan</span></div>
                <div><b>PDF &amp; Excel</b><span>laporan siap cetak</span></div>
            </div>
        </div>

        <!-- Kolom Kanan: Gambar Baru & Interaktif Klik Melayang -->
        <div class="hero-visual muncul t3">
            <div class="chip chip-a">
                <span class="ik"><x-icon name="smartphone" :size="20" /></span>
                <div><b>Presensi via QR Code</b><span>Peserta tanpa perlu login</span></div>
            </div>

            <!-- Kontainer Gambar Utama dengan ID untuk Fungsi Klik Melayang -->
            <div id="floatingCard"
                 onclick="toggleFloating()"
                 class="foto-bingkai"
                 style="cursor: pointer; transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); overflow: hidden;">
                <img src="{{ asset('images/gedung-kominfo.jpeg') }}" alt="Gedung Dinas Komunikasi dan Informatika Kota Tomohon" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <div class="logo-lencana"><img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon"></div>

            <div class="chip chip-b">
                <span class="ik"><x-icon name="shield" :size="20" /></span>
                <div><b>Diskominfo Kota Tomohon</b><span>{{ config('sihadir.pemerintah') }}</span></div>
            </div>
        </div>
    </div>
</section>

{{-- ============================== TENTANG =============================== --}}
<section class="seksi" id="tentang">
    <div class="wadah">
        <div class="seksi-judul">
            <span class="atas">Tentang</span>
            <h2>Tentang SIHADIR</h2>
            <p>Sistem Informasi Daftar Hadir Kegiatan Berbasis Web pada Dinas Komunikasi dan Informatika Kota Tomohon.
                Menggantikan lembar daftar hadir kertas yang mudah hilang, sulit dibaca, dan harus diketik ulang.</p>
        </div>

        <div class="grid-4">
            <article class="fitur">
                <div class="ik"><x-icon name="grid" :size="24" /></div>
                <h3>Kegiatan &amp; QR Code</h3>
                <p>Simpan data kegiatan lengkap dengan jadwal dan lokasi. Setiap kegiatan otomatis memiliki kode, tautan, dan QR Code unik.</p>
            </article>
            <article class="fitur">
                <div class="ik"><x-icon name="edit-2" :size="24" /></div>
                <h3>Presensi Peserta</h3>
                <p>Peserta memindai QR Code, mengisi data singkat, dan menandatangani langsung di layar ponsel tanpa membuat akun.</p>
            </article>
            <article class="fitur">
                <div class="ik"><x-icon name="activity" :size="24" /></div>
                <h3>Pantau Kehadiran</h3>
                <p>Lihat daftar hadir yang bertambah secara langsung, tambahkan kehadiran manual, dan batalkan data yang keliru.</p>
            </article>
            <article class="fitur">
                <div class="ik"><x-icon name="file-text" :size="24" /></div>
                <h3>Laporan &amp; Ekspor</h3>
                <p>Rekap per kegiatan, per periode, dan per peserta. Cetak langsung atau unduh sebagai PDF dan Excel.</p>
            </article>
        </div>
    </div>
</section>

{{-- ============================= CARA KERJA ============================= --}}
<section class="seksi" style="padding-top:8px">
    <div class="wadah">
        <div class="seksi-judul">
            <span class="atas">Alur singkat</span>
            <h2>Empat langkah, tanpa kertas</h2>
        </div>
        <ol class="langkah" style="padding:0;margin:0">
            <li><h3>Buat kegiatan</h3><p>Operator mengisi nama, tanggal, jam, dan lokasi kegiatan, lalu mengaktifkan presensi.</p></li>
            <li><h3>Bagikan QR Code</h3><p>Tampilkan QR Code di layar proyektor atau cetak di lokasi. Tautan teks juga tersedia.</p></li>
            <li><h3>Peserta presensi</h3><p>Peserta memindai, mengisi nama, instansi, jabatan, dan tanda tangan digital.</p></li>
            <li><h3>Pantau &amp; laporkan</h3><p>Kehadiran terekap otomatis. Cetak laporan atau unduh PDF dan Excel kapan saja.</p></li>
        </ol>
    </div>
</section>

{{-- ============================== KODE ================================== --}}
<section class="seksi" style="padding-top:8px">
    <div class="wadah">
        <div class="pita">
            <div class="pita-isi">
                <div>
                    <h2>Sedang menghadiri kegiatan?</h2>
                    <p>Pindai QR Code yang ditampilkan panitia, atau ketik kode kegiatan di sini untuk membuka formulir daftar hadir.</p>
                </div>
                <div>
                    <form class="form-kode" method="GET" action="{{ route('presensi.kode') }}">
                        <input type="text" name="kode" maxlength="20" placeholder="Masukkan kode kegiatan" autocomplete="off" aria-label="Kode kegiatan" required>
                        <button type="submit" class="btn btn-utama">Isi Daftar Hadir</button>
                    </form>
                    @error('kode')
                        <p style="color:#FFD2B0;font-weight:600;margin-top:10px">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ======================== PRESENSI TEPERCAYA ========================== --}}
<section class="seksi" style="padding-top:8px">
    <div class="wadah">
        <div class="seksi-judul">
            <span class="atas">Keandalan data</span>
            <h2>Presensi yang dapat dipertanggungjawabkan</h2>
            <p>Beberapa aturan bawaan menjaga agar daftar hadir tetap sah dan tidak disalahgunakan.</p>
        </div>
        <div class="grid-3">
            <article class="fitur">
                <div class="ik"><x-icon name="clock" :size="24" /></div>
                <h3>Dibatasi waktu</h3>
                <p>Presensi hanya diterima saat kegiatan aktif dan dalam rentang jam kegiatan. Waktu hadir diambil dari jam server.</p>
            </article>
            <article class="fitur">
                <div class="ik"><x-icon name="user-check" :size="24" /></div>
                <h3>Tanpa presensi ganda</h3>
                <p>Satu peserta hanya tercatat satu kali per kegiatan, dan operator dapat membatalkan kehadiran yang mencurigakan.</p>
            </article>
            <article class="fitur">
                <div class="ik"><x-icon name="lock" :size="24" /></div>
                <h3>Aman &amp; terkontrol</h3>
                <p>Hak akses dibedakan untuk Admin, Operator, dan Pimpinan. Setiap aktivitas penting tercatat pada log.</p>
            </article>
        </div>
    </div>
</section>

{{-- =============================== PANDUAN ============================== --}}
<section class="seksi" id="panduan">
    <div class="wadah">
        <div class="seksi-judul">
            <span class="atas">Panduan</span>
            <h2>Cara menggunakan SIHADIR</h2>
            <p>Pilih peran Anda untuk melihat langkah singkatnya.</p>
        </div>

        <div class="panduan">
            <details open>
                <summary>Peserta kegiatan <x-icon name="chevron-down" :size="20" /></summary>
                <div>
                    <ol>
                        <li>Pindai QR Code kegiatan dengan kamera ponsel, atau ketik kode kegiatan di halaman ini.</li>
                        <li>Ketik nama Anda. Jika sudah terdaftar, pilih dari daftar yang muncul.</li>
                        <li>Lengkapi instansi dan jabatan, lalu bubuhkan tanda tangan pada layar.</li>
                        <li>Tekan <b>Kirim Kehadiran</b>. Halaman konfirmasi menandakan presensi berhasil.</li>
                    </ol>
                </div>
            </details>
            <details>
                <summary>Operator kegiatan <x-icon name="chevron-down" :size="20" /></summary>
                <div>
                    <ol>
                        <li>Log in, buka menu <b>Kegiatan</b>, lalu pilih <b>Kegiatan Baru</b>.</li>
                        <li>Isi data kegiatan. Kegiatan baru berstatus <b>Draft</b>.</li>
                        <li>Buka detail kegiatan dan tekan <b>Buka Presensi</b> agar peserta dapat mengisi.</li>
                        <li>Tampilkan QR Code, pantau kehadiran, dan tutup presensi bila kegiatan selesai.</li>
                    </ol>
                </div>
            </details>
            <details>
                <summary>Pimpinan <x-icon name="chevron-down" :size="20" /></summary>
                <div>
                    <ol>
                        <li>Log in untuk melihat ringkasan kegiatan dan grafik kehadiran pada Dashboard.</li>
                        <li>Buka daftar kegiatan atau menu <b>Laporan</b> untuk melihat rekap kehadiran.</li>
                        <li>Akses Pimpinan bersifat baca saja, sehingga data tidak dapat diubah.</li>
                    </ol>
                </div>
            </details>
            <details>
                <summary>Admin sistem <x-icon name="chevron-down" :size="20" /></summary>
                <div>
                    <ol>
                        <li>Kelola akun pengguna dan peran pada menu <b>Pengguna</b>.</li>
                        <li>Kelola kategori kegiatan, instansi, dan data peserta (termasuk impor Excel).</li>
                        <li>Pantau seluruh aktivitas pengguna melalui <b>Log Aktivitas</b>.</li>
                        <li>Ganti kata sandi awal akun setelah login pertama.</li>
                    </ol>
                </div>
            </details>
        </div>
    </div>
</section>

{{-- =============================== KONTAK =============================== --}}
<section class="seksi" id="kontak" style="padding-top:8px">
    <div class="wadah">
        <div class="seksi-judul">
            <span class="atas">Kontak</span>
            <h2>Butuh bantuan?</h2>
            <p>Hubungi pengelola SIHADIR di Dinas Komunikasi dan Informatika Kota Tomohon untuk pembuatan akun, pelatihan, atau kendala penggunaan.</p>
        </div>
        <div class="kontak">
            <div class="card card-pad">
                <div class="kontak-item">
                    <span class="ik"><x-icon name="map-pin" :size="20" /></span>
                    <div><b>Alamat</b><span>{{ config('sihadir.instansi') }}<br>{{ config('sihadir.kontak.alamat') }}</span></div>
                </div>
                @if (config('sihadir.kontak.telepon'))
                    <div class="kontak-item">
                        <span class="ik"><x-icon name="phone" :size="20" /></span>
                        <div><b>Telepon</b><span>{{ config('sihadir.kontak.telepon') }}</span></div>
                    </div>
                @endif
                @if (config('sihadir.kontak.email'))
                    <div class="kontak-item">
                        <span class="ik"><x-icon name="mail" :size="20" /></span>
                        <div><b>Email</b><span>{{ config('sihadir.kontak.email') }}</span></div>
                    </div>
                @endif
                <div class="kontak-item">
                    <span class="ik"><x-icon name="clock" :size="20" /></span>
                    <div><b>Jam layanan</b><span>{{ config('sihadir.kontak.jam_layanan') }}</span></div>
                </div>
            </div>
            <div class="card card-pad" style="background:var(--hijau-800);border:0;color:#fff;display:flex;flex-direction:column;justify-content:center;gap:14px">
                <h3 style="color:#fff;font-size:22px">Siap mencoba SIHADIR?</h3>
                <p style="color:rgba(255,255,255,.8)">Log in dengan akun yang diberikan Admin untuk mulai membuat kegiatan dan memantau kehadiran.</p>
                <div>
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-utama"><x-icon name="log-in" :size="18" /> {{ auth()->check() ? 'Buka Dashboard' : 'Log in' }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =============================== FOOTER =============================== --}}
<footer class="footer">
    <div class="wadah">
        <div class="footer-isi">
            <a href="{{ route('beranda') }}" class="merek" data-tanpa-tunda>
                <img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon">
                <span><b>SIHADIR</b><small>{{ config('sihadir.nama_panjang') }}</small></span>
            </a>
            <div style="display:flex;gap:18px;flex-wrap:wrap;font-weight:600;font-size:14px">
                <a href="#tentang">Tentang</a><a href="#panduan">Panduan</a><a href="#kontak">Kontak</a>
                <a href="{{ route('login') }}">Log in</a>
            </div>
        </div>
        <div class="footer-bawah">
            <span>&copy; {{ date('Y') }} {{ config('sihadir.instansi') }}.</span>
            <span>{{ config('sihadir.pemerintah') }}</span>
        </div>
    </div>
</footer>

{{-- Script JavaScript untuk Animasi Klik Melayang pada Bingkai Foto --}}
<script>
    let isFloating = false;
    function toggleFloating() {
        const card = document.getElementById('floatingCard');
        isFloating = !isFloating;

        if (isFloating) {
            // Efek melayang ke atas, membesar sedikit, dan memberi bayangan lebih tebal
            card.style.transform = 'translateY(-14px) scale(1.03)';
            card.style.boxShadow = '0 25px 50px -12px rgba(13, 148, 136, 0.35)';
            card.style.borderColor = '#ea580c';
        } else {
            // Kembali ke posisi normal
            card.style.transform = 'translateY(0px) scale(1)';
            card.style.boxShadow = '';
            card.style.borderColor = '';
        }
    }
</script>
@endsection
