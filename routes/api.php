<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\KategoriProdukController;
use App\Http\Controllers\Api\PesananController;

// Endpoint publik untuk login dan register
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Endpoint publik untuk melihat produk & kategori
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/kategori', [KategoriProdukController::class, 'index']);

// Endpoint yang dilindungi (butuh login/token)
Route::group(['middleware' => ['auth:sanctum']], function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Pesanan (Checkout)
    Route::post('/pesanan', [PesananController::class, 'store']);
    Route::get('/pesanan', [PesananController::class, 'index']); // Untuk pembeli & penjual

    // Khusus Produsen/Penjual
    Route::prefix('produsen')->group(function () {
        Route::get('/produk', [ProdukController::class, 'produkProdusen']); // Lihat produk sendiri
        Route::post('/produk', [ProdukController::class, 'store']); // Tambah produk baru
        // Route::put('/produk/{produk}', [ProdukController::class, 'update']);
        // Route::delete('/produk/{produk}', [ProdukController::class, 'destroy']);
    });
});