@extends('layouts.app')

@section('judul', 'Profil Saya')
@section('remah', 'Ubah nama dan kata sandi akun Anda')

@section('konten')
<div class="detail-grid">
    <div class="card muncul">
        <div class="card-head"><h3>Data Akun</h3></div>
        <form method="POST" action="{{ route('profil.update') }}" class="card-body" novalidate>
            @csrf @method('PUT')

            <div class="field @error('nama_lengkap') ada-galat @enderror">
                <label for="nama_lengkap">Nama lengkap <span class="wajib">*</span></label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $pengguna->nama_lengkap) }}" maxlength="100" required>
                @include('partials.galat-field', ['nama' => 'nama_lengkap'])
            </div>

            <h4 style="margin:24px 0 12px;font-size:16px">Ganti kata sandi <span class="teks-mute teks-kecil" style="font-weight:500">(kosongkan bila tidak diganti)</span></h4>

            <div class="field @error('password_lama') ada-galat @enderror">
                <label for="password_lama">Kata sandi saat ini</label>
                <input type="password" id="password_lama" name="password_lama" autocomplete="current-password">
                @include('partials.galat-field', ['nama' => 'password_lama'])
            </div>
            <div class="baris-2">
                <div class="field @error('password_baru') ada-galat @enderror">
                    <label for="password_baru">Kata sandi baru</label>
                    <input type="password" id="password_baru" name="password_baru" autocomplete="new-password" minlength="8">
                    <span class="bantuan">Minimal 8 karakter.</span>
                    @include('partials.galat-field', ['nama' => 'password_baru'])
                </div>
                <div class="field">
                    <label for="password_baru_confirmation">Ulangi kata sandi baru</label>
                    <input type="password" id="password_baru_confirmation" name="password_baru_confirmation" autocomplete="new-password">
                </div>
            </div>

            <div class="form-aksi">
                <button type="submit" class="btn btn-utama"><x-icon name="check" :size="16" /> Simpan Profil</button>
            </div>
        </form>
    </div>

    <div class="card qr-kartu muncul t2">
        <span class="avatar" style="width:72px;height:72px;font-size:26px;margin:0 auto 12px">{{ $pengguna->inisial }}</span>
        <h3>{{ $pengguna->nama_lengkap }}</h3>
        <p class="teks-mute" style="margin:4px 0 12px">{{ $pengguna->username }}</p>
        <span class="badge badge-{{ $pengguna->role }}">{{ $pengguna->role_label }}</span>
    </div>
</div>
@endsection
