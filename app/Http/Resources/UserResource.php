<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'nama' => $this->nama,
            'email' => $this->email,
            'noHp' => $this->no_hp,
            // Konversi 'penjual' -> 'Producer', 'pembeli' -> 'Buyer'
            // agar sesuai dengan enum UserType di Flutter
            'tipeUser' => $this->tipe_user == 'penjual' ? 'Producer' : ($this->tipe_user == 'pembeli' ? 'Buyer' : 'Admin'),
            'tglDaftar' => $this->tgl_daftar,
        ];
    }
}