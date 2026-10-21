<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    protected $table = 'peserta';

    protected $primaryKey = 'id_peserta';

    protected $fillable = [
        'id_instansi',
        'nip_nik',
        'nama',
        'jabatan',
        'no_hp',
        'email',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'id_instansi', 'id_instansi');
    }

    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'id_peserta', 'id_peserta');
    }
}
