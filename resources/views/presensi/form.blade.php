@extends('layouts.dasar')

@section('judul', 'Isi Daftar Hadir')

@section('badan')
<div class="presensi">
    <div class="presensi-wadah">
        <div class="presensi-kop">
            <img src="{{ asset('images/logo-tomohon.png') }}" alt="Lambang Kota Tomohon">
            <div><b>SIHADIR</b><small>{{ config('sihadir.instansi') }}</small></div>
        </div>

        <section class="kegiatan-kepala muncul">
            <span class="badge">{{ $kegiatan->kategori->nama_kategori }}</span>
            <h1>{{ $kegiatan->nama_kegiatan }}</h1>
            <ul>
                <li><x-icon name="calendar" :size="17" /> {{ $kegiatan->tanggal->translatedFormat('l, d F Y') }}</li>
                <li><x-icon name="clock" :size="17" /> {{ $kegiatan->jam_mulai_hm }} – {{ $kegiatan->jam_selesai_hm }} WITA</li>
                <li><x-icon name="map-pin" :size="17" /> {{ $kegiatan->lokasi }}</li>
            </ul>
        </section>

        @if ($sudahHadir)
            <div class="card card-pad muncul t2">
                <div class="alert alert-info" style="margin:0">
                    <x-icon name="info" :size="20" />
                    <div>
                        Perangkat ini sudah dipakai untuk mengisi daftar hadir kegiatan ini.
                        Jika Anda perlu mengisi untuk orang lain atau terjadi kekeliruan, hubungi operator kegiatan.
                    </div>
                </div>
            </div>
        @else
            <div class="card muncul t2">
                <form method="POST" action="{{ route('presensi.simpan', $kegiatan->kode_unik) }}" id="form-presensi"
                      data-url-cari="{{ route('presensi.cari', $kegiatan->kode_unik) }}" class="card-body" novalidate>
                    @csrf
                    <input type="hidden" name="id_peserta" id="id_peserta" value="{{ old('id_peserta') }}">

                    <div class="field posisi-rel @error('nama') ada-galat @enderror">
                        <label for="nama">Nama lengkap <span class="wajib">*</span></label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama') }}" maxlength="100"
                               autocomplete="off" placeholder="Ketik nama Anda" required>
                        <div class="saran" id="saran-peserta" hidden></div>
                        @include('partials.galat-field', ['nama' => 'nama'])
                    </div>

                    <div class="alert alert-ok" id="terdaftar" hidden style="margin-top:-4px">
                        <x-icon name="check-circle" :size="18" />
                        <div>
                            Data Anda ditemukan pada peserta terdaftar.
                            <button type="button" id="lepas-peserta" class="btn btn-putih btn-kecil" style="margin-left:6px">Bukan saya / isi manual</button>
                        </div>
                    </div>

                    <div class="field @error('instansi') ada-galat @enderror">
                        <label for="instansi">Instansi / asal <span class="wajib">*</span></label>
                        <input type="text" id="instansi" name="instansi" value="{{ old('instansi', config('sihadir.instansi')) }}" maxlength="150" required>
                        @include('partials.galat-field', ['nama' => 'instansi'])
                    </div>

                    <div class="field @error('jabatan') ada-galat @enderror">
                        <label for="jabatan">Jabatan <span class="wajib">*</span></label>
                        <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan') }}" maxlength="100" required>
                        @include('partials.galat-field', ['nama' => 'jabatan'])
                    </div>

                    <div class="field @error('no_hp') ada-galat @enderror">
                        <label for="no_hp">Nomor telepon (opsional)</label>
                        <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" maxlength="20" inputmode="tel" placeholder="08xxxxxxxxxx">
                        @include('partials.galat-field', ['nama' => 'no_hp'])
                    </div>

                    <div class="field @error('tanda_tangan') ada-galat @enderror">
                        <label>Tanda tangan @if ($kegiatan->tanda_tangan_wajib)<span class="wajib">*</span>@else(opsional)@endif</label>
                        <div class="ttd-kotak" id="ttd-kotak">
                            <canvas id="kanvas-ttd" data-wajib="{{ $kegiatan->tanda_tangan_wajib ? '1' : '0' }}"></canvas>
                            <div class="petunjuk">Tanda tangan di sini dengan jari atau mouse</div>
                        </div>
                        <div class="ttd-aksi">
                            <span class="galat" id="galat-ttd">@error('tanda_tangan'){{ $message }}@enderror</span>
                            <button type="button" id="hapus-ttd" class="btn btn-putih btn-kecil"><x-icon name="x" :size="14" /> Hapus</button>
                        </div>
                        <input type="hidden" name="tanda_tangan" id="tanda_tangan">
                    </div>

                    <button type="submit" class="btn btn-utama btn-besar btn-blok"><x-icon name="check-circle" :size="20" /> Kirim Kehadiran</button>
                    <p class="teks-kecil teks-mute tengah" style="margin-top:12px">Waktu hadir dicatat otomatis oleh sistem saat Anda menekan tombol kirim.</p>
                </form>
            </div>
        @endif

        <p class="teks-kecil teks-mute tengah" style="margin-top:20px">&copy; {{ date('Y') }} {{ config('sihadir.instansi') }}</p>
    </div>
</div>
@endsection

@push('vendor')
<script src="{{ asset('vendor/signature_pad.min.js') }}"></script>
@endpush

@push('skrip')
<script src="{{ asset('js/presensi.js') }}?v=1"></script>
@endpush
