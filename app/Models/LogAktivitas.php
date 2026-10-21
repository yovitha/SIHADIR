<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $primaryKey = 'id_log';

    public $timestamps = false;

    protected $fillable = ['id_user', 'aktivitas', 'waktu'];

    protected function casts(): array
    {
        return ['waktu' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /** Mencatat aktivitas penting pengguna (KF-14). */
    public static function catat(string $aktivitas, ?int $idUser = null): void
    {
        static::create([
            'id_user' => $idUser ?? Auth::id(),
            'aktivitas' => Str::limit($aktivitas, 195, ''),
            'waktu' => now(),
        ]);
    }
}
