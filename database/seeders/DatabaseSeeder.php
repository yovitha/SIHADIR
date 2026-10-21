<?php

namespace Database\Seeders;

use App\Models\Instansi;
use App\Models\KategoriKegiatan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Data awal sistem (Tabel 5.8): kategori kegiatan, instansi, dan akun awal.
     * Kata sandi akun awal WAJIB diganti setelah login pertama.
     */
    public function run(): void
    {
        foreach (['Rapat', 'Apel', 'Sosialisasi', 'Bimbingan Teknis', 'Pelatihan', 'Lainnya'] as $nama) {
            KategoriKegiatan::firstOrCreate(['nama_kategori' => $nama]);
        }

        // [K-01] Daftar instansi/bidang menunggu konfirmasi Diskominfo; ini hanya data awal contoh.
        foreach ([
            'Dinas Komunikasi dan Informatika Kota Tomohon',
            'Sekretariat Daerah Kota Tomohon',
            'Badan Kepegawaian dan Pengembangan SDM',
            'Dinas Pendidikan dan Kebudayaan',
            'Dinas Kesehatan',
            'Tamu / Umum',
        ] as $nama) {
            Instansi::firstOrCreate(['nama_instansi' => $nama]);
        }

        User::firstOrCreate(['username' => 'admin'], [
            'nama_lengkap' => 'Administrator SIHADIR',
            'password' => 'admin123',
            'role' => 'admin',
            'status_aktif' => true,
        ]);

        User::firstOrCreate(['username' => 'operator'], [
            'nama_lengkap' => 'Operator Kegiatan',
            'password' => 'operator123',
            'role' => 'operator',
            'status_aktif' => true,
        ]);

        User::firstOrCreate(['username' => 'pimpinan'], [
            'nama_lengkap' => 'Pimpinan Diskominfo',
            'password' => 'pimpinan123',
            'role' => 'pimpinan',
            'status_aktif' => true,
        ]);

        // Data contoh agar dashboard dan laporan langsung terisi. Hapus baris ini untuk pemakaian resmi.
        $this->call(DataContohSeeder::class);
    }
}
