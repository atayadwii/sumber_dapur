<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPesananTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('detail_pesanan')->insert([
            [
                'pesanan_id' => 1,
                'produk_id' => 1,
                'jumlah' => 1,
                'subtotal' => 15000,
            ],
            [
                'pesanan_id' => 2,
                'produk_id' => 2,
                'jumlah' => 1,
                'subtotal' => 5000,
            ],
            [
                'pesanan_id' => 3,
                'produk_id' => 3,
                'jumlah' => 1,
                'subtotal' => 12000,
            ],
        ]);
    }
}