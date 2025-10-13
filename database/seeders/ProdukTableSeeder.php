<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdukTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('produk')->insert([
            [
                'user_id' => 2, // Penjual 1
                'kategori_id' => 1,
                'nama_produk' => 'Nasi Goreng',
                'deskripsi_produk' => 'Nasi goreng khas rumahan',
                'harga' => 15000,
                'stok' => 50,
                'satuan' => 'Porsi',
            ],
            [
                'user_id' => 2,
                'kategori_id' => 2,
                'nama_produk' => 'Es Teh Manis',
                'deskripsi_produk' => 'Segar dan manis',
                'harga' => 5000,
                'stok' => 100,
                'satuan' => 'Gelas',
            ],
            [
                'user_id' => 2,
                'kategori_id' => 3,
                'nama_produk' => 'Kecap Manis',
                'deskripsi_produk' => 'Botol 300ml',
                'harga' => 12000,
                'stok' => 30,
                'satuan' => 'Botol',
            ],
        ]);
    }
}