@extends('layouts.app')

@section('judul', 'Pengguna')
@section('remah', 'Kelola akun Admin, Operator, dan Pimpinan')

@section('aksi-atas')
    <a href="{{ route('pengguna.create') }}" class="btn btn-utama btn-kecil"><x-icon name="user-plus" :size="16" /> Akun Baru</a>
@endsection

@section('konten')
<div class="card muncul">
    <form method="GET" action="{{ route('pengguna.index') }}" class="filter">
        <div class="field lebar">
            <label for="q">Cari pengguna</label>
            <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Nama atau nama pengguna...">
        </div>
        <div class="tombol-grup">
            <button type="submit" class="btn btn-hijau"><x-icon name="search" :size="16" /> Cari</button>
            @if (request()->filled('q'))
                <a href="{{ route('pengguna.index') }}" class="btn btn-putih">Atur ulang</a>
            @endif
        </div>
    </form>

    @if ($pengguna->isEmpty())
        <div class="kosong"><x-icon name="users" :size="44" /><p>Tidak ada pengguna yang cocok.</p></div>
    @else
        <div class="tabel-gulir">
            <table class="tbl">
                <thead><tr><th>Pengguna</th><th>Peran</th><th>Status</th><th>Dibuat</th><th></th></tr></thead>
                <tbody>
                @foreach ($pengguna as $u)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:12px">
                                <span class="avatar">{{ $u->inisial }}</span>
                                <div><span class="nama">{{ $u->nama_lengkap }}</span><div class="sub">{{ $u->username }}</div></div>
                            </div>
                        </td>
                        <td><span class="badge badge-{{ $u->role }}">{{ $u->role_label }}</span></td>
                        <td><span class="badge {{ $u->status_aktif ? 'badge-aktif' : 'badge-nonaktif' }}">{{ $u->status_aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td>{{ optional($u->created_at)->translatedFormat('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="aksi">
                                <a class="btn btn-lembut btn-kecil" href="{{ route('pengguna.edit', $u) }}"><x-icon name="edit-2" :size="14" /> Ubah</a>
                                @if ($u->id_user !== auth()->id())
                                    <form method="POST" action="{{ route('pengguna.reset', $u) }}" data-konfirmasi="Atur ulang kata sandi {{ $u->username }}? Kata sandi sementara akan dibuat.">
                                        @csrf
                                        <button type="submit" class="btn btn-putih btn-kecil"><x-icon name="key" :size="14" /> Reset sandi</button>
                                    </form>
                                    <form method="POST" action="{{ route('pengguna.status', $u) }}" data-konfirmasi="{{ $u->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $u->username }}?">
                                        @csrf
                                        <button type="submit" class="btn {{ $u->status_aktif ? 'btn-bahaya' : 'btn-hijau' }} btn-kecil">
                                            <x-icon :name="$u->status_aktif ? 'slash' : 'check'" :size="14" /> {{ $u->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $pengguna->links('pagination.sihadir') }}
    @endif
</div>
@endsection
