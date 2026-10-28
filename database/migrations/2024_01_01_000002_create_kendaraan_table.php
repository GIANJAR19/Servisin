<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->id('id_kendaraan');
            $table->foreignId('pelanggan_id')->constrained('pelanggan', 'id_pelanggan')->onDelete('cascade');
            $table->string('nomor_polisi', 12)->unique();
            $table->string('merek', 45);
            $table->string('tipe', 45);
            $table->integer('tahun');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};
