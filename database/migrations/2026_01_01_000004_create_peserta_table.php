<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta', function (Blueprint $table) {
            $table->increments('id_peserta');
            $table->unsignedInteger('id_instansi');
            $table->string('nip_nik', 25)->nullable()->unique();
            $table->string('nama', 100);
            $table->string('jabatan', 100);
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamps();

            $table->index('nama');
            $table->foreign('id_instansi')->references('id_instansi')->on('instansi')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta');
    }
};
