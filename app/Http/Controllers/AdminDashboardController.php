<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminDashboardController extends Controller
{
    public function getDashboardSummary()
    {
        // Ambil JWT token user yang sedang login di backend utama
        $token = JWTAuth::getToken();

        // Ambil URL backend kedua dari .env
        $backend2Url = env('BACKEND_2');

        // Kirim request ke backend kedua
        $response = Http::withToken($token)
            ->get($backend2Url . '/api/admin/dashboard');

        // Cek kalau ada error dari backend kedua
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal ambil data dari backend kedua',
                'error'   => $response->json(),
            ], $response->status());
        }

        // Return data dari backend kedua ke frontend
        return response()->json($response->json());
    }
}
