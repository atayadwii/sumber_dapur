<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriProduk;
use App\Http\Resources\KategoriResource;

class KategoriProdukController extends Controller
{
    public function index()
    {
        return KategoriResource::collection(KategoriProduk::all());
    }
}