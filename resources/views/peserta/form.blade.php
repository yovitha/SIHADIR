@extends('layouts.app')

@php $ubah = $peserta->exists; @endphp
@section('judul', $ubah ? 'Ubah Peserta' : 'Peserta Baru')
@section('remah', $ubah ? $peserta->nama : 'Tambah peserta terdaftar')

@section('konten')
<div class="card form-lebar muncul">
    <form method="POST" action="{{ $ubah ? route('peserta.update', $peserta) : route('peserta.store') }}" class="card-body" novalidate>
        @csrf
        @if ($ubah) @method('PUT') @endif

        <div class="baris-2">
            <div class="field @error('nama') ada-galat @enderror">
                <label for="nama">Nama lengkap <span class="wajib">*</span></label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $peserta->nama) }}" maxlength="100" required autofocus>
                @include('partials.galat-field', ['nama' => 'nama'])
            </div>
            <div class="field @error('nip_nik') ada-galat @enderror">
                <label for="nip_nik">NIP / NIK (opsional)</label>
                <input type="text" id="nip_nik" name="nip_nik" value="{{ old('nip_nik', $peserta->nip_nik) }}" maxlength="25" inputmode="numeric">
                @include('partials.galat-field', ['nama' => 'nip_nik'])
            </div>
        </div>

        <div class="baris-2">
            <div class="field @error('jabatan') ada-galat @enderror">
                <label for="jabatan">Jabatan <span class="wajib">*</span></label>
                <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $peserta->jabatan) }}" maxlength="100" required>
                @include('partials.galat-field', ['nama' => 'jabatan'])
            </div>
            <div class="field @error('id_instansi') ada-galat @enderror">
                <label for="id_instansi">Instansi <span class="wajib">*</span></label>
                <select id="id_instansi" name="id_instansi" required>
                    <option value="">Pilih instansi</option>
                    @foreach ($instansi as $i)
                        <option value="{{ $i->id_instansi }}" @selected(old('id_instansi', $peserta->id_instansi) == $i->id_instansi)>{{ $i->nama_instansi }}</option>
                    @endforeach
                </select>
                @include('partials.galat-field', ['nama' => 'id_instansi'])
            </div>
        </div>

        <div class="baris-2">
            <div class="field @error('no_hp') ada-galat @enderror">
                <label for="no_hp">Nomor telepon (opsional)</label>
                <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp', $peserta->no_hp) }}" maxlength="20">
                @include('partials.galat-field', ['nama' => 'no_hp'])
            </div>
            <div class="field @error('email') ada-galat @enderror">
                <label for="email">Email (opsional)</label>
                <input type="email" id="email" name="email" value="{{ old('email', $peserta->email) }}" maxlength="100">
                @include('partials.galat-field', ['nama' => 'email'])
            </div>
        </div>

        <div class="form-aksi">
            <a href="{{ route('peserta.index') }}" class="btn btn-putih">Batal</a>
            <button type="submit" class="btn btn-utama"><x-icon name="check" :size="16" /> {{ $ubah ? 'Simpan Perubahan' : 'Simpan Peserta' }}</button>
        </div>
    </form>
</div>
@endsection
