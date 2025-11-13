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
}