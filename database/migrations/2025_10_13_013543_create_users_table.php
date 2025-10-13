<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 45);
            $table->string('email', 45)->unique();
            $table->string('password');
            $table->enum('tipe_user', ['admin', 'penjual', 'pembeli']);
            $table->string('no_hp', 20)->nullable();
            $table->timestamp('tgl_daftar')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};