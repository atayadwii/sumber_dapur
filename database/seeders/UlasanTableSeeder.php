<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UlasanTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ulasan')->insert([
            [
                'pesanan_id' => 1,
                'user_id' => 3,
                'rating' => 5,
                'komentar' => 'Makanannya enak!',
                'tgl_ulasan' => Carbon::now(),
            ],
            [
                'pesanan_id' => 2,
                'user_id' => 3,
                'rating' => 4,
                'komentar' => 'Minumannya segar!',
                'tgl_ulasan' => Carbon::now(),
            ],
            [
                'pesanan_id' => 3,
                'user_id' => 3,
                'rating' => 5,
                'komentar' => 'Pelayanan cepat dan ramah.',
                'tgl_ulasan' => Carbon::now(),
            ],
        ]);
    }
}