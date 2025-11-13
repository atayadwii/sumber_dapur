<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;
    protected $table = 'produk';

    protected $fillable = [
        'user_id',
        'kategori_id',
        'nama_produk',
        'deskripsi_produk',
        'harga',
        'stok',
        'satuan',
    ];

    /**
     * Relasi: Produk dimiliki oleh 1 User (Penjual)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: Produk dimiliki oleh 1 Kategori
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_id');
    }
}