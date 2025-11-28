<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('bukti_penerimaan')->nullable()->after('status_pesanan');
            $table->text('alamat_pengiriman')->nullable()->after('bukti_penerimaan');
            $table->text('catatan')->nullable()->after('alamat_pengiriman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['bukti_penerimaan', 'alamat_pengiriman', 'catatan']);
        });
    }
};
