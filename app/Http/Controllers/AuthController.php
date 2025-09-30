<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
            // 1️⃣ Validasi dasar (tanpa unique:users supaya kita kontrol pesan)
            $validated = $request->validate([
                'member_card_no' => 'nullable|string',
                'date_of_birth'  => 'nullable|date|before:today|required_with:member_card_no',
                'email'          => 'required|string|email|max:255',
                'password'       => 'required|string|min:8',
                'name'           => 'nullable|string|max:255',
            ]);

            // 2️⃣ Cek email dulu -> kembalikan sebagai validation error (422) supaya konsisten di frontend
            if (User::where('email', $validated['email'])->exists()) {
                return response()->json([
                    'errors' => [
                        'email' => ['Email ini sudah terdaftar. Gunakan email lain.'],
                    ]
                ], 422);
            }

            $memberId     = null;
            $memberCardNo = null;
            $name         = $validated['name'] ?? null;
            $dob          = $validated['date_of_birth'] ?? null;

            // Helper: normalisasi tanggal input jika ada
            $inputDobNormalized = null;
            if (!empty($dob)) {
                try {
                    $inputDobNormalized = Carbon::parse($dob)->format('Y-m-d');
                } catch (\Throwable $ex) {
                    $inputDobNormalized = $dob; // fallback ke string asli jika parse error
                }
            }

            // 3️⃣ Kalau user isi nomor member card -> cek lokal dulu, lalu backend2
            if (!empty($validated['member_card_no'])) {

                // cek di tabel user_profils lokal
                $existingProfil = UserProfil::where('MEMBER_CARD_NO', $validated['member_card_no'])->first();

                if ($existingProfil) {
                    // jika profil lokal ada -> pastikan tanggal lahir cocok (normalisasi)
                    $profileDobNormalized = null;
                    if (!empty($existingProfil->MEMBER_DATE_OF_BIRTH)) {
                        try {
                            $profileDobNormalized = Carbon::parse($existingProfil->MEMBER_DATE_OF_BIRTH)->format('Y-m-d');
                        } catch (\Throwable $ex) {
                            $profileDobNormalized = $existingProfil->MEMBER_DATE_OF_BIRTH;
                        }
                    }

                    if ($inputDobNormalized && $profileDobNormalized && $profileDobNormalized !== $inputDobNormalized) {
                        return response()->json([
                            'errors' => [
                                'date_of_birth' => ['Tanggal lahir tidak sesuai dengan data member lokal.'],
                            ]
                        ], 422);
                    }

                    // cek apakah member sudah punya akun
                    if (User::where('member_id', $existingProfil->MEMBER_ID)->exists()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Member ini sudah memiliki akun.',
                        ], 409);
                    }

                    // gunakan data profil lokal
                    $memberId     = $existingProfil->MEMBER_ID;
                    $memberCardNo = $existingProfil->MEMBER_CARD_NO;
                    $name         = $existingProfil->MEMBER_NAME;
                    $dob          = $existingProfil->MEMBER_DATE_OF_BIRTH;
                } else {

                    $backend2Url = env('BACKEND_2');
                    $response = Http::post($backend2Url . '/api/member/check', [
                        'card_no' => $validated['member_card_no'],
                    ]);

                    // parse body (aman jika backend2 mengembalikan JSON meskipun HTTP 200 dengan success:false)
                    $body = null;
                    try {
                        $body = $response->json();
                    } catch (\Throwable $ex) {
                        // fallback decode
                        $body = json_decode($response->body(), true);
                    }

                    // Jika backend2 mengembalikan success:false atau pesan "tidak ditemukan", anggap member tidak ditemukan
                    if (is_array($body) && (isset($body['success']) && $body['success'] === false
                        || (!empty($body['message']) && stripos($body['message'], 'tidak ditemukan') !== false)
                    )) {
                        return response()->json([
                            'errors' => [
                                'member_card_no' => [$body['message'] ?? 'Nomor member card tidak ditemukan atau salah.'],
                            ]
                        ], 422);
                    }

                    // jika HTTP request gagal (500/timeout dll) dan tidak ada pesan khusus -> laporkan 500
                    if ($response->failed() && empty($body)) {
                        Log::error('Backend2 request failed (no body)', [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal mengambil data member dari backend kedua.',
                            'details' => $response->body(),
                        ], 500);
                    }

                    // Ambil data member dari response (harusnya di key 'data')
                    $memberData = $body['data'] ?? null;

                    // jika tidak ada data valid -> member tidak ditemukan
                    if (empty($memberData) || empty($memberData['MEMBER_ID'])) {
                        return response()->json([
                            'errors' => [
                                'member_card_no' => ['Nomor member card tidak ditemukan atau salah.'],
                            ]
                        ], 422);
                    }

                    // cek tanggal lahir dari backend2 (normalisasi)
                    $memberDobNormalized = null;
                    if (!empty($memberData['MEMBER_DATE_OF_BIRTH'])) {
                        try {
                            $memberDobNormalized = Carbon::parse($memberData['MEMBER_DATE_OF_BIRTH'])->format('Y-m-d');
                        } catch (\Throwable $ex) {
                            $memberDobNormalized = $memberData['MEMBER_DATE_OF_BIRTH'];
                        }
                    }

                    if ($inputDobNormalized && $memberDobNormalized && $memberDobNormalized !== $inputDobNormalized) {
                        return response()->json([
                            'errors' => [
                                'date_of_birth' => ['Tanggal lahir tidak sesuai dengan data member.'],
                            ]
                        ], 422);
                    }

                    // cek apakah member sudah punya akun di tabel users
                    if (User::where('member_id', $memberData['MEMBER_ID'])->exists()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Member ini sudah memiliki akun.',
                        ], 409);
                    }

                    // simpan profil ke lokal (gunakan data backend2; pastikan MEM... fields ada)
                    UserProfil::create([
                        'MEMBER_ID'             => $memberData['MEMBER_ID'],
                        'MEMBER_CARD_NO'        => $memberData['MEMBER_CARD_NO'] ?? $validated['member_card_no'],
                        'MEMBER_NAME'           => $memberData['MEMBER_NAME'] ?? null,
                        'MEMBER_PLACE_OF_BIRTH' => $memberData['MEMBER_PLACE_OF_BIRTH'] ?? null,
                        'MEMBER_DATE_OF_BIRTH'  => $memberData['MEMBER_DATE_OF_BIRTH'] ?? $dob,
                        'MEMBER_KTP_NO'         => $memberData['MEMBER_KTP_NO'] ?? null,
                        'MEMBER_SEX'            => $memberData['MEMBER_SEX'] ?? null,
                        'MEMBER_RT'             => $memberData['MEMBER_RT'] ?? null,
                        'MEMBER_RW'             => $memberData['MEMBER_RW'] ?? null,
                        'MEMBER_KELURAHAN'      => $memberData['MEMBER_KELURAHAN'] ?? null,
                        'MEMBER_KECAMATAN'      => $memberData['MEMBER_KECAMATAN'] ?? null,
                        'MEMBER_KOTA'           => $memberData['MEMBER_KOTA'] ?? null,
                        'MEMBER_POST_CODE'      => $memberData['MEMBER_POST_CODE'] ?? null,
                        'MEMBER_ADDRESS'        => $memberData['MEMBER_ADDRESS'] ?? null,
                        'MEMBER_JML_TANGGUNGAN' => $memberData['MEMBER_JML_TANGGUNGAN'] ?? null,
                        'MEMBER_PENDAPATAN'     => $memberData['MEMBER_PENDAPATAN'] ?? null,
                        'MEMBER_TELP'           => $memberData['MEMBER_TELP'] ?? null,
                        'MEMBER_NPWP'           => $memberData['MEMBER_NPWP'] ?? null,
                        'MEMBER_IS_MARRIED'     => $memberData['MEMBER_IS_MARRIED'] ?? null,
                        'MEMBER_IS_WNI'         => $memberData['MEMBER_IS_WNI'] ?? null,
                        'REF$AGAMA_ID'          => $memberData['REF$AGAMA_ID'] ?? null,
                        'DATE_CREATE'           => now(),
                        'MEMBER_IS_VALID'       => $memberData['MEMBER_IS_VALID'] ?? 0,
                        'MEMBER_ACTIVE_FROM'    => $memberData['MEMBER_ACTIVE_FROM'] ?? null,
                        'MEMBER_ACTIVE_TO'      => $memberData['MEMBER_ACTIVE_TO'] ?? null,
                        'MEMBER_KUPON'          => $memberData['MEMBER_KUPON'] ?? null,
                    ]);

                    // set variable untuk user
                    $memberId     = $memberData['MEMBER_ID'];
                    $memberCardNo = $memberData['MEMBER_CARD_NO'] ?? $validated['member_card_no'];
                    $name         = $memberData['MEMBER_NAME'] ?? $validated['email'];
                    $dob          = $memberData['MEMBER_DATE_OF_BIRTH'] ?? $dob;
                }
            }

            // 4️⃣ Buat user baru di MySQL
            $user = User::create([
                'name'           => $name ?? $validated['email'],
                'email'          => $validated['email'],
                'password'       => Hash::make($validated['password']),
                'role'           => 'user',
                'member_id'      => $memberId,
                'member_card_no' => $memberCardNo,
            ]);

            event(new Registered($user));

            // 5️⃣ Buat JWT token untuk user yang baru register
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success'    => true,
                'message'    => 'Registrasi berhasil.',
                'user'       => $user,
                'token'      => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Register error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada proses registrasi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function registerCs(Request $request)
    {
        // ✅ Logging awal request
        Log::info('Request register CS diterima', ['payload' => $request->all()]);

        // ✅ Cek apakah user yang login adalah admin
        $user = auth('api')->user();
        if (!$user || $user->role !== 'admin') {
            Log::warning('Register CS ditolak, bukan admin', ['user' => $user]);
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            // ✅ Validasi input
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // ✅ Buat user baru dengan role CS
            $cs = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'cs', // langsung set role CS
            ]);

            // ✅ Kirim email verifikasi otomatis
            event(new Registered($cs));

            Log::info('Register CS berhasil', ['cs_id' => $cs->id]);

            return response()->json([
                'success' => true,
                'message' => 'Registrasi CS berhasil. Silakan cek email untuk verifikasi.',
                'user'    => $cs,
            ], 201);
        } catch (\Throwable $e) {
            // ✅ Logging error detail
            Log::error('Register CS error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada proses registrasi CS.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $userExists = \App\Models\User::where('email', $credentials['email'])->exists();
        if (!$userExists) {
            return response()->json([
                'success' => false,
                'message' => 'Email tersebut belum terdaftar.',
            ], 404);
        }

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = auth('api')->user();
        $user->last_seen_at = now();
        $user->save();

        // 🔹 Broadcast status online
        event(new \App\Events\AdminStatusUpdated($user));

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
        $user = auth('api')->user();

        if ($user) {
            // Kosongkan last_seen_at
            $user->last_seen_at = null;

            // Paksa save dan cek hasil
            if (!$user->save()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal update status logout.'
                ], 500);
            }

            // Broadcast status offline
            event(new \App\Events\AdminStatusUpdated($user));
        }

        // Logout JWT setelah last_seen_at sudah tersimpan
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
