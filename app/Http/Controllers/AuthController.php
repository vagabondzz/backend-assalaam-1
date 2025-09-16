<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\UserProfil;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'member_card_no' => 'nullable|string',
                'date_of_birth'  => 'nullable|date|before:today|required_with:member_card_no',
                'email'          => 'required|string|email|max:255|unique:users',
                'password'       => 'required|string|min:8',
                'name'           => 'nullable|string|max:255',
            ]);

            $memberId = null;
            $memberCardNo = null;
            $name = $validated['name'] ?? null;
            $dob  = $validated['date_of_birth'] ?? null;
            $memberData = null;

            // 🔹 Kalau user isi nomor member card → ambil data member dari backend2 API (SQL Server)
            if (!empty($validated['member_card_no'])) {

                Log::info('Mengambil data member dari backend2 API', [
                    'card_no' => $validated['member_card_no']
                ]);

                // 🔹 Tidak pakai token (langsung panggil API backend2)
                $response = Http::post('http://127.0.0.1:8002/api/member/check', [
                    'card_no' => $validated['member_card_no'],
                ]);

                if ($response->failed()) {
                    Log::error('Gagal mengambil data member dari backend kedua', [
                        'response_status' => $response->status(),
                        'response_body'   => $response->body(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengambil data member dari backend kedua.',
                        'details' => $response->body(),
                    ], 500);
                }

                $memberData = $response->json()['data'] ?? null;

                if (!$memberData) {
                    Log::warning('Nomor kartu member tidak ditemukan', [
                        'card_no' => $validated['member_card_no'],
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Nomor kartu member tidak ditemukan.',
                    ], 404);
                }

                if ($memberData['MEMBER_DATE_OF_BIRTH'] != $dob) {
                    Log::warning('Tanggal lahir tidak sesuai', [
                        'card_no' => $validated['member_card_no'],
                        'input_dob' => $dob,
                        'member_dob' => $memberData['MEMBER_DATE_OF_BIRTH'],
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Tanggal lahir tidak sesuai dengan data member.',
                    ], 422);
                }

                // cek apakah member sudah punya akun
                if (User::where('member_id', $memberData['MEMBER_ID'])->exists()) {
                    Log::warning('Member sudah memiliki akun', [
                        'member_id' => $memberData['MEMBER_ID']
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Member ini sudah memiliki akun.',
                    ], 409);
                }

                $memberId     = $memberData['MEMBER_ID'];
                $memberCardNo = $memberData['MEMBER_CARD_NO'];
                $name         = $memberData['MEMBER_NAME'];
                $dob          = $memberData['MEMBER_DATE_OF_BIRTH'];
            }

            // 🔹 Buat user baru di MySQL
            $user = User::create([
                'name'           => $name ?? $validated['email'],
                'email'          => $validated['email'],
                'password'       => Hash::make($validated['password']),
                'role'           => 'user',
                'member_id'      => $memberId,
                'member_card_no' => $memberCardNo,
            ]);

            // 🔹 Kalau ada member → simpan profil MySQL
            if ($memberData) {
                UserProfil::create([
                    'MEMBER_ID'             => $memberData['MEMBER_ID'],
                    'MEMBER_CARD_NO'        => $memberData['MEMBER_CARD_NO'],
                    'MEMBER_NAME'           => $memberData['MEMBER_NAME'],
                    'MEMBER_PLACE_OF_BIRTH' => $memberData['MEMBER_PLACE_OF_BIRTH'],
                    'MEMBER_DATE_OF_BIRTH'  => $dob,
                    'MEMBER_KTP_NO'         => $memberData['MEMBER_KTP_NO'],
                    'MEMBER_SEX'            => $memberData['MEMBER_SEX'],
                    'MEMBER_RT'             => $memberData['MEMBER_RT'],
                    'MEMBER_RW'             => $memberData['MEMBER_RW'],
                    'MEMBER_KELURAHAN'      => $memberData['MEMBER_KELURAHAN'],
                    'MEMBER_KECAMATAN'      => $memberData['MEMBER_KECAMATAN'],
                    'MEMBER_KOTA'           => $memberData['MEMBER_KOTA'],
                    'MEMBER_POST_CODE'      => $memberData['MEMBER_POST_CODE'],
                    'MEMBER_ADDRESS'        => $memberData['MEMBER_ADDRESS'],
                    'MEMBER_JML_TANGGUNGAN' => $memberData['MEMBER_JML_TANGGUNGAN'],
                    'MEMBER_PENDAPATAN'     => $memberData['MEMBER_PENDAPATAN'],
                    'MEMBER_TELP'           => $memberData['MEMBER_TELP'],
                    'MEMBER_NPWP'           => $memberData['MEMBER_NPWP'],
                    'MEMBER_IS_MARRIED'     => $memberData['MEMBER_IS_MARRIED'],
                    'MEMBER_IS_WNI'         => $memberData['MEMBER_IS_WNI'],
                    'REF$AGAMA_ID'          => $memberData['REF$AGAMA_ID'],
                    'DATE_CREATE'           => now(),
                    'MEMBER_IS_VALID'       => $memberData['MEMBER_IS_VALID'] ?? 0,
                    'MEMBER_ACTIVE_FROM'    => $memberData['MEMBER_ACTIVE_FROM'] ?? null,
                    'MEMBER_ACTIVE_TO'      => $memberData['MEMBER_ACTIVE_TO'] ?? null,
                    'MEMBER_KUPON'          => $memberData['MEMBER_KUPON']
                ]);
            }

            event(new Registered($user));

            // 🔹 Buat JWT token untuk user yang baru register (untuk login langsung)
            $token = JWTAuth::fromUser($user);

            Log::info('Registrasi user berhasil', ['user_id' => $user->id]);

            return response()->json([
                'success'    => true,
                'message'    => 'Registrasi berhasil.',
                'user'       => $user,
                'token'      => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Error pada proses registrasi', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada proses registrasi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = auth('api')->user();

        $isMember = !empty($user->member_id);
        $memberData = null;

        if ($isMember) {
            $memberData = UserProfil::find($user->member_id);
        }

        $response = [
            'success'      => true,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60,
            'role'         => $user->role,
            'user'         => $user,
            'is_member'    => $isMember,
            'member'       => $memberData,
        ];

        return response()->json($response);
    }

    /**
     * Ambil user yang sedang login
     */
    public function me()
    {
        $user = auth('api')->user();

        $memberData = null;
        if (!empty($user->member_id)) {
            $memberData = UserProfil::find($user->member_id);
        }

        return response()->json([
            'user'      => $user,
            'is_member' => !empty($user->member_id),
            'member'    => $memberData,
        ]);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.'
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success'      => true,
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                'expires_in'   => JWTAuth::factory()->getTTL() * 60,
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Token tidak valid atau sudah kadaluarsa.'
            ], 401);
        }
    }
}
