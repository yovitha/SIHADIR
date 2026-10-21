# SIHADIR - Sistem Informasi Daftar Hadir Kegiatan (Prototipe)
Diskominfo Kota Tomohon

Prototipe web statis (HTML + CSS + JavaScript). Tidak perlu PHP, MySQL, atau XAMPP.
Data tersimpan di browser (localStorage).

## Cara menjalankan di VS Code
1. Buka folder induk (misalnya `sihadir-prototype`) di VS Code, pastikan folder `sihadir` ada di dalamnya.
2. Pasang ekstensi **Live Server** (Ritwick Dey) jika belum ada.
3. Klik kanan `sihadir/login.html` lalu pilih **Open with Live Server**.
4. Alamat yang terbuka: `http://127.0.0.1:5500/sihadir/login.html`.

## Akun demo
| Peran    | Username   | Password     |
|----------|------------|--------------|
| Admin    | admin      | admin123     |
| Operator | operator   | operator123  |
| Pimpinan | pimpinan   | pimpinan123  |

## Cara mencoba alur presensi
1. Login sebagai admin atau operator, buka **Kegiatan & QR Code**, lalu **Tambah Kegiatan**.
2. Di halaman detail, klik **Aktifkan presensi**.
3. Klik **Buka presensi** (tab baru), isi formulir dan tanda tangan, lalu kirim.
4. Buka **Pantau Kehadiran**: data muncul otomatis.
5. Buka **Laporan & Ekspor** untuk cetak/PDF dan Excel.

## Catatan
- QR Code memakai pustaka dari internet (CDN). Perlu koneksi saat halaman detail dibuka.
- Data hanya ada di browser tempat data dibuat. Peserta dari HP tidak akan masuk ke data laptop
  sebelum ada server dan basis data (tahap PHP + MySQL pada dokumen perencanaan).
- Kata sandi demo disimpan apa adanya khusus untuk prototipe. Versi produksi wajib memakai hash.
- Reset data contoh: menu **Master Data** > **Reset data demo**.

## Logo Dinas Kominfo Kota Tomohon
Kode sudah siap memakai logo. Yang perlu kamu lakukan:
1. Siapkan file logo resmi Dinas Komunikasi dan Informatika Kota Tomohon (minta ke pembimbing atau ambil dari situs/media resmi dinas).
2. Simpan dengan nama **`logo-diskominfo.png`** di folder `sihadir/assets/img/`.
3. Muat ulang halaman. Logo otomatis tampil di halaman login, menu samping, halaman presensi peserta, dan kop laporan cetak.

Selama file itu belum ada, aplikasi memakai logo sementara (`logo-placeholder.svg`) dan konsol browser menampilkan 404 untuk `logo-diskominfo.png`. Itu normal. Disarankan PNG persegi atau hampir persegi dengan latar transparan.

## Catatan pengembangan
- Dua halaman diakses lewat nama berkas: `presensi.html` (publik, untuk peserta) dan `pantau.html` (pantau kehadiran).
- Jika QR Code tidak muncul, periksa koneksi internet karena pustaka QR dimuat dari cdnjs.
