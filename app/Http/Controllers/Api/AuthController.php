<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:45',
            'email' => 'required|string|email|max:45|unique:users',
            'password' => 'required|string|min:6',
            'no_hp' => 'nullable|string|max:20',
            // Flutter mengirim "Producer" atau "Buyer", validasi itu.
            'tipe_user' => ['required', 'string', Rule::in(['Producer', 'Buyer'])],
        ]);

        // Mapping dari Flutter Enum String ke Database Enum String
        $tipe_user_db = $validatedData['tipe_user'] == 'Producer' ? 'penjual' : 'pembeli';

        $user = User::create([
            'nama' => $validatedData['nama'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'no_hp' => $validatedData['no_hp'] ?? null,
            'tipe_user' => $tipe_user_db,
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'noHp' => $user->no_hp,
                'tipeUser' => $user->tipe_user == 'penjual' ? 'Producer' : 'Buyer',
                'tglDaftar' => $user->tgl_daftar,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'noHp' => $user->no_hp,
                'tipeUser' => $user->tipe_user == 'penjual' ? 'Producer' : 'Buyer',
                'tglDaftar' => $user->tgl_daftar,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil'], 200);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'noHp' => $user->no_hp,
            'tipeUser' => $user->tipe_user == 'penjual' ? 'Producer' : 'Buyer',
            'tglDaftar' => $user->tgl_daftar,
        ]);
    }
}