@extends('layouts.app')

@section('judul', 'Laporan')
@section('remah', 'Rekap kehadiran per kegiatan, periode, dan peserta — cetak, PDF, atau Excel')

@section('konten')
<div class="grid-2" style="margin-bottom:20px">
    {{-- ============ Laporan per periode ============ --}}
    <div class="card muncul">
        <div class="card-head"><h3>Laporan per Periode</h3><span class="badge badge-kat">Rekap</span></div>
        <form method="GET" action="{{ route('laporan.periode') }}" target="_blank" class="card-body">
            <div class="baris-2">
                <div class="field">
                    <label for="dari">Dari tanggal <span class="wajib">*</span></label>
                    <input type="date" id="dari" name="dari" value="{{ now()->startOfMonth()->toDateString() }}" required>
                </div>
                <div class="field">
                    <label for="sampai">Sampai tanggal <span class="wajib">*</span></label>
                    <input type="date" id="sampai" name="sampai" value="{{ now()->endOfMonth()->toDateString() }}" required>
                </div>
            </div>
            <div class="field">
                <label for="id_kategori">Kategori</label>
                <select id="id_kategori" name="id_kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $kat)
                        <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tombol-grup">
                <button type="submit" name="format" value="html" class="btn btn-utama"><x-icon name="eye" :size="16" /> Lihat / Cetak</button>
                <button type="submit" name="format" value="pdf" class="btn btn-putih"><x-icon name="download" :size="16" /> PDF</button>
                <button type="submit" name="format" value="xlsx" class="btn btn-putih"><x-icon name="download" :size="16" /> Excel</button>
            </div>
        </form>
    </div>

    {{-- ============ Laporan per peserta ============ --}}
    <div class="card muncul t2">
        <div class="card-head"><h3>Laporan per Peserta</h3><span class="badge badge-kat">Riwayat</span></div>
        @if ($peserta->isEmpty())
            <div class="kosong"><x-icon name="users" :size="40" /><p>Belum ada data peserta terdaftar.</p></div>
        @else
            <form method="GET" action="{{ route('laporan.peserta') }}" target="_blank" class="card-body">
                <div class="field">
                    <label for="id_peserta">Peserta <span class="wajib">*</span></label>
                    <select id="id_peserta" name="id_peserta" required>
                        <option value="">Pilih peserta</option>
                        @foreach ($peserta as $p)
                            <option value="{{ $p->id_peserta }}">{{ $p->nama }} — {{ $p->instansi->nama_instansi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="baris-2">
                    <div class="field"><label for="p_dari">Dari tanggal</label><input type="date" id="p_dari" name="dari"></div>
                    <div class="field"><label for="p_sampai">Sampai tanggal</label><input type="date" id="p_sampai" name="sampai"></div>
                </div>
                <div class="tombol-grup">
                    <button type="submit" name="format" value="html" class="btn btn-utama"><x-icon name="eye" :size="16" /> Lihat / Cetak</button>
                    <button type="submit" name="format" value="pdf" class="btn btn-putih"><x-icon name="download" :size="16" /> PDF</button>
                    <button type="submit" name="format" value="xlsx" class="btn btn-putih"><x-icon name="download" :size="16" /> Excel</button>
                </div>
            </form>
        @endif
    </div>
</div>

{{-- ============ Laporan per kegiatan ============ --}}
<div class="card muncul">
    <div class="card-head">
        <h3>Laporan per Kegiatan</h3>
        <a href="{{ route('kegiatan.index') }}" class="btn btn-putih btn-kecil">Semua kegiatan <x-icon name="arrow-right" :size="14" /></a>
    </div>
    @if ($kegiatan->isEmpty())
        <div class="kosong"><x-icon name="file-text" :size="44" /><p>Belum ada kegiatan aktif atau selesai yang dapat dilaporkan.</p></div>
    @else
        <div class="tabel-gulir">
            <table class="tbl">
                <thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Hadir</th><th>Status</th><th>Unduh laporan</th></tr></thead>
                <tbody>
                @foreach ($kegiatan as $k)
                    <tr>
                        <td><a class="nama" href="{{ route('kegiatan.show', $k) }}">{{ $k->nama_kegiatan }}</a><div class="sub">{{ $k->kategori->nama_kategori }} · {{ $k->lokasi }}</div></td>
                        <td>{{ $k->tanggal->translatedFormat('d M Y') }}</td>
                        <td><b>{{ $k->hadir_count }}</b></td>
                        <td><span class="badge {{ $k->status_class }}">{{ $k->status_label }}</span></td>
                        <td>
                            <div class="aksi">
                                <a class="btn btn-lembut btn-kecil" href="{{ route('laporan.kegiatan', $k) }}" target="_blank"><x-icon name="printer" :size="14" /> Cetak</a>
                                <a class="btn btn-putih btn-kecil" href="{{ route('laporan.kegiatan', ['kegiatan' => $k, 'format' => 'pdf']) }}" data-tanpa-tunda>PDF</a>
                                <a class="btn btn-putih btn-kecil" href="{{ route('laporan.kegiatan', ['kegiatan' => $k, 'format' => 'xlsx']) }}" data-tanpa-tunda>Excel</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
