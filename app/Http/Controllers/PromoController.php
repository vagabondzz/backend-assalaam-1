<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PromoController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:webp,jpg|max:2048',
            ]);

            $path = $request->file('image')->store('promo', 'public');

            $promo = Promo::create([
                'path' => $path,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Promo berhasil ditambahkan.',
                'data' => $promo,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan promo: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan promo.',
            ], 500);
        }
    }

    public function index()
    {
        try {
            $promos = Promo::orderByDesc('created_at')->get();
            return response()->json($promos);
        } catch (\Exception $e) {
            Log::error('Error saat mengambil daftar promo: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar promo.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $promo = Promo::findOrFail($id);

            if (Storage::disk('public')->exists($promo->path)) {
                Storage::disk('public')->delete($promo->path);
            }

            $promo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Promo berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat menghapus promo: '.$e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus promo.',
            ], 500);
        }
    }
}
