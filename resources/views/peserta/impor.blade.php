@extends('layouts.app')

@section('judul', 'Impor Peserta')
@section('remah', 'Unggah daftar peserta dari berkas Excel atau CSV')

@section('aksi-atas')
    <a href="{{ route('peserta.index') }}" class="btn btn-putih btn-kecil">Kembali</a>
@endsection

@section('konten')
<div class="detail-grid">
    <div class="card muncul">
        <div class="card-head"><h3>Unggah Berkas</h3></div>
        <form method="POST" action="{{ route('peserta.impor.proses') }}" enctype="multipart/form-data" class="card-body" novalidate>
            @csrf
            <div class="field @error('berkas') ada-galat @enderror">
                <label for="berkas">Berkas Excel / CSV <span class="wajib">*</span></label>
                <input type="file" id="berkas" name="berkas" accept=".xlsx,.xls,.csv" required>
                <span class="bantuan">Format: .xlsx, .xls, atau .csv, maksimal 2 MB. Baris pertama dianggap judul kolom.</span>
                @include('partials.galat-field', ['nama' => 'berkas'])
            </div>
            <button type="submit" class="btn btn-utama"><x-icon name="upload" :size="16" /> Impor Sekarang</button>
        </form>
    </div>

    <div class="card muncul t2">
        <div class="card-head"><h3>Format Berkas</h3></div>
        <div class="card-body">
            <p class="teks-mute" style="margin-bottom:12px">Urutan kolom harus sama dengan templat:</p>
            <ol style="margin:0 0 16px;padding-left:20px;line-height:1.9">
                <li>NIP/NIK <span class="teks-mute teks-kecil">(opsional, unik)</span></li>
                <li>Nama <span class="teks-mute teks-kecil">(wajib)</span></li>
                <li>Jabatan <span class="teks-mute teks-kecil">(wajib)</span></li>
                <li>Instansi <span class="teks-mute teks-kecil">(wajib; dibuat otomatis jika belum ada)</span></li>
                <li>No. HP <span class="teks-mute teks-kecil">(opsional)</span></li>
                <li>Email <span class="teks-mute teks-kecil">(opsional)</span></li>
            </ol>
            <p class="teks-kecil teks-mute" style="margin-bottom:14px">Baris yang tidak valid dilaporkan dan dilewati, tanpa membatalkan baris lain yang valid.</p>
            <a href="{{ route('peserta.templat') }}" class="btn btn-lembut" data-tanpa-tunda><x-icon name="download" :size="16" /> Unduh Templat</a>
        </div>
    </div>
</div>
@endsection
