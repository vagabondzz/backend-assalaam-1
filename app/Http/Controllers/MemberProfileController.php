<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MemberProfileController extends Controller
{
    /**
     * Tampilkan profil user
     */
    public function show()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User belum login'
            ], 401);
        }

        // Ambil profil dari tabel user_profil (MySQL)
        $profil = DB::table('user_profil')
            ->where('MEMBER_ID', $user->member_id)
            ->first();

        if (!$profil) {
            return response()->json([
                'success' => false,
                'message' => 'Profil belum ditemukan, mungkin belum diaktifkan admin'
            ], 404);
        }

        $statusNikah = $profil->MEMBER_IS_MARRIED == 1 ? 'Menikah' : 'Lajang';
        $statusKewarganegeraan = $profil->MEMBER_IS_WNI == 1? 'WNI' : 'WNA';

        return response()->json([
            'success' => true,
            'data' => [
                'MEMBER_ID'             => $profil->MEMBER_ID,
                'MEMBER_NAME'           => $profil->MEMBER_NAME,
                'MEMBER_CARD_NO'        => $profil->MEMBER_CARD_NO,
                'MEMBER_DATE_OF_BIRTH'  => $profil->MEMBER_DATE_OF_BIRTH,
                'MEMBER_PLACE_OF_BIRTH' => $profil->MEMBER_PLACE_OF_BIRTH,
                'MEMBER_SEX'            => $profil->MEMBER_SEX,
                'MEMBER_IS_WNI'         => $statusKewarganegeraan,
                'MEMBER_KTP_NO'         => $profil->MEMBER_KTP_NO,
                'MEMBER_ADDRESS'        => $profil->MEMBER_ADDRESS,
                'MEMBER_RT'             => $profil->MEMBER_RT,
                'MEMBER_RW'             => $profil->MEMBER_RW,
                'MEMBER_KELURAHAN'      => $profil->MEMBER_KELURAHAN,
                'MEMBER_KECAMATAN'      => $profil->MEMBER_KECAMATAN,
                'MEMBER_IS_MARRIED'     => $statusNikah,
                'MEMBER_TELP'           => $profil->MEMBER_TELP,
                'MEMBER_KOTA'           => $profil->MEMBER_KOTA,
                'MEMBER_POST_CODE'      => $profil->MEMBER_POST_CODE,
                'MEMBER_NPWP'           => $profil->MEMBER_NPWP,
                'MEMBER_JML_TANGGUNGAN' => $profil->MEMBER_JML_TANGGUNGAN,
                'MEMBER_PENDAPATAN'     => $profil->MEMBER_PENDAPATAN,
                'EMAIL'                 => $user->email,
                'PROFILE_PHOTO'         => $user->profile_photo 
                    ? asset('storage/'.$user->profile_photo) 
                    : null,
            ]
        ]);
    }

    /**
     * Update field editable profil + push ke backend kedua
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User belum login'
            ], 401);
        }

        $profil = DB::table('user_profil')
            ->where('MEMBER_ID', $user->member_id)
            ->first();

        if (!$profil) {
            return response()->json([
                'success' => false,
                'message' => 'Profil belum ditemukan'
            ], 404);
        }

        // validasi hanya field yang boleh diedit
        $validated = $request->validate([
            'MEMBER_ADDRESS'        => 'nullable|string|max:255',
            'MEMBER_TELP'           => 'nullable|string|max:20',
            'MEMBER_RT'             => 'nullable|integer',
            'MEMBER_KTP_NO'         => 'nullable|integer',
            'MEMBER_RW'             => 'nullable|integer',
            'MEMBER_IS_WNI'         => 'nullable|integer|in:0,1',
            'MEMBER_KELURAHAN'      => 'nullable|string|max:50',
            'MEMBER_KECAMATAN'      => 'nullable|string|max:50',
            'MEMBER_IS_MARRIED'     => 'nullable|integer|in:0,1',
            'MEMBER_KOTA'           => 'nullable|string|max:50',
            'MEMBER_POST_CODE'      => 'nullable|integer',
            'MEMBER_NPWP'           => 'nullable|string|max:50',
            'MEMBER_JML_TANGGUNGAN' => 'nullable|integer',
            'MEMBER_PENDAPATAN'     => 'nullable|numeric'
        ]);

        // update ke MySQL
        DB::table('user_profil')
            ->where('MEMBER_ID', $user->member_id)
            ->update(array_merge($validated, [
                'USER_CREATE' => 'web',
                'DATE_MODIFY' => now()
            ]));

        // kirim ke backend kedua (SQL Server)
        try {
            $token = $request->bearerToken();

            $payload = array_merge($validated, [
                'MEMBER_ID' => $user->member_id,
                'USER_UPDATE' => 'web'
            ]);

            $response = Http::withToken($token)
                ->put('http://127.0.0.1:8002/api/member/update-profile', $payload);

            if ($response->failed()) {
                Log::error('Gagal push update profil ke backend kedua', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error push update profil ke backend kedua', [
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui '
        ]);
    }

    /**
     * Upload / update foto profil user (disimpan di tabel users)
     */
    public function uploadPhoto(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User belum login'
            ], 401);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            // hapus foto lama jika ada
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // simpan foto baru
            $path = $request->file('photo')->store('profile', 'public');
            $user->profile_photo = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui',
                'photo_url' => asset('storage/'.$path)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada foto yang diupload'
        ], 400);
    }

    public function deletePhoto(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User belum login'
            ], 401);
        }

        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $user->profile_photo = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil dihapus'
        ]);
    }
}
