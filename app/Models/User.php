<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'nama_lengkap',
        'username',
        'password',
        'role',
        'status_aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    /** Admin dan Operator boleh mengubah data; Pimpinan hanya membaca. */
    public function bisaMengelola(): bool
    {
        return in_array($this->role, ['admin', 'operator'], true);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'operator' => 'Operator',
            'pimpinan' => 'Pimpinan',
            default => ucfirst((string) $this->role),
        };
    }

    public function getInisialAttribute(): string
    {
        $kata = preg_split('/\s+/', trim($this->nama_lengkap)) ?: [];
        $huruf = '';
        foreach (array_slice($kata, 0, 2) as $k) {
            $huruf .= mb_strtoupper(mb_substr($k, 0, 1));
        }

        return $huruf ?: 'U';
    }
}
