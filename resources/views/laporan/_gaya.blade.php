{{-- Gaya laporan: dipakai bersama oleh tampilan cetak (browser) dan PDF (DomPDF). Tanpa flex/grid agar kompatibel dengan DomPDF. --}}
@page { size: A4; margin: 16mm 14mm; }
.lap { font-family: "DejaVu Sans", "Plus Jakarta Sans", Arial, sans-serif; color: #111; font-size: 11.5px; line-height: 1.5; }
.lap table { border-collapse: collapse; width: 100%; }
.lap .kop { width: 100%; border-bottom: 3px double #0A3B39; padding-bottom: 10px; margin-bottom: 16px; }
.lap .kop td { border: 0; vertical-align: middle; padding: 0; }
.lap .kop .logo { width: 70px; }
.lap .kop .logo img { width: 62px; height: auto; display: block; }
.lap .kop .teks { text-align: center; }
.lap .kop .teks .pem { font-size: 12px; letter-spacing: .04em; text-transform: uppercase; }
.lap .kop .teks .dinas { font-size: 15px; font-weight: bold; text-transform: uppercase; color: #0A3B39; }
.lap .kop .teks .alamat { font-size: 10.5px; color: #444; }
.lap h1.judul { font-size: 15px; text-align: center; text-transform: uppercase; margin: 6px 0 14px; letter-spacing: .03em; color: #0A3B39; }
.lap .meta { margin-bottom: 14px; }
.lap .meta td { border: 0; padding: 2px 0; vertical-align: top; }
.lap .meta td.k { width: 120px; font-weight: bold; }
.lap .meta td.t { width: 14px; }
.lap table.data th { background: #0F4C4A; color: #fff; padding: 7px 6px; border: 1px solid #0F4C4A; font-size: 11px; text-align: center; }
.lap table.data td { border: 1px solid #999; padding: 6px; vertical-align: middle; }
.lap table.data tr.zebra td { background: #F5F8F7; }
.lap .c { text-align: center; }
.lap .r { text-align: right; }
.lap .ttd-img { height: 34px; width: auto; }
.lap .ringkas { margin: 14px 0; }
.lap .ringkas td { border: 1px solid #999; padding: 8px 10px; }
.lap .ringkas td.angka { font-size: 18px; font-weight: bold; color: #B94F08; text-align: center; width: 90px; }
.lap .pengesahan { width: 100%; margin-top: 28px; page-break-inside: avoid; }
.lap .pengesahan td { border: 0; width: 50%; vertical-align: top; padding: 0; }
.lap .pengesahan .blok { width: 220px; margin-left: auto; text-align: center; }
.lap .pengesahan .ruang { height: 62px; }
.lap .catatan { margin-top: 22px; font-size: 10px; color: #666; }
.lap .kosong { text-align: center; padding: 18px; color: #666; border: 1px dashed #999; }
