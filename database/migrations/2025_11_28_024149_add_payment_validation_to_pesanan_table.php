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
            // Payment validation fields
            $table->string('bukti_pembayaran')->nullable()->after('bukti_penerimaan');
            $table->timestamp('payment_deadline')->nullable()->after('bukti_pembayaran');
            $table->boolean('is_paid')->default(false)->after('payment_deadline');
            
            // Rating & Review fields
            $table->decimal('rating', 2, 1)->nullable()->after('is_paid');
            $table->text('review')->nullable()->after('rating');
            $table->json('review_images')->nullable()->after('review');
            
            // Timestamps
            $table->timestamp('paid_at')->nullable()->after('review_images');
            $table->timestamp('completed_at')->nullable()->after('paid_at');
            $table->text('rejection_reason')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_pembayaran',
                'payment_deadline',
                'is_paid',
                'rating',
                'review',
                'review_images',
                'paid_at',
                'completed_at',
                'rejection_reason'
            ]);
        });
    }
};
