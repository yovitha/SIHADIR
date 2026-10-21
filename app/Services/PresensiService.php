<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\Kehadiran;

class PresensiService
{
    /**
     * Mencari presensi ganda (AB-04).
     * Peserta terdaftar dicek lewat id_peserta; tamu dicek lewat kombinasi nama + instansi.
     */
    public function cariGanda(Kegiatan $kegiatan, ?int $idPeserta, ?string $nama = null, ?string $instansi = null): ?Kehadiran
    {
        $query = Kehadiran::query()
            ->where('id_kegiatan', $kegiatan->id_kegiatan)
            ->where('status', 'tercatat');

        if ($idPeserta) {
            return $query->where('id_peserta', $idPeserta)->first();
        }

        return $query
            ->whereNull('id_peserta')
            ->whereRaw('LOWER(nama_manual) = ?', [mb_strtolower(trim((string) $nama))])
            ->whereRaw('LOWER(instansi_manual) = ?', [mb_strtolower(trim((string) $instansi))])
            ->first();
    }

    /**
     * Menyimpan kehadiran. Waktu hadir selalu memakai jam server (AB-06).
     *
     * @param  array{id_peserta?:int|null,nama?:string|null,instansi?:string|null,jabatan?:string|null,no_hp?:string|null,tanda_tangan?:string|null,ip?:string|null,keterangan?:string|null}  $data
     */
    public function catat(Kegiatan $kegiatan, array $data): Kehadiran
    {
        $idPeserta = $data['id_peserta'] ?? null;

        $isi = [
            'waktu_hadir' => now(),
            'tanda_tangan' => $data['tanda_tangan'] ?? null,
            'ip_address' => $data['ip'] ?? null,
            'status' => 'tercatat',
            'keterangan' => $data['keterangan'] ?? null,
        ];

        if ($idPeserta) {
            // Kehadiran yang pernah dibatalkan dipulihkan, karena kombinasi kegiatan + peserta bersifat unik.
            $lama = Kehadiran::query()
                ->where('id_kegiatan', $kegiatan->id_kegiatan)
                ->where('id_peserta', $idPeserta)
                ->first();

            if ($lama) {
                $lama->update($isi);

                return $lama;
            }

            return Kehadiran::create($isi + [
                'id_kegiatan' => $kegiatan->id_kegiatan,
                'id_peserta' => $idPeserta,
            ]);
        }

        return Kehadiran::create($isi + [
            'id_kegiatan' => $kegiatan->id_kegiatan,
            'nama_manual' => trim((string) ($data['nama'] ?? '')),
            'instansi_manual' => trim((string) ($data['instansi'] ?? '')),
            'jabatan_manual' => trim((string) ($data['jabatan'] ?? '')),
            'no_hp_manual' => $data['no_hp'] ?? null,
        ]);
    }
}
