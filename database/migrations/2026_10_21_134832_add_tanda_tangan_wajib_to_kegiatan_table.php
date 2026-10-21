<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menambahkan kolom.
     */
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Memeriksa apakah kolom sudah ada sebelumnya untuk menghindari error duplikat
            if (!Schema::hasColumn('kegiatan', 'tanda_tangan_wajib')) {
                $table->boolean('tanda_tangan_wajib')->default(true)->after('penyelenggara');
            }
        });
    }

    /**
     * Batalkan migrasi (rollback).
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            if (Schema::hasColumn('kegiatan', 'tanda_tangan_wajib')) {
                $table->dropColumn('tanda_tangan_wajib');
            }
        });
    }
};
