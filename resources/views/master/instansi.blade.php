@extends('layouts.app')

@section('judul', 'Instansi')
@section('remah', 'Data instansi, perangkat daerah, atau bidang asal peserta')

@section('konten')
<div class="detail-grid">
    <div class="card muncul">
        <div class="card-head"><h3>Daftar Instansi</h3><span class="badge badge-kat">{{ $instansi->total() }} instansi</span></div>
        @if ($instansi->isEmpty())
            <div class="kosong"><x-icon name="briefcase" :size="40" /><p>Belum ada data instansi.</p></div>
        @else
            <div class="tabel-gulir">
                <table class="tbl">
                    <thead><tr><th>Instansi</th><th>Peserta</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($instansi as $i)
                        <tr>
                            <td><span class="nama">{{ $i->nama_instansi }}</span>@if ($i->alamat)<div class="sub">{{ $i->alamat }}</div>@endif</td>
                            <td>{{ $i->peserta_count }}</td>
                            <td>
                                <div class="aksi">
                                    <a class="btn btn-lembut btn-kecil" href="{{ route('instansi.edit', $i) }}"><x-icon name="edit-2" :size="14" /> Ubah</a>
                                    <form method="POST" action="{{ route('instansi.destroy', $i) }}" data-konfirmasi="Hapus instansi {{ $i->nama_instansi }}?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-bahaya btn-kecil" @disabled($i->peserta_count > 0) title="{{ $i->peserta_count > 0 ? 'Masih dipakai data peserta' : 'Hapus' }}"><x-icon name="trash-2" :size="14" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $instansi->links('pagination.sihadir') }}
        @endif
    </div>

    <div class="card muncul t2">
        <div class="card-head"><h3>Tambah Instansi</h3></div>
        <form method="POST" action="{{ route('instansi.store') }}" class="card-body" novalidate>
            @csrf
            <div class="field @error('nama_instansi') ada-galat @enderror">
                <label for="nama_instansi">Nama instansi <span class="wajib">*</span></label>
                <input type="text" id="nama_instansi" name="nama_instansi" value="{{ old('nama_instansi') }}" maxlength="150" required>
                @include('partials.galat-field', ['nama' => 'nama_instansi'])
            </div>
            <div class="field @error('alamat') ada-galat @enderror">
                <label for="alamat">Alamat (opsional)</label>
                <input type="text" id="alamat" name="alamat" value="{{ old('alamat') }}" maxlength="200">
                @include('partials.galat-field', ['nama' => 'alamat'])
            </div>
            <button type="submit" class="btn btn-utama btn-blok"><x-icon name="plus" :size="16" /> Tambah</button>
        </form>
    </div>
</div>
@endsection
