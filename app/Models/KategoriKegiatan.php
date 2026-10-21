<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKegiatan extends Model
{
    protected $table = 'kategori_kegiatan';

    protected $primaryKey = 'id_kategori';

    public $timestamps = false;

    protected $fillable = ['nama_kategori'];

    public function kegiatan()
    {
        return $this->hasMany(Kegiatan::class, 'id_kategori', 'id_kategori');
    }
}
