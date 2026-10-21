@extends('layouts.app')

@php $ubah = $pengguna->exists; @endphp
@section('judul', $ubah ? 'Ubah Akun' : 'Akun Baru')
@section('remah', $ubah ? $pengguna->username : 'Tambah akun pengguna')

@section('konten')
<div class="card form-lebar muncul">
    <form method="POST" action="{{ $ubah ? route('pengguna.update', $pengguna) : route('pengguna.store') }}" class="card-body" novalidate>
        @csrf
        @if ($ubah) @method('PUT') @endif

        <div class="baris-2">
            <div class="field @error('nama_lengkap') ada-galat @enderror">
                <label for="nama_lengkap">Nama lengkap <span class="wajib">*</span></label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $pengguna->nama_lengkap) }}" maxlength="100" required autofocus>
                @include('partials.galat-field', ['nama' => 'nama_lengkap'])
            </div>
            <div class="field @error('username') ada-galat @enderror">
                <label for="username">Nama pengguna <span class="wajib">*</span></label>
                <input type="text" id="username" name="username" value="{{ old('username', $pengguna->username) }}" maxlength="50" autocomplete="off" required>
                @include('partials.galat-field', ['nama' => 'username'])
            </div>
        </div>

        <div class="field @error('role') ada-galat @enderror">
            <label for="role">Peran <span class="wajib">*</span></label>
            <select id="role" name="role" required>
                @foreach (['admin' => 'Admin — mengelola seluruh sistem', 'operator' => 'Operator — mengelola kegiatan miliknya', 'pimpinan' => 'Pimpinan — hanya melihat (baca saja)'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('role', $pengguna->role) === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            @include('partials.galat-field', ['nama' => 'role'])
        </div>

        @unless ($ubah)
            <div class="baris-2">
                <div class="field @error('password') ada-galat @enderror">
                    <label for="password">Kata sandi awal <span class="wajib">*</span></label>
                    <input type="password" id="password" name="password" autocomplete="new-password" minlength="8" required>
                    <span class="bantuan">Minimal 8 karakter.</span>
                    @include('partials.galat-field', ['nama' => 'password'])
                </div>
                <div class="field">
                    <label for="password_confirmation">Ulangi kata sandi <span class="wajib">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                </div>
            </div>
        @else
            <div class="alert alert-info"><x-icon name="info" :size="18" /><div>Untuk mengganti kata sandi, gunakan tombol <b>Reset sandi</b> pada daftar pengguna.</div></div>
        @endunless

        <div class="form-aksi">
            <a href="{{ route('pengguna.index') }}" class="btn btn-putih">Batal</a>
            <button type="submit" class="btn btn-utama"><x-icon name="check" :size="16" /> {{ $ubah ? 'Simpan Perubahan' : 'Buat Akun' }}</button>
        </div>
    </form>
</div>
@endsection
