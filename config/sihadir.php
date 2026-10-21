<?php

return [
    'nama' => 'SIHADIR',
    'nama_panjang' => 'Sistem Informasi Daftar Hadir Kegiatan',
    'instansi' => 'Dinas Komunikasi dan Informatika Kota Tomohon',
    'pemerintah' => 'Pemerintah Kota Tomohon',

    // AB-03: toleransi waktu presensi (menit) sebelum jam mulai dan sesudah jam selesai [K-06]
    'toleransi_menit' => (int) env('SIHADIR_TOLERANSI_MENIT', 15),

    // AB-04: batasi satu perangkat satu presensi per kegiatan (cookie). Matikan bila memakai satu tablet bersama.
    'batasi_perangkat' => (bool) env('SIHADIR_BATASI_PERANGKAT', true),

    // AB-12: ukuran maksimal gambar tanda tangan (KB)
    'maks_tanda_tangan_kb' => 200,

    // Panjang kode unik kegiatan (AB-01)
    'panjang_kode' => 10,

    // Informasi kontak yang tampil di halaman depan. Kosongkan bila belum dikonfirmasi.
    'kontak' => [
        'alamat' => env('SIHADIR_ALAMAT', 'Kota Tomohon, Sulawesi Utara'),
        'telepon' => env('SIHADIR_TELEPON'),
        'email' => env('SIHADIR_EMAIL'),
        'jam_layanan' => env('SIHADIR_JAM_LAYANAN', 'Senin - Jumat, jam kerja'),
    ],
];
