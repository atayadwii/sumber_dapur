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
    Route::put('/pesanan/{id}/status', [PesananController::class, 'updateStatus']); // Update status (penjual)
    Route::post('/pesanan/{id}/complete', [PesananController::class, 'completeOrder']); // Complete order (pembeli)
    
    // Payment Validation Routes
    Route::post('/pesanan/{id}/upload-payment', [PesananController::class, 'uploadPaymentProof']); // Upload bukti pembayaran (pembeli)
    Route::post('/pesanan/{id}/upload-bukti-pembayaran', [PesananController::class, 'uploadPaymentProof']); // Alias untuk Flutter
    Route::post('/pesanan/{id}/confirm-payment', [PesananController::class, 'confirmPayment']); // Konfirmasi pembayaran (penjual)
    Route::post('/pesanan/{id}/konfirmasi-pembayaran', [PesananController::class, 'confirmPayment']); // Alias untuk Flutter
    Route::post('/pesanan/{id}/review', [PesananController::class, 'submitReview']); // Submit rating & review (pembeli)
    Route::post('/pesanan/{id}/selesaikan', [PesananController::class, 'submitReview']); // Alias untuk Flutter

    // Khusus Produsen/Penjual
    Route::prefix('produsen')->group(function () {
        Route::get('/produk', [ProdukController::class, 'produkProdusen']); // Lihat produk sendiri
        Route::post('/produk', [ProdukController::class, 'store']); // Tambah produk baru
        Route::put('/produk/{produk}', [ProdukController::class, 'update']); // Update produk
        Route::delete('/produk/{produk}', [ProdukController::class, 'destroy']); // Hapus produk
    });
});