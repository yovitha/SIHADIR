@extends('layouts.app')

@section('judul', 'Kegiatan')
@section('remah', 'Daftar seluruh kegiatan dan status presensinya')

@if (auth()->user()->bisaMengelola())
    @section('aksi-atas')
        <a href="{{ route('kegiatan.create') }}" class="btn btn-utama btn-kecil"><x-icon name="plus" :size="16" /> Kegiatan Baru</a>
    @endsection
@endif

@section('konten')
<div class="card muncul">
    <form method="GET" action="{{ route('kegiatan.index') }}" class="filter">
        <div class="field lebar">
            <label for="q">Cari kegiatan</label>
            <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Ketik nama kegiatan...">
        </div>
        <div class="field">
            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori">
                <option value="">Semua</option>
                @foreach ($kategori as $kat)
                    <option value="{{ $kat->id_kategori }}" @selected(request('kategori') == $kat->id_kategori)>{{ $kat->nama_kategori }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}">
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Semua</option>
                @foreach (\App\Models\Kegiatan::STATUS as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="tombol-grup">
            <button type="submit" class="btn btn-hijau"><x-icon name="search" :size="16" /> Cari</button>
            @if (request()->hasAny(['q', 'kategori', 'tanggal', 'status']))
                <a href="{{ route('kegiatan.index') }}" class="btn btn-putih">Atur ulang</a>
            @endif
        </div>
    </form>

    @if ($kegiatan->isEmpty())
        <div class="kosong"><x-icon name="calendar" :size="44" /><p>Tidak ada kegiatan yang cocok dengan pencarian.</p></div>
    @else
        <div class="tabel-gulir">
            <table class="tbl">
                <thead><tr><th>Kegiatan</th><th>Waktu</th><th>Kategori</th><th>Hadir</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($kegiatan as $k)
                    <tr>
                        <td>
                            <a class="nama" href="{{ route('kegiatan.show', $k) }}">{{ $k->nama_kegiatan }}</a>
                            <div class="sub"><x-icon name="map-pin" :size="13" /> {{ $k->lokasi }} · {{ $k->user->nama_lengkap ?? '-' }}</div>
                        </td>
                        <td>{{ $k->tanggal->translatedFormat('d M Y') }}<div class="sub">{{ $k->jam_mulai_hm }}–{{ $k->jam_selesai_hm }} WITA</div></td>
                        <td><span class="badge badge-kat">{{ $k->kategori->nama_kategori }}</span></td>
                        <td><b>{{ $k->hadir_count }}</b></td>
                        <td><span class="badge {{ $k->status_class }}">{{ $k->status_label }}</span></td>
                        <td>
                            <div class="aksi">
                                <a class="btn btn-lembut btn-kecil" href="{{ route('kegiatan.show', $k) }}"><x-icon name="eye" :size="15" /> Detail</a>
                                <a class="btn btn-putih btn-kecil btn-ikon" href="{{ route('kegiatan.qr', $k) }}" title="QR Code"><x-icon name="grid" :size="15" /></a>
                                <a class="btn btn-putih btn-kecil btn-ikon" href="{{ route('kegiatan.pantau', $k) }}" title="Pantau"><x-icon name="activity" :size="15" /></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $kegiatan->links('pagination.sihadir') }}
    @endif
</div>
@endsection
