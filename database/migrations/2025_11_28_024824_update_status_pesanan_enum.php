<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE pesanan MODIFY COLUMN status_pesanan ENUM('pending', 'menunggu_pembayaran', 'menunggu_konfirmasi', 'proses', 'selesai', 'batal', 'kadaluarsa') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pesanan MODIFY COLUMN status_pesanan ENUM('pending', 'proses', 'selesai', 'batal') DEFAULT 'pending'");
    }
};
