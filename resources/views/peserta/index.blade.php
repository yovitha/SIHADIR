@extends('layouts.app')

@section('judul', 'Data Peserta')
@section('remah', 'Peserta terdaftar yang dapat dipilih saat presensi')

@section('aksi-atas')
    <a href="{{ route('peserta.impor') }}" class="btn btn-putih btn-kecil"><x-icon name="upload" :size="16" /> Impor Excel</a>
    <a href="{{ route('peserta.create') }}" class="btn btn-utama btn-kecil"><x-icon name="plus" :size="16" /> Peserta Baru</a>
@endsection

@section('konten')
<div class="card muncul">
    <form method="GET" action="{{ route('peserta.index') }}" class="filter">
        <div class="field lebar">
            <label for="q">Cari peserta</label>
            <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Nama, NIP/NIK, atau jabatan...">
        </div>
        <div class="field">
            <label for="instansi">Instansi</label>
            <select id="instansi" name="instansi">
                <option value="">Semua</option>
                @foreach ($instansi as $i)
                    <option value="{{ $i->id_instansi }}" @selected(request('instansi') == $i->id_instansi)>{{ $i->nama_instansi }}</option>
                @endforeach
            </select>
        </div>
        <div class="tombol-grup">
            <button type="submit" class="btn btn-hijau"><x-icon name="search" :size="16" /> Cari</button>
            @if (request()->hasAny(['q', 'instansi']))
                <a href="{{ route('peserta.index') }}" class="btn btn-putih">Atur ulang</a>
            @endif
        </div>
    </form>

    @if ($peserta->isEmpty())
        <div class="kosong"><x-icon name="users" :size="44" /><p>Tidak ada peserta yang cocok.</p></div>
    @else
        <div class="tabel-gulir">
            <table class="tbl">
                <thead><tr><th>Nama</th><th>NIP/NIK</th><th>Instansi</th><th>Kontak</th><th></th></tr></thead>
                <tbody>
                @foreach ($peserta as $p)
                    <tr>
                        <td><span class="nama">{{ $p->nama }}</span><div class="sub">{{ $p->jabatan }}</div></td>
                        <td>{{ $p->nip_nik ?: '—' }}</td>
                        <td>{{ $p->instansi->nama_instansi }}</td>
                        <td>{{ $p->no_hp ?: '—' }}@if ($p->email)<div class="sub">{{ $p->email }}</div>@endif</td>
                        <td>
                            <div class="aksi">
                                <a class="btn btn-putih btn-kecil btn-ikon" href="{{ route('laporan.peserta', ['id_peserta' => $p->id_peserta]) }}" target="_blank" title="Riwayat kehadiran"><x-icon name="file-text" :size="15" /></a>
                                <a class="btn btn-lembut btn-kecil" href="{{ route('peserta.edit', $p) }}"><x-icon name="edit-2" :size="14" /> Ubah</a>
                                <form method="POST" action="{{ route('peserta.destroy', $p) }}" data-konfirmasi="Hapus peserta {{ $p->nama }}?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-bahaya btn-kecil btn-ikon" title="Hapus"><x-icon name="trash-2" :size="15" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $peserta->links('pagination.sihadir') }}
    @endif
</div>
@endsection
