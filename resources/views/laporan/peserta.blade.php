@include('laporan._rangka-awal', ['judulHalaman' => 'Riwayat Kehadiran – '.$peserta->nama, 'kembali' => route('laporan.index')])
@include('laporan._kop')

<h1 class="judul">Riwayat Kehadiran Peserta</h1>

<table class="meta">
    <tr><td class="k">Nama</td><td class="t">:</td><td>{{ $peserta->nama }}</td></tr>
    @if ($peserta->nip_nik)<tr><td class="k">NIP / NIK</td><td class="t">:</td><td>{{ $peserta->nip_nik }}</td></tr>@endif
    <tr><td class="k">Jabatan</td><td class="t">:</td><td>{{ $peserta->jabatan }}</td></tr>
    <tr><td class="k">Instansi</td><td class="t">:</td><td>{{ $peserta->instansi->nama_instansi }}</td></tr>
    <tr><td class="k">Periode</td><td class="t">:</td><td>
        @if ($dari && $sampai) {{ $dari->translatedFormat('d F Y') }} s.d. {{ $sampai->translatedFormat('d F Y') }}
        @elseif ($dari) Sejak {{ $dari->translatedFormat('d F Y') }}
        @elseif ($sampai) Sampai {{ $sampai->translatedFormat('d F Y') }}
        @else Seluruh periode @endif
    </td></tr>
    <tr><td class="k">Kegiatan dihadiri</td><td class="t">:</td><td><b>{{ $riwayat->count() }} kegiatan</b></td></tr>
</table>

@if ($riwayat->isEmpty())
    <div class="kosong">Belum ada riwayat kehadiran untuk peserta ini pada periode yang dipilih.</div>
@else
    <table class="data">
        <thead>
            <tr><th style="width:30px">No</th><th style="width:72px">Tanggal</th><th>Kegiatan</th><th>Kategori</th><th>Lokasi</th><th style="width:62px">Waktu Hadir</th></tr>
        </thead>
        <tbody>
        @foreach ($riwayat as $i => $h)
            <tr class="{{ $i % 2 ? 'zebra' : '' }}">
                <td class="c">{{ $i + 1 }}</td>
                <td class="c">{{ $h->kegiatan->tanggal->format('d/m/Y') }}</td>
                <td>{{ $h->kegiatan->nama_kegiatan }}</td>
                <td>{{ $h->kegiatan->kategori->nama_kategori }}</td>
                <td>{{ $h->kegiatan->lokasi }}</td>
                <td class="c">{{ $h->waktu_hadir->format('H:i') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@include('laporan._rangka-akhir')
