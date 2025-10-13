<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PesananTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pesanan')->insert([
            [
                'user_pembeli_id' => 3, // Pembeli 1
                'user_penjual_id' => 2, // Penjual 1
                'tgl_pesanan' => Carbon::now(),
                'status_pesanan' => 'pending',
                'total_harga' => 20000,
            ],
            [
                'user_pembeli_id' => 3,
                'user_penjual_id' => 2,
                'tgl_pesanan' => Carbon::now(),
                'status_pesanan' => 'proses',
                'total_harga' => 5000,
            ],
            [
                'user_pembeli_id' => 3,
                'user_penjual_id' => 2,
                'tgl_pesanan' => Carbon::now(),
                'status_pesanan' => 'selesai',
                'total_harga' => 12000,
            ],
        ]);
    }
}