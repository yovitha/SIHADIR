<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->increments('id_kegiatan');
            $table->unsignedInteger('id_user');
            $table->unsignedInteger('id_kategori');
            $table->string('nama_kegiatan', 150);
            $table->text('deskripsi')->nullable();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi', 150);
            $table->string('penyelenggara', 100)->nullable();
            $table->string('kode_unik', 32)->unique();
            $table->enum('status', ['draft', 'aktif', 'selesai', 'dibatalkan'])->default('draft');
            $table->boolean('tanda_tangan_wajib')->default(true);
            $table->timestamps();

            $table->index(['tanggal', 'status']);
            $table->foreign('id_user')->references('id_user')->on('users')->restrictOnDelete();
            $table->foreign('id_kategori')->references('id_kategori')->on('kategori_kegiatan')->restrictOnDelete();
        });

        // Lapisan pengaman kedua: jam selesai harus setelah jam mulai (MySQL 8.0.16+ / MariaDB 10.2+).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            try {
                DB::statement('ALTER TABLE kegiatan ADD CONSTRAINT chk_kegiatan_jam CHECK (jam_selesai > jam_mulai)');
            } catch (\Throwable $e) {
                // Versi MySQL lama tidak mendukung CHECK; validasi aplikasi tetap berlaku.
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
