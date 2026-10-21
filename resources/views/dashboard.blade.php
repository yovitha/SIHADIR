@extends('layouts.app')

@section('judul', 'Dashboard')
@section('remah', 'Ringkasan kegiatan dan kehadiran')

@if (auth()->user()->bisaMengelola())
    @section('aksi-atas')
        <a href="{{ route('kegiatan.create') }}" class="btn btn-utama btn-kecil"><x-icon name="plus" :size="16" /> Kegiatan Baru</a>
    @endsection
@endif

@section('konten')
<section class="sambutan muncul">
    <div>
        <h2>Halo, {{ explode(' ', trim(auth()->user()->nama_lengkap))[0] }}! 👋</h2>
        <p>
            @if ($kegiatanAktif->count())
                Ada <b>{{ $kegiatanAktif->count() }} kegiatan</b> dengan presensi yang sedang dibuka.
            @else
                Belum ada kegiatan yang presensinya sedang dibuka.
            @endif
        </p>
    </div>
    <div class="tombol-grup">
        @if (auth()->user()->bisaMengelola())
            <a href="{{ route('kegiatan.create') }}" class="btn btn-utama"><x-icon name="plus" :size="18" /> Buat Kegiatan</a>
        @endif
        <a href="{{ route('laporan.index') }}" class="btn btn-putih"><x-icon name="file-text" :size="18" /> Laporan</a>
    </div>
</section>

<section class="stat-grid">
    <a class="stat s1 muncul t1" href="{{ route('kegiatan.index') }}">
        <span class="ik"><x-icon name="calendar" :size="26" /></span>
        <div><b>{{ number_format($totalKegiatan, 0, ',', '.') }}</b><span>Total Kegiatan</span></div>
    </a>
    <a class="stat s2 muncul t2" href="{{ route('laporan.index') }}">
        <span class="ik"><x-icon name="user-check" :size="26" /></span>
        <div><b>{{ number_format($totalKehadiran, 0, ',', '.') }}</b><span>Total Kehadiran</span></div>
    </a>
    <a class="stat s3 muncul t3" href="{{ route('kegiatan.index', ['tanggal' => today()->toDateString()]) }}">
        <span class="ik"><x-icon name="clock" :size="26" /></span>
        <div><b>{{ $kegiatanHariIni }}</b><span>Kegiatan Hari Ini</span></div>
    </a>
    <a class="stat s4 muncul t4" href="{{ route('laporan.index') }}">
        <span class="ik"><x-icon name="bar-chart-2" :size="26" /></span>
        <div><b>{{ str_replace('.', ',', (string) $rataRata) }}</b><span>Rata-rata Hadir / Kegiatan</span></div>
    </a>
</section>

<section class="grid-dash">
    <div class="card muncul">
        <div class="card-head">
            <h3>Kehadiran per Bulan</h3>
            <span class="badge badge-kat">6 bulan terakhir</span>
        </div>
        <div class="grafik-kotak"><canvas id="grafik-kehadiran" aria-label="Grafik batang kehadiran per bulan" role="img"></canvas></div>
    </div>

    <div class="card muncul t2">
        <div class="card-head">
            <h3><span class="titik-hidup"></span>Presensi Sedang Dibuka</h3>
        </div>
        @if ($kegiatanAktif->isEmpty())
            <div class="kosong"><x-icon name="calendar" :size="40" /><p>Tidak ada kegiatan aktif saat ini.</p></div>
        @else
            <ul class="daftar-ringkas">
                @foreach ($kegiatanAktif as $k)
                    <li>
                        <div class="tgl"><small>{{ $k->tanggal->translatedFormat('M') }}</small><span>{{ $k->tanggal->format('d') }}</span></div>
                        <div class="info">
                            <b>{{ $k->nama_kegiatan }}</b>
                            <span>{{ $k->jam_mulai_hm }}–{{ $k->jam_selesai_hm }} · {{ $k->hadir_count }} hadir</span>
                        </div>
                        <div class="aksi">
                            <a class="btn btn-lembut btn-kecil btn-ikon" href="{{ route('kegiatan.qr', $k) }}" title="Tampilkan QR Code"><x-icon name="grid" :size="16" /></a>
                            <a class="btn btn-hijau btn-kecil btn-ikon" href="{{ route('kegiatan.pantau', $k) }}" title="Pantau kehadiran"><x-icon name="activity" :size="16" /></a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

<section class="card muncul">
    <div class="card-head">
        <h3>Kegiatan Terbaru</h3>
        <a href="{{ route('kegiatan.index') }}" class="btn btn-putih btn-kecil">Lihat semua <x-icon name="arrow-right" :size="14" /></a>
    </div>
    @if ($kegiatanTerbaru->isEmpty())
        <div class="kosong"><x-icon name="calendar" :size="40" /><p>Belum ada kegiatan.</p></div>
    @else
        <div class="tabel-gulir">
            <table class="tbl">
                <thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Kategori</th><th>Hadir</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($kegiatanTerbaru as $k)
                    <tr>
                        <td><a class="nama" href="{{ route('kegiatan.show', $k) }}">{{ $k->nama_kegiatan }}</a><div class="sub">{{ $k->lokasi }}</div></td>
                        <td>{{ $k->tanggal->translatedFormat('d M Y') }}<div class="sub">{{ $k->jam_mulai_hm }}–{{ $k->jam_selesai_hm }}</div></td>
                        <td><span class="badge badge-kat">{{ $k->kategori->nama_kategori }}</span></td>
                        <td><b>{{ $k->hadir_count }}</b></td>
                        <td><span class="badge {{ $k->status_class }}">{{ $k->status_label }}</span></td>
                        <td>
                            <div class="aksi">
                                <a class="btn btn-lembut btn-kecil" href="{{ route('kegiatan.show', $k) }}"><x-icon name="eye" :size="15" /> Detail</a>
                                <a class="btn btn-putih btn-kecil" href="{{ route('laporan.kegiatan', $k) }}" target="_blank"><x-icon name="printer" :size="15" /> Laporan</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection

@push('vendor')
<script src="{{ asset('vendor/chart.min.js') }}"></script>
@endpush

@push('skrip')
<script>
(function () {
    var data = @json($grafik);
    var el = document.getElementById('grafik-kehadiran');
    if (!el || !window.Chart) return;
    Chart.defaults.font.family = '"Plus Jakarta Sans", system-ui, sans-serif';
    Chart.defaults.color = '#4B6362';
    new Chart(el, {
        type: 'bar',
        data: {
            labels: data.label,
            datasets: [
                { label: 'Kehadiran', data: data.hadir, backgroundColor: '#E8741E', borderRadius: 10, borderSkipped: false, maxBarThickness: 38 },
                { label: 'Kegiatan', data: data.kegiatan, backgroundColor: '#0F4C4A', borderRadius: 10, borderSkipped: false, maxBarThickness: 38 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 18 } } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#F0E9DC' }, border: { display: false } }
            }
        }
    });
})();
</script>
@endpush
