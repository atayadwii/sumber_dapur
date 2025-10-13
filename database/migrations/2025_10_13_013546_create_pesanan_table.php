<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_pembeli_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_penjual_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('tgl_pesanan')->useCurrent();
            $table->enum('status_pesanan', ['pending', 'proses', 'selesai', 'batal'])->default('pending');
            $table->decimal('total_harga', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};