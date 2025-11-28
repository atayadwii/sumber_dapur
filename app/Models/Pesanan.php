<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;
    protected $table = 'pesanan';

    protected $fillable = [
        'user_pembeli_id',
        'user_penjual_id',
        'tgl_pesanan',
        'status_pesanan',
        'total_harga',
        'bukti_penerimaan',
        'alamat_pengiriman',
        'catatan',
        'bukti_pembayaran',
        'payment_deadline',
        'is_paid',
        'rating',
        'review',
        'review_images',
        'paid_at',
        'completed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'review_images' => 'array',
        'is_paid' => 'boolean',
        'rating' => 'decimal:1',
        'payment_deadline' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = [
        'bukti_pembayaran_url',
        'bukti_penerimaan_url',
        'review_images_urls'
    ];

    /**
     * Relasi: Pesanan dimiliki oleh 1 User (Pembeli)
     */
    public function pembeli()
    {
        return $this->belongsTo(User::class, 'user_pembeli_id');
    }

    /**
     * Relasi: Pesanan dimiliki oleh 1 User (Penjual)
     */
    public function penjual()
    {
        return $this->belongsTo(User::class, 'user_penjual_id');
    }

    /**
     * Relasi: Pesanan memiliki banyak DetailPesanan
     */
    public function detail()
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }

    // Accessors

    public function getBuktiPembayaranUrlAttribute()
    {
        if (!$this->bukti_pembayaran) {
            return null;
        }

        return asset('storage/' . $this->bukti_pembayaran);
    }

    public function getBuktiPenerimaanUrlAttribute()
    {
        if (!$this->bukti_penerimaan) {
            return null;
        }

        return asset('storage/' . $this->bukti_penerimaan);
    }

    public function getReviewImagesUrlsAttribute()
    {
        if (!$this->review_images) {
            return [];
        }

        return array_map(function($path) {
            return asset('storage/' . $path);
        }, $this->review_images);
    }
}