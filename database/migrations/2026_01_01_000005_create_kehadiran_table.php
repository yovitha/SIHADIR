<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->increments('id_kehadiran');
            $table->unsignedInteger('id_kegiatan');
            $table->unsignedInteger('id_peserta')->nullable();
            $table->string('nama_manual', 100)->nullable();
            $table->string('instansi_manual', 150)->nullable();
            $table->string('jabatan_manual', 100)->nullable();
            $table->string('no_hp_manual', 20)->nullable();
            $table->dateTime('waktu_hadir');
            $table->string('tanda_tangan', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->enum('status', ['tercatat', 'dibatalkan'])->default('tercatat');
            $table->string('keterangan', 100)->nullable();

            $table->unique(['id_kegiatan', 'id_peserta']);
            $table->index(['id_kegiatan', 'waktu_hadir']);
            $table->foreign('id_kegiatan')->references('id_kegiatan')->on('kegiatan')->restrictOnDelete();
            $table->foreign('id_peserta')->references('id_peserta')->on('peserta')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
