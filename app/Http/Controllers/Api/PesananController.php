<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pesanan;
use App\Models\Produk;

class PesananController extends Controller
{
    /**
     * Membuat pesanan baru (Checkout)
     * Ini meng-handle 1 pesanan untuk 1 penjual
     * Flutter app akan memanggil ini dalam loop jika ada >1 penjual
     */
    public function store(Request $request)
    {
        $pembeli = $request->user();

        $validatedData = $request->validate([
            'user_penjual_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            $createdOrder = DB::transaction(function () use ($pembeli, $validatedData) {
                
                $total_harga = 0;
                $itemsToCreate = [];
                $produkToUpdate = [];

                foreach ($validatedData['items'] as $item) {
                    $produk = Produk::findOrFail($item['produk_id']);

                    // Cek stok
                    if ($produk->stok < $item['jumlah']) {
                        throw new \Exception("Stok tidak mencukupi untuk produk: {$produk->nama_produk}");
                    }
                    
                    // Cek apakah produk milik penjual yang benar
                    if ($produk->user_id != $validatedData['user_penjual_id']) {
                        throw new \Exception("Produk {$produk->nama_produk} bukan milik penjual ini.");
                    }

                    $subtotal = $produk->harga * $item['jumlah'];
                    $total_harga += $subtotal;

                    $itemsToCreate[] = [
                        'produk_id' => $produk->id,
                        'jumlah' => $item['jumlah'],
                        'subtotal' => $subtotal,
                    ];
                    
                    $produkToUpdate[] = [
                        'id' => $produk->id,
                        'jumlah' => $item['jumlah'],
                    ];
                }

                // 1. Buat Pesanan
                $pesanan = Pesanan::create([
                    'user_pembeli_id' => $pembeli->id,
                    'user_penjual_id' => $validatedData['user_penjual_id'],
                    'status_pesanan' => 'pending',
                    'total_harga' => $total_harga,
                ]);

                // 2. Buat Detail Pesanan
                $pesanan->detail()->createMany($itemsToCreate);

                // 3. Kurangi Stok Produk
                foreach ($produkToUpdate as $p) {
                    Produk::find($p['id'])->decrement('stok', $p['jumlah']);
                }

                return $pesanan;
            });

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'pesanan_id' => $createdOrder->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat pesanan: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Melihat daftar pesanan (milik saya)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->tipe_user == 'pembeli') {
            $pesanan = $user->pesananPembeli()->with(['penjual', 'detail.produk'])->latest('tgl_pesanan')->get();
        } else if ($user->tipe_user == 'penjual') {
            $pesanan = $user->pesananPenjual()->with(['pembeli', 'detail.produk'])->latest('tgl_pesanan')->get();
        } else {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        // Kita belum buat PesananResource, jadi kembalikan JSON biasa
        return response()->json($pesanan);
    }
}
