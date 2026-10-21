@extends('layouts.app')

@section('judul', 'Log Aktivitas')
@section('remah', 'Riwayat aktivitas penting pengguna sistem')

@section('konten')
<div class="card muncul">
    <form method="GET" action="{{ route('log.index') }}" class="filter">
        <div class="field lebar">
            <label for="q">Cari aktivitas</label>
            <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Contoh: kegiatan, masuk, peserta...">
        </div>
        <div class="field">
            <label for="user">Pengguna</label>
            <select id="user" name="user">
                <option value="">Semua</option>
                @foreach ($pengguna as $u)
                    <option value="{{ $u->id_user }}" @selected(request('user') == $u->id_user)>{{ $u->nama_lengkap }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}">
        </div>
        <div class="tombol-grup">
            <button type="submit" class="btn btn-hijau"><x-icon name="search" :size="16" /> Cari</button>
            @if (request()->hasAny(['q', 'user', 'tanggal']))
                <a href="{{ route('log.index') }}" class="btn btn-putih">Atur ulang</a>
            @endif
        </div>
    </form>

    @if ($log->isEmpty())
        <div class="kosong"><x-icon name="clock" :size="44" /><p>Belum ada aktivitas yang tercatat.</p></div>
    @else
        <div class="tabel-gulir">
            <table class="tbl">
                <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th></tr></thead>
                <tbody>
                @foreach ($log as $l)
                    <tr>
                        <td style="white-space:nowrap">{{ $l->waktu->format('d/m/Y') }}<div class="sub">{{ $l->waktu->format('H:i:s') }}</div></td>
                        <td>
                            <span class="nama">{{ $l->user->nama_lengkap ?? '—' }}</span>
                            @if ($l->user)<div class="sub">{{ $l->user->role_label }}</div>@endif
                        </td>
                        <td>{{ $l->aktivitas }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $log->links('pagination.sihadir') }}
    @endif
</div>
@endsection
