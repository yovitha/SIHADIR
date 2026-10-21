<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_kegiatan', function (Blueprint $table) {
            $table->increments('id_kategori');
            $table->string('nama_kategori', 50)->unique();
        });

        Schema::create('instansi', function (Blueprint $table) {
            $table->increments('id_instansi');
            $table->string('nama_instansi', 150);
            $table->string('alamat', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instansi');
        Schema::dropIfExists('kategori_kegiatan');
    }
};
