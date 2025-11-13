<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- IMPORT SANCTUM

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // <-- TAMBAHKAN HasApiTokens

    /**
     * Nama tabel
     */
    protected $table = 'users';

    /**
     * Atribut yang bisa diisi
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'tipe_user',
        'no_hp',
        'tgl_daftar', // tgl_daftar diisi otomatis oleh DB (useCurrent)
    ];

    /**
     * Atribut yang disembunyikan saat di-serialisasi (dijadikan JSON)
     */
    protected $hidden = [
        'password',
        // 'remember_token', // Tidak ada di migrasi Anda
    ];

    /**
     * Atribut yang di-cast
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'tgl_daftar' => 'datetime',
    ];

    /**
     * Relasi: User (Penjual) memiliki banyak Produk
     */
    public function produk()
    {
        return $this->hasMany(Produk::class, 'user_id');
    }

    /**
     * Relasi: User (Pembeli) memiliki banyak Pesanan
     */
    public function pesananPembeli()
    {
        return $this->hasMany(Pesanan::class, 'user_pembeli_id');
    }

    /**
     * Relasi: User (Penjual) memiliki banyak Pesanan
     */
    public function pesananPenjual()
    {
        return $this->hasMany(Pesanan::class, 'user_penjual_id');
    }

    /**
     * Relasi: User memiliki banyak Alamat
     */
    public function alamat()
    {
        return $this->hasMany(Alamat::class, 'user_id');
    }
}