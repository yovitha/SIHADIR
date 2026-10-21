@include('laporan._rangka-awal', ['judulHalaman' => 'Daftar Hadir – '.$kegiatan->nama_kegiatan, 'kembali' => route('kegiatan.show', $kegiatan)])
@include('laporan._kop')

<h1 class="judul">Daftar Hadir Kegiatan</h1>

<table class="meta">
    <tr><td class="k">Kegiatan</td><td class="t">:</td><td>{{ $kegiatan->nama_kegiatan }}</td></tr>
    <tr><td class="k">Kategori</td><td class="t">:</td><td>{{ $kegiatan->kategori->nama_kategori }}</td></tr>
    <tr><td class="k">Hari / Tanggal</td><td class="t">:</td><td>{{ $kegiatan->tanggal->translatedFormat('l, d F Y') }}</td></tr>
    <tr><td class="k">Waktu</td><td class="t">:</td><td>{{ $kegiatan->jam_mulai_hm }} – {{ $kegiatan->jam_selesai_hm }} WITA</td></tr>
    <tr><td class="k">Tempat</td><td class="t">:</td><td>{{ $kegiatan->lokasi }}</td></tr>
    <tr><td class="k">Penyelenggara</td><td class="t">:</td><td>{{ $kegiatan->penyelenggara ?: config('sihadir.instansi') }}</td></tr>
    <tr><td class="k">Jumlah hadir</td><td class="t">:</td><td><b>{{ $kehadiran->count() }} orang</b></td></tr>
</table>

@if ($kehadiran->isEmpty())
    <div class="kosong">Belum ada peserta yang tercatat hadir pada kegiatan ini.</div>
@else
    <table class="data">
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Instansi</th>
                <th style="width:62px">Waktu</th>
                <th style="width:92px">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($kehadiran as $i => $h)
            <tr class="{{ $i % 2 ? 'zebra' : '' }}">
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $h->nama_tampil }}@if ($h->keterangan)<br><span style="font-size:9.5px;color:#666">{{ $h->keterangan }}</span>@endif</td>
                <td>{{ $h->jabatan_tampil }}</td>
                <td>{{ $h->instansi_tampil }}</td>
                <td class="c">{{ $h->waktu_hadir->format('H:i') }}</td>
                <td class="c">
                    @php $gambar = $pdf ? $h->tandaTanganDataUri() : $h->tanda_tangan_url; @endphp
                    @if ($gambar)
                        <img class="ttd-img" src="{{ $gambar }}" alt="Tanda tangan">
                    @else
                        <span style="color:#888">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@include('laporan._rangka-akhir')
