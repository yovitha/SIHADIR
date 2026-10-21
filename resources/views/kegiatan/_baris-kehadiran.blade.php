@forelse ($kehadiran as $i => $h)
    <tr @if ($h->status === 'dibatalkan') style="opacity:.55" @endif>
        <td>{{ $kehadiran->count() - $i }}</td>
        <td>
            <span class="nama">{{ $h->nama_tampil }}</span>
            <div class="sub">{{ $h->jabatan_tampil }} · {{ $h->instansi_tampil }}</div>
        </td>
        <td>{{ $h->waktu_hadir->format('H:i:s') }}<div class="sub">{{ $h->waktu_hadir->translatedFormat('d M Y') }}</div></td>
        <td>
            @if ($h->tanda_tangan_url)
                <img class="ttd-mini" src="{{ $h->tanda_tangan_url }}" alt="Tanda tangan {{ $h->nama_tampil }}" loading="lazy">
            @else
                <span class="teks-mute">—</span>
            @endif
        </td>
        <td>
            <span class="badge badge-{{ $h->status }}">{{ ucfirst($h->status) }}</span>
            @if ($h->keterangan)<div class="sub">{{ $h->keterangan }}</div>@endif
        </td>
        @if ($bisaKelola)
            <td>
                @if ($h->status === 'tercatat')
                    <form method="POST" action="{{ route('kehadiran.batal', $h) }}" data-konfirmasi="Batalkan kehadiran {{ $h->nama_tampil }}?" class="aksi">
                        @csrf
                        <button type="submit" class="btn btn-bahaya btn-kecil"><x-icon name="x" :size="14" /> Batalkan</button>
                    </form>
                @endif
            </td>
        @endif
    </tr>
@empty
    <tr><td colspan="{{ $bisaKelola ? 6 : 5 }}"><div class="kosong"><x-icon name="users" :size="40" /><p>Belum ada peserta yang mengisi daftar hadir.</p></div></td></tr>
@endforelse
