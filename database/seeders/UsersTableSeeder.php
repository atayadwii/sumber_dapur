<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::insert([
            [
                'nama' => 'Admin 1',
                'email' => 'admin1@example.com',
                'password' => Hash::make('password'),
                'tipe_user' => 'admin',
                'no_hp' => '081234567890',
            ],
            [
                'nama' => 'Penjual 1',
                'email' => 'penjual1@example.com',
                'password' => Hash::make('password'),
                'tipe_user' => 'penjual',
                'no_hp' => '081234567891',
            ],
            [
                'nama' => 'Pembeli 1',
                'email' => 'pembeli1@example.com',
                'password' => Hash::make('password'),
                'tipe_user' => 'pembeli',
                'no_hp' => '081234567892',
            ],
        ]);
    }
}