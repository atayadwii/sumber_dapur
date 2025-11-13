<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdukResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'namaProduk' => $this->nama_produk,
            'deskripsi' => $this->deskripsi_produk,
            'harga' => (float) $this->harga,
            'stok' => (int) $this->stok,
            'satuan' => $this->satuan,
            // 'kategoriId' => $this->kategori_id,
            // 'penjualId' => $this->user_id,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'penjual' => new UserResource($this->whenLoaded('user')),
        ];
    }
}