<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->id('id_booking');
            $table->string('kode_booking', 30)->unique();
            $table->foreignId('kendaraan_id')->constrained('kendaraan', 'id_kendaraan')->onDelete('restrict');
            $table->foreignId('layanan_id')->constrained('layanan', 'id_layanan')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users', 'id_user')->onDelete('set null');
            $table->date('tanggal');
            $table->time('jam');
            $table->text('keluhan')->nullable();
            $table->enum('status', ['Menunggu', 'Dikerjakan', 'Selesai', 'Dibatalkan'])->default('Menunggu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};
