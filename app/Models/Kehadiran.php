<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    protected $table = 'kehadiran';

    protected $primaryKey = 'id_kehadiran';

    public $timestamps = false;

    protected $fillable = [
        'id_kegiatan',
        'id_peserta',
        'nama_manual',
        'instansi_manual',
        'jabatan_manual',
        'no_hp_manual',
        'waktu_hadir',
        'tanda_tangan',
        'ip_address',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return ['waktu_hadir' => 'datetime'];
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id_peserta');
    }

    public function getNamaTampilAttribute(): string
    {
        return $this->peserta?->nama ?? (string) $this->nama_manual;
    }

    public function getInstansiTampilAttribute(): string
    {
        return $this->peserta?->instansi?->nama_instansi ?? (string) $this->instansi_manual;
    }

    public function getJabatanTampilAttribute(): string
    {
        return $this->peserta?->jabatan ?? (string) $this->jabatan_manual;
    }

    public function getTandaTanganUrlAttribute(): ?string
    {
        return $this->tanda_tangan ? asset('storage/'.$this->tanda_tangan) : null;
    }

    public function getTandaTanganPathAttribute(): ?string
    {
        return $this->tanda_tangan ? storage_path('app/public/'.$this->tanda_tangan) : null;
    }

    /** Untuk laporan PDF: gambar tanda tangan disisipkan langsung sebagai data URI. */
    public function tandaTanganDataUri(): ?string
    {
        $path = $this->tanda_tangan_path;

        if (! $path || ! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }
}
