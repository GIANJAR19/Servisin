<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->string('judul', 150);
            $table->text('pesan');
            $table->enum('tipe', ['booking_baru', 'status_antrean', 'kuota', 'info'])->default('info');
            $table->foreignId('booking_id')->nullable()->constrained('booking', 'id_booking')->onDelete('cascade');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
