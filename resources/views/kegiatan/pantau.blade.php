@extends('layouts.app')

@section('judul', 'Pantau Kehadiran')
@section('remah', $kegiatan->nama_kegiatan)

@section('aksi-atas')
    <a href="{{ route('kegiatan.show', $kegiatan) }}" class="btn btn-putih btn-kecil"><x-icon name="eye" :size="16" /> Detail</a>
@endsection

@section('konten')
<div class="card muncul" id="pantau" data-url="{{ route('kegiatan.pantau.data', $kegiatan) }}">
    <div class="card-head">
        <div class="pantau-angka">
            <div class="angka-raksasa" id="angka-hadir">{{ $kehadiran->where('status', 'tercatat')->count() }}</div>
            <div>
                <b style="display:block;color:var(--hijau-900)">peserta hadir</b>
                <span class="teks-kecil teks-mute">Status: <b id="status-kegiatan">{{ $kegiatan->status_label }}</b> · {{ $kegiatan->jam_mulai_hm }}–{{ $kegiatan->jam_selesai_hm }} WITA</span>
            </div>
        </div>
        <div class="tombol-grup" style="align-items:center">
            <span class="penanda-langsung" id="penanda"><i class="titik-hidup" style="margin:0"></i><span>Pembaruan otomatis</span></span>
            <label class="cek teks-kecil"><input type="checkbox" id="saklar-otomatis" checked> Otomatis</label>
            <button type="button" class="btn btn-lembut btn-kecil" id="muat-ulang"><x-icon name="refresh-cw" :size="14" /> Muat ulang</button>
            <a href="{{ route('kegiatan.qr', $kegiatan) }}" class="btn btn-hijau btn-kecil"><x-icon name="grid" :size="14" /> QR Code</a>
        </div>
    </div>
    <div class="tabel-gulir">
        <table class="tbl">
            <thead><tr><th>No</th><th>Peserta</th><th>Waktu hadir</th><th>Tanda tangan</th><th>Status</th>@if ($bisaKelola)<th></th>@endif</tr></thead>
            <tbody id="badan-kehadiran">@include('kegiatan._baris-kehadiran')</tbody>
        </table>
    </div>
</div>

@if ($bisaKelola && $kegiatan->status !== 'dibatalkan')
<div class="card muncul t2" style="margin-top:20px">
    <div class="card-head"><h3>Tambah Kehadiran Manual</h3><span class="teks-kecil teks-mute">Untuk peserta yang tidak dapat memakai ponsel</span></div>
    <form method="POST" action="{{ route('kehadiran.manual', $kegiatan) }}" class="card-body" novalidate>
        @csrf
        <div class="field @error('id_peserta') ada-galat @enderror">
            <label for="id_peserta">Peserta terdaftar</label>
            <select id="id_peserta" name="id_peserta">
                <option value="">— Pilih peserta, atau isi data tamu di bawah —</option>
                @foreach ($peserta as $p)
                    <option value="{{ $p->id_peserta }}" @selected(old('id_peserta') == $p->id_peserta)>{{ $p->nama }} — {{ $p->jabatan }} ({{ $p->instansi->nama_instansi }})</option>
                @endforeach
            </select>
        </div>
        <div class="baris-3">
            <div class="field @error('nama') ada-galat @enderror"><label for="nama">Nama tamu</label><input type="text" id="nama" name="nama" value="{{ old('nama') }}" maxlength="100">@include('partials.galat-field', ['nama' => 'nama'])</div>
            <div class="field @error('instansi') ada-galat @enderror"><label for="instansi">Instansi</label><input type="text" id="instansi" name="instansi" value="{{ old('instansi') }}" maxlength="150">@include('partials.galat-field', ['nama' => 'instansi'])</div>
            <div class="field @error('jabatan') ada-galat @enderror"><label for="jabatan">Jabatan</label><input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan') }}" maxlength="100">@include('partials.galat-field', ['nama' => 'jabatan'])</div>
        </div>
        <div class="form-aksi"><button type="submit" class="btn btn-utama"><x-icon name="user-plus" :size="16" /> Tambahkan</button></div>
    </form>
</div>
@endif
@endsection

@push('skrip')
<script src="{{ asset('js/pantau.js') }}?v=1"></script>
@endpush
