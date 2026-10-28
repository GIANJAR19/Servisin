<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id('id_layanan');
            $table->string('nama_layanan', 100);
            $table->integer('durasi_menit');
            $table->decimal('harga', 10, 2);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};
