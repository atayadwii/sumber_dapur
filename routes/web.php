<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\DetailPesananController;
use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/pesanan', [PesananController::class, 'index']);
Route::get('/detail-pesanan', [DetailPesananController::class, 'index']);
Route::get('/kategori', [KategoriProdukController::class, 'index']);
Route::get('/alamat', [AlamatController::class, 'index']);
Route::get('/ulasan', [UlasanController::class, 'index']);
Route::get('/users', [UserController::class, 'index']);