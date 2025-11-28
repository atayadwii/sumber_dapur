<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
                    'status_pesanan' => 'menunggu_pembayaran',
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
                'success' => true,
                'message' => 'Pesanan berhasil dibuat. Silakan upload bukti pembayaran.',
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

    /**
     * Update status pesanan (untuk Penjual)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $pesanan = Pesanan::with(['detail.produk'])->findOrFail($id);
            $user = $request->user();

            // Validasi status
            $validatedData = $request->validate([
                'status' => 'required|in:pending,proses,selesai,batal',
            ]);

            // Authorization: Pastikan user adalah penjual dari pesanan ini
            if ($pesanan->user_penjual_id != $user->id) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk mengupdate pesanan ini'
                ], 403);
            }

            // Update status
            $pesanan->update([
                'status_pesanan' => $validatedData['status']
            ]);

            return response()->json([
                'message' => 'Status pesanan berhasil diupdate',
                'pesanan' => $pesanan->load(['pembeli', 'penjual', 'detail.produk'])
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error('Error updating pesanan status: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengupdate status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Complete order dengan upload bukti (untuk Pembeli)
     */
    public function completeOrder(Request $request, $id)
    {
        try {
            $pesanan = Pesanan::with(['detail.produk'])->findOrFail($id);
            $user = $request->user();

            // Authorization: Pastikan user adalah pembeli dari pesanan ini
            if ($pesanan->user_pembeli_id != $user->id) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk menyelesaikan pesanan ini'
                ], 403);
            }

            // Validasi status pesanan harus 'proses'
            if ($pesanan->status_pesanan != 'proses') {
                return response()->json([
                    'message' => 'Pesanan harus berstatus proses untuk dapat diselesaikan'
                ], 400);
            }

            // Validasi file upload
            $validatedData = $request->validate([
                'bukti_penerimaan' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Upload file
            $file = $request->file('bukti_penerimaan');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('bukti_pesanan', $fileName, 'public');

            // Update pesanan
            $pesanan->update([
                'status_pesanan' => 'selesai',
                'bukti_penerimaan' => $filePath
            ]);

            return response()->json([
                'message' => 'Pesanan berhasil diselesaikan',
                'pesanan' => $pesanan->load(['pembeli', 'penjual', 'detail.produk'])
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error completing pesanan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyelesaikan pesanan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload bukti pembayaran oleh pembeli
     * POST /api/pesanan/{id}/upload-payment
     */
    public function uploadPaymentProof(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            ], [
                'bukti_pembayaran.required' => 'Bukti pembayaran wajib diupload',
                'bukti_pembayaran.image' => 'File harus berupa gambar',
                'bukti_pembayaran.mimes' => 'Format gambar harus jpeg, png, atau jpg',
                'bukti_pembayaran.max' => 'Ukuran gambar maksimal 5MB',
            ]);

            $pesanan = Pesanan::with(['detail.produk'])->findOrFail($id);
            $user = $request->user();

            // Validasi: hanya pembeli yang dapat upload
            if ($pesanan->user_pembeli_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk pesanan ini'
                ], 403);
            }

            // Validasi: status harus menunggu_pembayaran
            if ($pesanan->status_pesanan !== 'menunggu_pembayaran') {
                return response()->json([
                    'success' => false,
                    'message' => 'Status pesanan tidak valid untuk upload bukti pembayaran'
                ], 400);
            }

            // Hapus bukti pembayaran lama jika ada
            if ($pesanan->bukti_pembayaran) {
                Storage::disk('public')->delete($pesanan->bukti_pembayaran);
            }

            // Upload file baru
            $file = $request->file('bukti_pembayaran');
            $filename = time() . '_' . $pesanan->id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('payment_proofs', $filename, 'public');

            // Update pesanan
            $pesanan->update([
                'bukti_pembayaran' => $path,
                'status_pesanan' => 'menunggu_konfirmasi',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload. Menunggu konfirmasi penjual.',
                'pesanan' => $pesanan->fresh(['pembeli', 'penjual', 'detail.produk'])
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error uploading payment proof: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal upload bukti: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Konfirmasi pembayaran oleh penjual (Terima/Tolak)
     * POST /api/pesanan/{id}/confirm-payment
     */
    public function confirmPayment(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'is_accepted' => 'required|boolean',
                'rejection_reason' => 'required_if:is_accepted,false|string|max:500',
            ], [
                'is_accepted.required' => 'Status konfirmasi wajib diisi',
                'is_accepted.boolean' => 'Status konfirmasi harus true atau false',
                'rejection_reason.required_if' => 'Alasan penolakan wajib diisi',
                'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter',
            ]);

            $pesanan = Pesanan::with(['detail.produk'])->findOrFail($id);
            $user = $request->user();

            // Validasi: hanya penjual yang dapat konfirmasi
            if ($pesanan->user_penjual_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk pesanan ini'
                ], 403);
            }

            // Validasi: status harus menunggu_konfirmasi
            if ($pesanan->status_pesanan !== 'menunggu_konfirmasi') {
                return response()->json([
                    'success' => false,
                    'message' => 'Status pesanan tidak valid untuk konfirmasi pembayaran'
                ], 400);
            }

            $isAccepted = $request->input('is_accepted');

            if ($isAccepted) {
                // TERIMA pembayaran
                $pesanan->update([
                    'status_pesanan' => 'proses',
                    'is_paid' => true,
                    'paid_at' => now(),
                ]);

                $message = 'Pembayaran diterima. Pesanan sedang diproses.';
            } else {
                // TOLAK pembayaran
                $rejectionReason = $request->input('rejection_reason');
                
                $pesanan->update([
                    'status_pesanan' => 'batal',
                    'is_paid' => false,
                    'rejection_reason' => $rejectionReason,
                ]);

                $message = 'Pembayaran ditolak. Pesanan dibatalkan.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'pesanan' => $pesanan->fresh(['pembeli', 'penjual', 'detail.produk'])
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error confirming payment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal konfirmasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Submit rating & review oleh pembeli (Selesaikan pesanan)
     * POST /api/pesanan/{id}/review
     */
    public function submitReview(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'rating' => 'required|numeric|min:1|max:5',
                'review' => 'required|string|min:10|max:1000',
                'review_images' => 'nullable|array|max:5',
                'review_images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            ], [
                'rating.required' => 'Rating wajib diisi',
                'rating.min' => 'Rating minimal 1',
                'rating.max' => 'Rating maksimal 5',
                'review.required' => 'Review wajib diisi',
                'review.min' => 'Review minimal 10 karakter',
                'review.max' => 'Review maksimal 1000 karakter',
                'review_images.max' => 'Maksimal 5 foto produk',
                'review_images.*.image' => 'File harus berupa gambar',
                'review_images.*.mimes' => 'Format gambar harus jpeg, png, atau jpg',
                'review_images.*.max' => 'Ukuran gambar maksimal 5MB',
            ]);

            $pesanan = Pesanan::with(['detail.produk'])->findOrFail($id);
            $user = $request->user();

            // Validasi: hanya pembeli yang dapat submit review
            if ($pesanan->user_pembeli_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk pesanan ini'
                ], 403);
            }

            // Validasi: status harus proses atau selesai (untuk update review)
            if (!in_array($pesanan->status_pesanan, ['proses', 'selesai'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan belum dapat diselesaikan'
                ], 400);
            }

            // Upload review images jika ada
            $reviewImagePaths = [];
            if ($request->hasFile('review_images')) {
                foreach ($request->file('review_images') as $index => $image) {
                    $filename = time() . '_' . $pesanan->id . '_' . $index . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('review_images', $filename, 'public');
                    $reviewImagePaths[] = $path;
                }
            }

            // Update pesanan
            $pesanan->update([
                'status_pesanan' => 'selesai',
                'rating' => $request->input('rating'),
                'review' => $request->input('review'),
                'review_images' => $reviewImagePaths,
                'completed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Terima kasih! Pesanan telah diselesaikan.',
                'pesanan' => $pesanan->fresh(['pembeli', 'penjual', 'detail.produk'])
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error submitting review: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal submit review: ' . $e->getMessage()], 500);
        }
    }
}
