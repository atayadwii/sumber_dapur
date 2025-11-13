<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    use HasFactory;
    protected $table = 'detail_pesanan';

    protected $fillable = [
        'pesanan_id',
        'produk_id',
        'jumlah',
        'subtotal',
    ];

    /**
     * Relasi: Detail ini memiliki 1 Produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}