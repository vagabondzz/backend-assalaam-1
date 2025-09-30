<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        // Ambil query param dari request (misal: search, per_page, page)
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 20);
        $page = $request->query('page', 1);

        $token = JWTAuth::getToken();

        $backend2Url = env('BACKEND_2');
        $response = Http::withToken($token)
        ->get( $backend2Url . '/api/admin/transaksi', [
            'search' => $search,
            'per_page' => $perPage,
            'page' => $page,
        ]);

    
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dari backend kedua',
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json(), $response->status());
    }

    public function show($transNo)
    {
        $response = Http::get("http://127.0.0.1:8002/api/admin/transaksi/{$transNo}");

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail transaksi dari backend kedua',
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json(), $response->status());
    }
}
