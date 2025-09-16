<?php

namespace App\Http\Controllers;

use App\Models\UserProfil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class AdminMemberController extends Controller
{
    /**
     * Tampilkan daftar member
     * (nama, nomor member, status aktivasi, validasi, dan masa aktif)
     */
    public function index()
    {
        $members = UserProfil::select(
            'MEMBER_ID',
            'MEMBER_NAME',
            'MEMBER_CARD_NO',
            'MEMBER_IS_ACTIVE',
            'MEMBER_IS_VALID',
            'MEMBER_ACTIVE_TO'
        )->get();

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    public function show($id)
    {
        // ambil detail member di MySQL
        $member = UserProfil::findOrFail($id);

        return response()->json(['success' => true, 'data' => $member]);
    }

    /**
     * Update status aktivasi member via MySQL + API ke backend kedua (SQL Server)
     */
    public function updateActivation(Request $request, $id)
    {
        $request->validate([
            'MEMBER_IS_ACTIVE' => 'required|integer',
        ]);

        $statusAktif = $request->MEMBER_IS_ACTIVE;

        try {
            // 1️⃣ Update ke user_profil (MySQL)
            $userProfil = UserProfil::on('mysql')->findOrFail($id);
            $userProfil->MEMBER_IS_ACTIVE = $statusAktif;

            if ($statusAktif == 1 && empty($userProfil->MEMBER_ACTIVE_TO)) {
                $userProfil->MEMBER_ACTIVE_FROM = now();
                $userProfil->MEMBER_ACTIVE_TO   = now()->addYear();
            }
            $userProfil->save();

            // 2️⃣ Kirim ke backend kedua (SQL Server) via API + JWT
            $payload = [
                'MEMBER_ID'       => $userProfil->MEMBER_ID,
                'MEMBER_IS_ACTIVE'=> $statusAktif,
                'MEMBER_ACTIVE_FROM' => $userProfil->MEMBER_ACTIVE_FROM,
                'MEMBER_ACTIVE_TO'   => $userProfil->MEMBER_ACTIVE_TO,
            ];

            Log::info('Mengirim update aktivasi ke backend kedua', $payload);

            $token = JWTAuth::getToken(); // ambil token JWT yang sedang dipakai
            $response = Http::withToken($token)
                ->post('http://127.0.0.1:8002/api/member/active', $payload);

            if ($response->failed()) {
                Log::error('Respon gagal dari backend kedua', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal update aktivasi member di backend kedua',
                    'details' => $response->body()
                ], 500);
            }

            $dataFromBackend2 = $response->json();
            Log::info('Berhasil update aktivasi di backend kedua', $dataFromBackend2);

            return response()->json([
                'success' => true,
                'message' => 'Status aktivasi member berhasil diperbarui di MySQL & backend kedua',
                'data'    => [
                    'user_profil' => $userProfil,
                    'backend2'    => $dataFromBackend2,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error di fungsi updateActivation: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat update aktivasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
