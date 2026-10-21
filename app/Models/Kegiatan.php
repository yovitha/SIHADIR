<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';

    protected $primaryKey = 'id_kegiatan';

    protected $fillable = [
        'id_user',
        'id_kategori',
        'nama_kegiatan',
        'deskripsi',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'penyelenggara',
        'kode_unik',
        'status',
        'tanda_tangan_wajib',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tanda_tangan_wajib' => 'boolean',
        ];
    }

    public const STATUS = ['draft', 'aktif', 'selesai', 'dibatalkan'];

    /* ------------------------------ Relasi ------------------------------ */

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriKegiatan::class, 'id_kategori', 'id_kategori');
    }

    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'id_kegiatan', 'id_kegiatan');
    }

    /* ------------------------------ Scope ------------------------------- */

    /** Operator hanya melihat kegiatannya sendiri; Admin dan Pimpinan melihat semua. */
    public function scopeDapatDilihat(Builder $query, User $user): Builder
    {
        if ($user->isOperator()) {
            $query->where('id_user', $user->id_user);
        }

        return $query;
    }

    public function scopeDenganJumlahHadir(Builder $query): Builder
    {
        return $query->withCount([
            'kehadiran as hadir_count' => fn ($q) => $q->where('status', 'tercatat'),
        ]);
    }

    /* ----------------------------- Hak akses ---------------------------- */

    public function bisaDikelolaOleh(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isOperator() && (int) $this->id_user === (int) $user->id_user;
    }

    public function bisaDilihatOleh(User $user): bool
    {
        return $user->isOperator() ? $this->bisaDikelolaOleh($user) : true;
    }

    /** Kode acak 10 karakter tanpa huruf/angka yang mudah tertukar (AB-01). */
    public static function buatKodeUnik(): string
    {
        $abjad = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $panjang = (int) config('sihadir.panjang_kode', 10);

        do {
            $kode = '';
            for ($i = 0; $i < $panjang; $i++) {
                $kode .= $abjad[random_int(0, strlen($abjad) - 1)];
            }
        } while (static::where('kode_unik', $kode)->exists());

        return $kode;
    }

    /* ------------------------------ Waktu ------------------------------- */

    public function mulaiPada(): Carbon
    {
        return Carbon::parse($this->tanggal->toDateString().' '.$this->jam_mulai);
    }

    public function selesaiPada(): Carbon
    {
        return Carbon::parse($this->tanggal->toDateString().' '.$this->jam_selesai);
    }

    /** Presensi diterima mulai (jam mulai - toleransi) sampai (jam selesai + toleransi). AB-03 */
    public function dalamRentangWaktu(?Carbon $sekarang = null): bool
    {
        $sekarang ??= now();
        $toleransi = (int) config('sihadir.toleransi_menit', 15);

        return $sekarang->betweenIncluded(
            $this->mulaiPada()->subMinutes($toleransi),
            $this->selesaiPada()->addMinutes($toleransi),
        );
    }

    /** Kegiatan berstatus Aktif yang sudah lewat waktunya otomatis menjadi Selesai. */
    public static function sinkronStatus(): void
    {
        $toleransi = (int) config('sihadir.toleransi_menit', 15);
        $batas = now()->subMinutes($toleransi);

        static::query()->where('status', 'aktif')->get()->each(function (Kegiatan $k) use ($batas) {
            if ($k->selesaiPada()->lt($batas)) {
                $k->update(['status' => 'selesai']);
            }
        });
    }

    /* ----------------------------- Tampilan ----------------------------- */

    public function getUrlPresensiAttribute(): string
    {
        return route('presensi.form', $this->kode_unik);
    }

    public function getJamMulaiHmAttribute(): string
    {
        return substr((string) $this->jam_mulai, 0, 5);
    }

    public function getJamSelesaiHmAttribute(): string
    {
        return substr((string) $this->jam_selesai, 0, 5);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusClassAttribute(): string
    {
        return 'badge-'.$this->status;
    }
}
