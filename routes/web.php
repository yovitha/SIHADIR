<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstansiController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KehadiranController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('beranda');
Route::get('/hadir', [HomeController::class, 'cariKode'])->name('presensi.kode');

// Presensi peserta tanpa login (KF-06)
Route::get('/hadir/{kode}', [PresensiController::class, 'form'])->name('presensi.form');
Route::post('/hadir/{kode}', [PresensiController::class, 'simpan'])->middleware('throttle:30,1')->name('presensi.simpan');
Route::get('/hadir/{kode}/berhasil', [PresensiController::class, 'berhasil'])->name('presensi.berhasil');
Route::get('/hadir/{kode}/peserta', [PresensiController::class, 'cariPeserta'])->middleware('throttle:60,1')->name('presensi.cari');

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.proses');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Area pengguna terautentikasi
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'aktif'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    // Admin dan Operator: mengelola kegiatan, kehadiran, peserta, dan instansi
    Route::middleware('peran:admin,operator')->group(function () {
        Route::get('/kegiatan/baru', [KegiatanController::class, 'create'])->name('kegiatan.create');
        Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
        Route::get('/kegiatan/{kegiatan}/ubah', [KegiatanController::class, 'edit'])->whereNumber('kegiatan')->name('kegiatan.edit');
        Route::put('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->whereNumber('kegiatan')->name('kegiatan.update');
        Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->whereNumber('kegiatan')->name('kegiatan.destroy');
        Route::post('/kegiatan/{kegiatan}/status', [KegiatanController::class, 'ubahStatus'])->whereNumber('kegiatan')->name('kegiatan.status');

        Route::post('/kegiatan/{kegiatan}/kehadiran', [KehadiranController::class, 'tambahManual'])->whereNumber('kegiatan')->name('kehadiran.manual');
        Route::post('/kehadiran/{kehadiran}/batal', [KehadiranController::class, 'batalkan'])->whereNumber('kehadiran')->name('kehadiran.batal');

        Route::get('/peserta/impor', [PesertaController::class, 'formImpor'])->name('peserta.impor');
        Route::post('/peserta/impor', [PesertaController::class, 'impor'])->name('peserta.impor.proses');
        Route::get('/peserta/templat', [PesertaController::class, 'templat'])->name('peserta.templat');
        Route::get('/peserta', [PesertaController::class, 'index'])->name('peserta.index');
        Route::get('/peserta/baru', [PesertaController::class, 'create'])->name('peserta.create');
        Route::post('/peserta', [PesertaController::class, 'store'])->name('peserta.store');
        Route::get('/peserta/{peserta}/ubah', [PesertaController::class, 'edit'])->whereNumber('peserta')->name('peserta.edit');
        Route::put('/peserta/{peserta}', [PesertaController::class, 'update'])->whereNumber('peserta')->name('peserta.update');
        Route::delete('/peserta/{peserta}', [PesertaController::class, 'destroy'])->whereNumber('peserta')->name('peserta.destroy');

        Route::get('/instansi', [InstansiController::class, 'index'])->name('instansi.index');
        Route::post('/instansi', [InstansiController::class, 'store'])->name('instansi.store');
        Route::get('/instansi/{instansi}/ubah', [InstansiController::class, 'edit'])->whereNumber('instansi')->name('instansi.edit');
        Route::put('/instansi/{instansi}', [InstansiController::class, 'update'])->whereNumber('instansi')->name('instansi.update');
        Route::delete('/instansi/{instansi}', [InstansiController::class, 'destroy'])->whereNumber('instansi')->name('instansi.destroy');
    });

    // Semua peran (Operator hanya melihat miliknya, Pimpinan hanya membaca)
    Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
    Route::get('/kegiatan/{kegiatan}', [KegiatanController::class, 'show'])->whereNumber('kegiatan')->name('kegiatan.show');
    Route::get('/kegiatan/{kegiatan}/qr', [KegiatanController::class, 'qr'])->whereNumber('kegiatan')->name('kegiatan.qr');
    Route::get('/kegiatan/{kegiatan}/pantau', [KehadiranController::class, 'pantau'])->whereNumber('kegiatan')->name('kegiatan.pantau');
    Route::get('/kegiatan/{kegiatan}/pantau/data', [KehadiranController::class, 'data'])->whereNumber('kegiatan')->name('kegiatan.pantau.data');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/kegiatan/{kegiatan}', [LaporanController::class, 'kegiatan'])->whereNumber('kegiatan')->name('laporan.kegiatan');
    Route::get('/laporan/periode', [LaporanController::class, 'periode'])->name('laporan.periode');
    Route::get('/laporan/peserta', [LaporanController::class, 'peserta'])->name('laporan.peserta');

    // Khusus Admin
    Route::middleware('peran:admin')->group(function () {
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->whereNumber('kategori')->name('kategori.update');
        Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->whereNumber('kategori')->name('kategori.destroy');

        Route::get('/pengguna', [UserController::class, 'index'])->name('pengguna.index');
        Route::get('/pengguna/baru', [UserController::class, 'create'])->name('pengguna.create');
        Route::post('/pengguna', [UserController::class, 'store'])->name('pengguna.store');
        Route::get('/pengguna/{user}/ubah', [UserController::class, 'edit'])->whereNumber('user')->name('pengguna.edit');
        Route::put('/pengguna/{user}', [UserController::class, 'update'])->whereNumber('user')->name('pengguna.update');
        Route::post('/pengguna/{user}/status', [UserController::class, 'ubahStatus'])->whereNumber('user')->name('pengguna.status');
        Route::post('/pengguna/{user}/reset-sandi', [UserController::class, 'resetPassword'])->whereNumber('user')->name('pengguna.reset');

        Route::get('/log-aktivitas', [LogController::class, 'index'])->name('log.index');
    });
});
