@php
    $berkasLogo = public_path('images/logo-tomohon.png');
    $logoSrc = ! empty($pdf) && is_file($berkasLogo)
        ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($berkasLogo))
        : asset('images/logo-tomohon.png');
@endphp
{{-- Kop laporan. Format kop resmi menunggu konfirmasi Diskominfo [K-07]. --}}
<table class="kop">
    <tr>
        <td class="logo"><img src="{{ $logoSrc }}" alt="Lambang Kota Tomohon"></td>
        <td class="teks">
            <div class="pem">{{ config('sihadir.pemerintah') }}</div>
            <div class="dinas">{{ config('sihadir.instansi') }}</div>
            <div class="alamat">{{ config('sihadir.kontak.alamat') }}</div>
        </td>
        <td class="logo">&nbsp;</td>
    </tr>
</table>
