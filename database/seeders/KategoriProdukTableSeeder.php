<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriProdukTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kategori_produk')->insert([
            ['nama_kategori' => 'Makanan'],
            ['nama_kategori' => 'Minuman'],
            ['nama_kategori' => 'Bumbu Dapur'],
        ]);
    }
}