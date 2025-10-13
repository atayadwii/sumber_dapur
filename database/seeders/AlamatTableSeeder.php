<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlamatTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('alamat')->insert([
            [
                'user_id' => 3,
                'alamat_lengkap' => 'Jl. Merdeka No.1',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
                'kode_pos' => '10110',
                'latitude' => -6.1751,
                'longitude' => 106.8650,
            ],
            [
                'user_id' => 2,
                'alamat_lengkap' => 'Jl. Sudirman No.10',
                'kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '40115',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
            ],
            [
                'user_id' => 1,
                'alamat_lengkap' => 'Jl. Admin Raya',
                'kota' => 'Surabaya',
                'provinsi' => 'Jawa Timur',
                'kode_pos' => '60234',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
            ],
        ]);
    }
}