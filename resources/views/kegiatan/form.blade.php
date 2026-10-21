@extends('layouts.app')

@php $ubah = $kegiatan->exists; @endphp
@section('judul', $ubah ? 'Ubah Kegiatan' : 'Kegiatan Baru')
@section('remah', $ubah ? 'Perbarui data kegiatan' : 'Isi data kegiatan; kode unik dan QR Code dibuat otomatis')

@section('konten')
<div class="card form-lebar muncul">
    <form method="POST" action="{{ $ubah ? route('kegiatan.update', $kegiatan) : route('kegiatan.store') }}" class="card-body" novalidate>
        @csrf
        @if ($ubah) @method('PUT') @endif

        <div class="field @error('nama_kegiatan') ada-galat @enderror">
            <label for="nama_kegiatan">Nama kegiatan <span class="wajib">*</span></label>
            <input type="text" id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" maxlength="150" required autofocus>
            @include('partials.galat-field', ['nama' => 'nama_kegiatan'])
        </div>

        <div class="baris-2">
            <div class="field @error('id_kategori') ada-galat @enderror">
                <label for="id_kategori">Kategori <span class="wajib">*</span></label>
                <select id="id_kategori" name="id_kategori" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($kategori as $kat)
                        <option value="{{ $kat->id_kategori }}" @selected(old('id_kategori', $kegiatan->id_kategori) == $kat->id_kategori)>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
                @include('partials.galat-field', ['nama' => 'id_kategori'])
            </div>
            <div class="field @error('penyelenggara') ada-galat @enderror">
                <label for="penyelenggara">Penyelenggara</label>
                <input type="text" id="penyelenggara" name="penyelenggara" value="{{ old('penyelenggara', $kegiatan->penyelenggara) }}" maxlength="100">
                @include('partials.galat-field', ['nama' => 'penyelenggara'])
            </div>
        </div>

        <div class="baris-3">
            <div class="field @error('tanggal') ada-galat @enderror">
                <label for="tanggal">Tanggal <span class="wajib">*</span></label>
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', optional($kegiatan->tanggal)->toDateString()) }}" required>
                @include('partials.galat-field', ['nama' => 'tanggal'])
            </div>
            <div class="field @error('jam_mulai') ada-galat @enderror">
                <label for="jam_mulai">Jam mulai <span class="wajib">*</span></label>
                <input type="time" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai', $kegiatan->jam_mulai_hm ?: '08:00') }}" required>
                @include('partials.galat-field', ['nama' => 'jam_mulai'])
            </div>
            <div class="field @error('jam_selesai') ada-galat @enderror">
                <label for="jam_selesai">Jam selesai <span class="wajib">*</span></label>
                <input type="time" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai', $kegiatan->jam_selesai_hm ?: '10:00') }}" required>
                @include('partials.galat-field', ['nama' => 'jam_selesai'])
            </div>
        </div>

        <div class="field @error('lokasi') ada-galat @enderror">
            <label for="lokasi">Lokasi <span class="wajib">*</span></label>
            <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi', $kegiatan->lokasi) }}" maxlength="150" required>
            @include('partials.galat-field', ['nama' => 'lokasi'])
        </div>

        <div class="field @error('deskripsi') ada-galat @enderror">
            <label for="deskripsi">Deskripsi (opsional)</label>
            <textarea id="deskripsi" name="deskripsi" maxlength="2000">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
            @include('partials.galat-field', ['nama' => 'deskripsi'])
        </div>

        <div class="field">
            <label class="cek">
                <input type="checkbox" name="tanda_tangan_wajib" value="1" @checked(old('tanda_tangan_wajib', $kegiatan->tanda_tangan_wajib))>
                Wajibkan tanda tangan digital pada presensi
            </label>
            <span class="bantuan">Peserta menggambar tanda tangan pada layar sentuh. Presensi hanya diterima {{ config('sihadir.toleransi_menit') }} menit sebelum jam mulai sampai {{ config('sihadir.toleransi_menit') }} menit sesudah jam selesai.</span>
        </div>

        <div class="form-aksi">
            <a href="{{ $ubah ? route('kegiatan.show', $kegiatan) : route('kegiatan.index') }}" class="btn btn-putih">Batal</a>
            <button type="submit" class="btn btn-utama"><x-icon name="check" :size="16" /> {{ $ubah ? 'Simpan Perubahan' : 'Simpan Kegiatan' }}</button>
        </div>
    </form>
</div>
@endsection
