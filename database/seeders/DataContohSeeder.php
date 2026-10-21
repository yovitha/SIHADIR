<?php

namespace Database\Seeders;

use App\Models\Instansi;
use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kehadiran;
use App\Models\Peserta;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Data contoh untuk percobaan/demonstrasi. Seluruh nama di sini fiktif. */
class DataContohSeeder extends Seeder
{
    public function run(): void
    {
        if (Kegiatan::exists()) {
            return;
        }

        $operator = User::where('username', 'operator')->first();
        $instansi = Instansi::pluck('id_instansi', 'nama_instansi');
        $kategori = KategoriKegiatan::pluck('id_kategori', 'nama_kategori');
        $diskominfo = $instansi['Dinas Komunikasi dan Informatika Kota Tomohon'];

        $daftarPeserta = [
            ['Marlina Kaunang', 'Kepala Bidang Aplikasi Informatika'],
            ['Stevanus Mamahit', 'Kepala Bidang Komunikasi Publik'],
            ['Grace Pangemanan', 'Analis Sistem Informasi'],
            ['Jefry Lumentut', 'Pranata Komputer Ahli Pertama'],
            ['Novita Rumagit', 'Staf Sekretariat'],
            ['Kevin Tampi', 'Pengelola Jaringan'],
            ['Febby Sumampouw', 'Bendahara Pengeluaran'],
            ['Ronald Wowor', 'Pengelola Data dan Statistik'],
            ['Cindy Lontaan', 'Pranata Humas'],
            ['Alfian Runtu', 'Teknisi Jaringan'],
            ['Yolanda Makalew', 'Staf Persandian'],
            ['Daniel Mokodompit', 'Operator Layanan'],
            ['Sherly Tumbelaka', 'Analis Kepegawaian'],
            ['Imanuel Kalalo', 'Pengelola Website'],
            ['Maria Langi', 'Staf Program dan Pelaporan'],
        ];

        $peserta = collect($daftarPeserta)->map(fn ($p, $i) => Peserta::create([
            'id_instansi' => $diskominfo,
            'nip_nik' => '19900101'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT).'20200100'.($i % 9 + 1),
            'nama' => $p[0],
            'jabatan' => $p[1],
            'no_hp' => '08123'.str_pad((string) (400100 + $i * 37), 6, '0', STR_PAD_LEFT),
        ]));

        $contoh = [
            ['Apel Pagi Awal Bulan', 'Apel', 'Halaman Kantor Diskominfo', -150, '07:30', '08:30'],
            ['Rapat Koordinasi Pengembangan Aplikasi', 'Rapat', 'Ruang Rapat Diskominfo', -120, '09:00', '11:30'],
            ['Sosialisasi Keamanan Informasi', 'Sosialisasi', 'Aula Kantor Walikota', -90, '09:00', '12:00'],
            ['Bimtek Pengelolaan Website OPD', 'Bimbingan Teknis', 'Ruang Pelatihan Diskominfo', -60, '08:30', '15:00'],
            ['Rapat Evaluasi Triwulan', 'Rapat', 'Ruang Rapat Diskominfo', -35, '10:00', '12:00'],
            ['Pelatihan Dasar Desain Grafis', 'Pelatihan', 'Ruang Pelatihan Diskominfo', -18, '08:30', '14:00'],
            ['Apel Pagi', 'Apel', 'Halaman Kantor Diskominfo', -5, '07:30', '08:15'],
        ];

        foreach ($contoh as $i => [$nama, $kat, $lokasi, $selisih, $mulai, $selesai]) {
            $k = Kegiatan::create([
                'id_user' => $operator->id_user,
                'id_kategori' => $kategori[$kat],
                'nama_kegiatan' => $nama,
                'deskripsi' => 'Kegiatan contoh untuk percobaan sistem.',
                'tanggal' => today()->addDays($selisih)->toDateString(),
                'jam_mulai' => $mulai.':00',
                'jam_selesai' => $selesai.':00',
                'lokasi' => $lokasi,
                'penyelenggara' => 'Dinas Komunikasi dan Informatika Kota Tomohon',
                'kode_unik' => Kegiatan::buatKodeUnik(),
                'status' => 'selesai',
                'tanda_tangan_wajib' => false,
            ]);

            $jumlah = 8 + (($i * 3) % 8);
            foreach ($peserta->shuffle()->take($jumlah) as $n => $p) {
                Kehadiran::create([
                    'id_kegiatan' => $k->id_kegiatan,
                    'id_peserta' => $p->id_peserta,
                    'waktu_hadir' => $k->mulaiPada()->addMinutes($n * 2 + 1),
                    'ip_address' => '127.0.0.1',
                    'status' => 'tercatat',
                ]);
            }
        }

        // Satu kegiatan aktif hari ini agar fitur QR Code, presensi, dan pantau dapat langsung dicoba.
        $aktif = Kegiatan::create([
            'id_user' => $operator->id_user,
            'id_kategori' => $kategori['Rapat'],
            'nama_kegiatan' => 'Rapat Koordinasi SIHADIR (Contoh Aktif)',
            'deskripsi' => 'Kegiatan contoh yang sedang aktif. Pindai QR Code atau buka tautan presensi untuk mencoba.',
            'tanggal' => today()->toDateString(),
            'jam_mulai' => '00:00:00',
            'jam_selesai' => '23:59:00',
            'lokasi' => 'Ruang Rapat Diskominfo',
            'penyelenggara' => 'Dinas Komunikasi dan Informatika Kota Tomohon',
            'kode_unik' => Kegiatan::buatKodeUnik(),
            'status' => 'aktif',
            'tanda_tangan_wajib' => true,
        ]);

        foreach ($peserta->take(3) as $n => $p) {
            Kehadiran::create([
                'id_kegiatan' => $aktif->id_kegiatan,
                'id_peserta' => $p->id_peserta,
                'waktu_hadir' => now()->subMinutes(10 - $n * 3),
                'ip_address' => '127.0.0.1',
                'status' => 'tercatat',
                'keterangan' => 'Ditambahkan manual oleh Operator Kegiatan',
            ]);
        }
    }
}
