@extends('layouts.app')

@section('judul', 'Ubah Instansi')
@section('remah', $instansi->nama_instansi)

@section('konten')
<div class="card form-lebar muncul">
    <form method="POST" action="{{ route('instansi.update', $instansi) }}" class="card-body" novalidate>
        @csrf @method('PUT')
        <div class="field @error('nama_instansi') ada-galat @enderror">
            <label for="nama_instansi">Nama instansi <span class="wajib">*</span></label>
            <input type="text" id="nama_instansi" name="nama_instansi" value="{{ old('nama_instansi', $instansi->nama_instansi) }}" maxlength="150" required autofocus>
            @include('partials.galat-field', ['nama' => 'nama_instansi'])
        </div>
        <div class="field @error('alamat') ada-galat @enderror">
            <label for="alamat">Alamat (opsional)</label>
            <input type="text" id="alamat" name="alamat" value="{{ old('alamat', $instansi->alamat) }}" maxlength="200">
            @include('partials.galat-field', ['nama' => 'alamat'])
        </div>
        <div class="form-aksi">
            <a href="{{ route('instansi.index') }}" class="btn btn-putih">Batal</a>
            <button type="submit" class="btn btn-utama"><x-icon name="check" :size="16" /> Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
