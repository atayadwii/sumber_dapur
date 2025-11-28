<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Http\Resources\ProdukResource;

class ProdukController extends Controller
{
    /**
     * Menampilkan semua produk untuk HomeScreen
     */
    public function index()
    {
        // Eager load relasi 'kategori' dan 'user' (penjual)
        $produk = Produk::with(['kategori', 'user'])->latest()->get();
        return ProdukResource::collection($produk);
    }

    /**
     * Menampilkan produk HANYA milik produsen yang sedang login
     */
    public function produkProdusen(Request $request)
    {
        $user = $request->user();
        if ($user->tipe_user != 'penjual') {
            return response()->json(['message' => 'Hanya produsen yang bisa mengakses'], 403);
        }

        $produk = $user->produk()->with('kategori')->latest()->get();
        return ProdukResource::collection($produk);
    }

    /**
     * Menyimpan produk baru
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->tipe_user != 'penjual') {
            return response()->json(['message' => 'Hanya produsen yang bisa menambah produk'], 403);
        }

        $validatedData = $request->validate([
            'kategori_id' => 'required|exists:kategori_produk,id',
            'nama_produk' => 'required|string|max:45',
            'deskripsi_produk' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
        ]);

        // Tambahkan user_id dari user yang sedang login
        $validatedData['user_id'] = $user->id;

        $produk = Produk::create($validatedData);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'produk' => new ProdukResource($produk->load('kategori'))
        ], 201);
    }

    /**
     * Mengupdate produk yang sudah ada
     */
    public function update(Request $request, Produk $produk)
    {
        $user = $request->user();
        if ($user->tipe_user != 'penjual') {
            return response()->json(['message' => 'Hanya produsen yang bisa mengupdate produk'], 403);
        }

        // Pastikan produk ini milik user yang sedang login
        if ($produk->user_id != $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk mengupdate produk ini'], 403);
        }

        $validatedData = $request->validate([
            'kategori_id' => 'sometimes|required|exists:kategori_produk,id',
            'nama_produk' => 'sometimes|required|string|max:45',
            'deskripsi_produk' => 'nullable|string',
            'harga' => 'sometimes|required|numeric|min:0',
            'stok' => 'sometimes|required|integer|min:0',
            'satuan' => 'sometimes|required|string|max:50',
        ]);

        $produk->update($validatedData);

        return response()->json([
            'message' => 'Produk berhasil diupdate',
            'produk' => new ProdukResource($produk->load('kategori'))
        ], 200);
    }

    /**
     * Menghapus produk
     */
    public function destroy(Request $request, Produk $produk)
    {
        $user = $request->user();
        if ($user->tipe_user != 'penjual') {
            return response()->json(['message' => 'Hanya produsen yang bisa menghapus produk'], 403);
        }

        // Pastikan produk ini milik user yang sedang login
        if ($produk->user_id != $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk menghapus produk ini'], 403);
        }

        $produk->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus'
        ], 200);
    }
}