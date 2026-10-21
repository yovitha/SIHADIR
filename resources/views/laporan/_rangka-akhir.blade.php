{{-- Penutup laporan: blok pengesahan dan catatan cetak. Variabel: $dicetakOleh --}}
<table class="pengesahan">
    <tr>
        <td></td>
        <td>
            <div class="blok">
                Tomohon, {{ now()->translatedFormat('d F Y') }}<br>
                Penanggung Jawab
                <div class="ruang"></div>
                <b>(............................................)</b>
            </div>
        </td>
    </tr>
</table>
<div class="catatan">Dicetak oleh {{ $dicetakOleh }} melalui SIHADIR pada {{ now()->translatedFormat('d F Y, H:i') }} WITA.</div>
</div>
</div>
</body>
</html>
