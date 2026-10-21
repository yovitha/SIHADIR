@extends('layouts.app')

@section('judul', 'Detail Kegiatan')
@section('remah', $kegiatan->kategori->nama_kategori.' · '.$kegiatan->tanggal->translatedFormat('d F Y'))

@section('aksi-atas')
    <a href="{{ route('laporan.kegiatan', $kegiatan) }}" target="_blank" class="btn btn-putih btn-kecil"><x-icon name="printer" :size="16" /> Laporan</a>
@endsection

@section('konten')
@php
    $ubahStatus = function (string $status, string $label, string $ikon, string $kelas, ?string $konfirmasi = null) use ($kegiatan) {
        return compact('status', 'label', 'ikon', 'kelas', 'konfirmasi');
    };
    $aksiStatus = match ($kegiatan->status) {
        'draft' => [$ubahStatus('aktif', 'Buka Presensi', 'play', 'btn-utama'), $ubahStatus('dibatalkan', 'Batalkan', 'slash', 'btn-bahaya', 'Batalkan kegiatan ini?')],
        'aktif' => [$ubahStatus('selesai', 'Tutup Presensi', 'pause', 'btn-hijau', 'Tutup presensi dan tandai kegiatan selesai?'), $ubahStatus('draft', 'Kembali ke Draft', 'edit-2', 'btn-putih'), $ubahStatus('dibatalkan', 'Batalkan', 'slash', 'btn-bahaya', 'Batalkan kegiatan ini?')],
        'selesai' => [$ubahStatus('aktif', 'Buka Kembali', 'refresh-cw', 'btn-putih', 'Buka kembali presensi kegiatan ini?')],
        'dibatalkan' => [$ubahStatus('draft', 'Pulihkan ke Draft', 'refresh-cw', 'btn-putih')],
        default => [],
    };
@endphp

<div class="judul-halaman muncul">
    <div>
        <h2>{{ $kegiatan->nama_kegiatan }}</h2>
        <p><span class="badge {{ $kegiatan->status_class }}">{{ $kegiatan->status_label }}</span>
           <span class="badge badge-kat">{{ $kegiatan->kategori->nama_kategori }}</span></p>
    </div>
    @if ($bisaKelola)
        <div class="tombol-grup">
            @foreach ($aksiStatus as $a)
                <form method="POST" action="{{ route('kegiatan.status', $kegiatan) }}" @if ($a['konfirmasi']) data-konfirmasi="{{ $a['konfirmasi'] }}" @endif>
                    @csrf
                    <input type="hidden" name="status" value="{{ $a['status'] }}">
                    <button type="submit" class="btn {{ $a['kelas'] }}"><x-icon :name="$a['ikon']" :size="16" /> {{ $a['label'] }}</button>
                </form>
            @endforeach
        </div>
    @endif
</div>

<div class="detail-grid">
    <div style="display:grid;gap:20px">
        <div class="card muncul">
            <div class="card-head">
                <h3>Informasi Kegiatan</h3>
                @if ($bisaKelola)
                    <div class="tombol-grup">
                        <a href="{{ route('kegiatan.edit', $kegiatan) }}" class="btn btn-lembut btn-kecil"><x-icon name="edit-2" :size="14" /> Ubah</a>
                        <form method="POST" action="{{ route('kegiatan.destroy', $kegiatan) }}"
                              data-konfirmasi="{{ $kehadiran->count() ? 'Kegiatan ini sudah memiliki kehadiran dan akan ditandai Dibatalkan. Lanjutkan?' : 'Hapus kegiatan ini secara permanen?' }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-bahaya btn-kecil"><x-icon name="trash-2" :size="14" /> {{ $kehadiran->count() ? 'Batalkan' : 'Hapus' }}</button>
                        </form>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="info-baris"><x-icon name="calendar" /><div><small>Tanggal</small><b>{{ $kegiatan->tanggal->translatedFormat('l, d F Y') }}</b></div></div>
                <div class="info-baris"><x-icon name="clock" /><div><small>Waktu</small><b>{{ $kegiatan->jam_mulai_hm }} – {{ $kegiatan->jam_selesai_hm }} WITA</b></div></div>
                <div class="info-baris"><x-icon name="map-pin" /><div><small>Lokasi</small><b>{{ $kegiatan->lokasi }}</b></div></div>
                <div class="info-baris"><x-icon name="briefcase" /><div><small>Penyelenggara</small><b>{{ $kegiatan->penyelenggara ?: '-' }}</b></div></div>
                <div class="info-baris"><x-icon name="user" /><div><small>Dibuat oleh</small><b>{{ $kegiatan->user->nama_lengkap ?? '-' }}</b></div></div>
                @if ($kegiatan->deskripsi)
                    <div class="info-baris"><x-icon name="file-text" /><div><small>Deskripsi</small><b style="font-weight:500">{{ $kegiatan->deskripsi }}</b></div></div>
                @endif
            </div>
        </div>
    </div>

    <div class="card qr-kartu muncul t2">
        <div class="label">Kode kegiatan</div>
        <div class="kode-besar">{{ $kegiatan->kode_unik }}</div>
        <div class="qr-kotak" style="margin-top:16px" data-qr="{{ $kegiatan->url_presensi }}" aria-label="QR Code presensi"></div>
        <div class="tautan-salin">
            <input type="text" id="tautan-presensi" value="{{ $kegiatan->url_presensi }}" readonly aria-label="Tautan presensi">
            <button type="button" class="btn btn-lembut btn-kecil" data-salin="#tautan-presensi"><x-icon name="copy" :size="14" /> Salin</button>
        </div>
        <div class="tombol-grup" style="justify-content:center;margin-top:16px">
            <a href="{{ route('kegiatan.qr', $kegiatan) }}" class="btn btn-hijau btn-kecil"><x-icon name="maximize" :size="14" /> Layar Penuh</a>
            <button type="button" class="btn btn-putih btn-kecil" data-unduh-qr="{{ $kegiatan->url_presensi }}" data-nama="qr-{{ $kegiatan->kode_unik }}"><x-icon name="download" :size="14" /> Unduh</button>
        </div>
        @if ($kegiatan->status !== 'aktif')
            <p class="teks-kecil teks-mute" style="margin-top:14px">Presensi hanya dapat diisi saat status kegiatan <b>Aktif</b>.</p>
        @endif
    </div>
</div>

<div class="card muncul" style="margin-top:20px">
    <div class="card-head">
        <h3>Daftar Hadir <span class="badge badge-aktif" style="margin-left:6px">{{ $jumlahHadir }} hadir</span></h3>
        <a href="{{ route('kegiatan.pantau', $kegiatan) }}" class="btn btn-hijau btn-kecil"><x-icon name="activity" :size="14" /> Pantau Langsung</a>
    </div>
    <div class="tabel-gulir">
        <table class="tbl">
            <thead><tr><th>No</th><th>Peserta</th><th>Waktu hadir</th><th>Tanda tangan</th><th>Status</th>@if ($bisaKelola)<th></th>@endif</tr></thead>
            <tbody>@include('kegiatan._baris-kehadiran', ['kehadiran' => $kehadiran, 'bisaKelola' => $bisaKelola])</tbody>
        </table>
    </div>
</div>
@endsection

@push('vendor')
<script src="{{ asset('vendor/qrcode.js') }}"></script>
@endpush
