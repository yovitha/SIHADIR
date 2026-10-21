@if (session('sukses'))
    <div class="alert alert-ok" role="status"><x-icon name="check-circle" :size="20" /><div>{{ session('sukses') }}</div></div>
@endif
@if (session('galat'))
    <div class="alert alert-galat" role="alert"><x-icon name="alert-triangle" :size="20" /><div>{{ session('galat') }}</div></div>
@endif
@if (session('sandi_baru'))
    <div class="alert alert-peringatan" role="status">
        <x-icon name="key" :size="20" />
        <div>
            Kata sandi sementara untuk <b>{{ session('sandi_baru')['username'] }}</b>:
            <code style="font-size:16px;font-weight:800;letter-spacing:.06em">{{ session('sandi_baru')['password'] }}</code><br>
            <span class="teks-kecil">Catat dan sampaikan kepada pemilik akun. Kata sandi ini hanya tampil sekali.</span>
        </div>
    </div>
@endif
@if (session('galat_impor') && count(session('galat_impor')))
    <div class="alert alert-peringatan" role="alert">
        <x-icon name="info" :size="20" />
        <div>
            Baris yang dilewati:
            <ul>
                @foreach (array_slice(session('galat_impor'), 0, 30) as $g)
                    <li>{{ $g }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
