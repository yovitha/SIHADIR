@include('laporan._rangka-awal', ['judulHalaman' => 'Rekap Kehadiran '.$dari->translatedFormat('d M Y').' – '.$sampai->translatedFormat('d M Y'), 'kembali' => route('laporan.index')])
@include('laporan._kop')

<h1 class="judul">Rekapitulasi Kehadiran Kegiatan</h1>

<table class="meta">
    <tr><td class="k">Periode</td><td class="t">:</td><td>{{ $dari->translatedFormat('d F Y') }} s.d. {{ $sampai->translatedFormat('d F Y') }}</td></tr>
    <tr><td class="k">Kategori</td><td class="t">:</td><td>{{ $kategoriDipilih ?? 'Semua kategori' }}</td></tr>
    <tr><td class="k">Jumlah kegiatan</td><td class="t">:</td><td><b>{{ $daftar->count() }}</b></td></tr>
    <tr><td class="k">Total kehadiran</td><td class="t">:</td><td><b>{{ $totalHadir }} orang</b></td></tr>
</table>

@if ($daftar->isEmpty())
    <div class="kosong">Tidak ada kegiatan pada periode dan kategori yang dipilih.</div>
@else
    @if ($rekapKategori->count() > 1)
        <table class="data" style="margin-bottom:16px">
            <thead><tr><th>Kategori</th><th style="width:110px">Jumlah Kegiatan</th><th style="width:110px">Total Hadir</th></tr></thead>
            <tbody>
            @foreach ($rekapKategori as $nama => $r)
                <tr class="{{ $loop->odd ? '' : 'zebra' }}"><td>{{ $nama }}</td><td class="c">{{ $r['kegiatan'] }}</td><td class="c">{{ $r['hadir'] }}</td></tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <table class="data">
        <thead>
            <tr><th style="width:30px">No</th><th style="width:72px">Tanggal</th><th>Kegiatan</th><th>Kategori</th><th>Lokasi</th><th style="width:62px">Status</th><th style="width:48px">Hadir</th></tr>
        </thead>
        <tbody>
        @foreach ($daftar as $i => $k)
            <tr class="{{ $i % 2 ? 'zebra' : '' }}">
                <td class="c">{{ $i + 1 }}</td>
                <td class="c">{{ $k->tanggal->format('d/m/Y') }}</td>
                <td>{{ $k->nama_kegiatan }}</td>
                <td>{{ $k->kategori->nama_kategori }}</td>
                <td>{{ $k->lokasi }}</td>
                <td class="c">{{ $k->status_label }}</td>
                <td class="c"><b>{{ $k->hadir_count }}</b></td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="6" class="r" style="font-weight:bold;padding:6px">Total kehadiran</td><td class="c" style="font-weight:bold">{{ $totalHadir }}</td></tr>
        </tfoot>
    </table>
@endif

@include('laporan._rangka-akhir')
