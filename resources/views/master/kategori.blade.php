@extends('layouts.app')

@section('judul', 'Kategori Kegiatan')
@section('remah', 'Jenis kegiatan yang dapat dipilih saat membuat kegiatan')

@section('konten')
<div class="detail-grid">
    <div class="card muncul">
        <div class="card-head"><h3>Daftar Kategori</h3><span class="badge badge-kat">{{ $kategori->count() }} kategori</span></div>
        @if ($kategori->isEmpty())
            <div class="kosong"><x-icon name="tag" :size="40" /><p>Belum ada kategori.</p></div>
        @else
            <div class="tabel-gulir">
                <table class="tbl">
                    <thead><tr><th>Nama kategori</th><th>Dipakai</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($kategori as $kat)
                        <tr>
                            <td>
                                <form method="POST" action="{{ route('kategori.update', $kat) }}" class="aksi" style="gap:8px">
                                    @csrf @method('PUT')
                                    <div class="field" style="margin:0;flex:1;min-width:180px">
                                        <input type="text" name="nama_kategori" value="{{ old('nama_kategori_'.$kat->id_kategori, $kat->nama_kategori) }}" maxlength="50" required aria-label="Nama kategori">
                                    </div>
                                    <button type="submit" class="btn btn-lembut btn-kecil"><x-icon name="check" :size="14" /> Simpan</button>
                                </form>
                            </td>
                            <td>{{ $kat->kegiatan_count }} kegiatan</td>
                            <td>
                                <form method="POST" action="{{ route('kategori.destroy', $kat) }}" data-konfirmasi="Hapus kategori {{ $kat->nama_kategori }}?" class="aksi">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-bahaya btn-kecil" @disabled($kat->kegiatan_count > 0) title="{{ $kat->kegiatan_count > 0 ? 'Masih dipakai kegiatan' : 'Hapus' }}"><x-icon name="trash-2" :size="14" /> Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card muncul t2">
        <div class="card-head"><h3>Tambah Kategori</h3></div>
        <form method="POST" action="{{ route('kategori.store') }}" class="card-body" novalidate>
            @csrf
            <div class="field @error('nama_kategori') ada-galat @enderror">
                <label for="nama_kategori">Nama kategori <span class="wajib">*</span></label>
                <input type="text" id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori') }}" maxlength="50" required>
                @include('partials.galat-field', ['nama' => 'nama_kategori'])
            </div>
            <button type="submit" class="btn btn-utama btn-blok"><x-icon name="plus" :size="16" /> Tambah</button>
        </form>
    </div>
</div>
@endsection
