<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AdminGuestController extends Controller
{
    /**
     * Tampilkan daftar member
     */
    public function index()
    {
        try {
            $members = Guest::select(
                'MEMBER_ID',
                'MEMBER_NAME',
                'MEMBER_CARD_NO',
                'MEMBER_IS_WNI',
                'MEMBER_IS_VALID',
                'MEMBER_PLACE_OF_BIRTH',
                'MEMBER_DATE_OF_BIRTH',
                'MEMBER_KTP_NO',
                'MEMBER_ADDRESS',
                'MEMBER_KELURAHAN',
                'MEMBER_KECAMATAN',
                'MEMBER_KOTA',
                'MEMBER_RT',
                'MEMBER_RW',
                'MEMBER_POST_CODE',
                'MEMBER_JML_TANGGUNGAN',
                'MEMBER_PENDAPATAN',
                'MEMBER_TELP',
                'MEMBER_NPWP'
            )->get();

            Log::info('Berhasil ambil daftar member', ['jumlah' => $members->count()]);

            return response()->json([
                'success' => true,
                'data' => $members,
            ]);
        } catch (\Exception $e) {
            Log::error('Error ambil daftar member: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal ambil daftar member',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $member = Guest::findOrFail($id);
            Log::info('Berhasil ambil detail member', ['id' => $id]);
            return response()->json([
                'success' => true,
                'data' => $member
            ]);
        } catch (\Exception $e) {
            Log::error('Error ambil detail member: '.$e->getMessage(), ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal ambil detail member',
            ], 500);
        }
    }

    public function activate(Request $request, $id)
    {
        try {
            $guest = Guest::findOrFail($id);
            $tipeMember = $request->input('MEMBER_TYPE', $guest->MEMBER_TYPE ?? 'PAS');

            $payload = [
                'MEMBER_ID'            => $guest->MEMBER_ID,
                'MEMBER_NAME'          => $guest->MEMBER_NAME,
                'MEMBER_IS_WNI'        => $guest->MEMBER_IS_WNI,
                'MEMBER_PLACE_OF_BIRTH'=> $guest->MEMBER_PLACE_OF_BIRTH,
                'MEMBER_DATE_OF_BIRTH' => $guest->MEMBER_DATE_OF_BIRTH,
                'MEMBER_KTP_NO'        => $guest->MEMBER_KTP_NO,
                'MEMBER_SEX'           => $guest->MEMBER_SEX,
                'MEMBER_RT'            => $guest->MEMBER_RT,
                'MEMBER_RW'            => $guest->MEMBER_RW,
                'MEMBER_KELURAHAN'     => $guest->MEMBER_KELURAHAN,
                'MEMBER_KECAMATAN'     => $guest->MEMBER_KECAMATAN,
                'MEMBER_KOTA'          => $guest->MEMBER_KOTA,
                'MEMBER_IS_MARRIED'    => $guest->MEMBER_IS_MARRIED,
                'MEMBER_POST_CODE'     => $guest->MEMBER_POST_CODE,
                'MEMBER_ADDRESS'       => $guest->MEMBER_ADDRESS,
                'MEMBER_JML_TANGGUNGAN'=> $guest->MEMBER_JML_TANGGUNGAN,
                'MEMBER_PENDAPATAN'    => $guest->MEMBER_PENDAPATAN,
                'MEMBER_TELP'          => $guest->MEMBER_TELP,
                'MEMBER_NPWP'          => $guest->MEMBER_NPWP,
                'MEMBER_TYPE'          => $tipeMember,
            ];

            Log::info('Mengirim payload ke backend kedua', $payload);

            $token = JWTAuth::getToken();
            
            $backend2Url = env('BACKEND_2');
            $response = Http::withToken($token)
                ->post( $backend2Url. '/api/member/validate', $payload);

            if ($response->failed()) {
                Log::error('Respon gagal dari backend kedua', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal aktivasi member di backend kedua',
                    'details' => $response->body()
                ], 500);
            }

            $dataFromBackend2 = $response->json();
            Log::info('Berhasil terima respon backend kedua', $dataFromBackend2);

            DB::table('user_profil')->updateOrInsert(
                ['MEMBER_ID' => $guest->MEMBER_ID],
                [
                    'MEMBER_CARD_NO'       => $dataFromBackend2['member_card_no'] ?? null,
                    'MEMBER_TYPE'          => $tipeMember,
                    'MEMBER_NAME'          => $guest->MEMBER_NAME,
                    'MEMBER_IS_WNI'        => $guest->MEMBER_IS_WNI,
                    'MEMBER_PLACE_OF_BIRTH'=> $guest->MEMBER_PLACE_OF_BIRTH,
                    'MEMBER_DATE_OF_BIRTH' => $guest->MEMBER_DATE_OF_BIRTH,
                    'MEMBER_KTP_NO'        => $guest->MEMBER_KTP_NO,
                    'MEMBER_SEX'           => $guest->MEMBER_SEX,
                    'MEMBER_RT'            => $guest->MEMBER_RT,
                    'MEMBER_RW'            => $guest->MEMBER_RW,
                    'MEMBER_KELURAHAN'     => $guest->MEMBER_KELURAHAN,
                    'MEMBER_KECAMATAN'     => $guest->MEMBER_KECAMATAN,
                    'MEMBER_IS_MARRIED'    => $guest->MEMBER_IS_MARRIED,
                    'MEMBER_KOTA'          => $guest->MEMBER_KOTA,
                    'MEMBER_POST_CODE'     => $guest->MEMBER_POST_CODE,
                    'MEMBER_ADDRESS'       => $guest->MEMBER_ADDRESS,
                    'MEMBER_JML_TANGGUNGAN'=> $guest->MEMBER_JML_TANGGUNGAN,
                    'MEMBER_PENDAPATAN'    => $guest->MEMBER_PENDAPATAN,
                    'MEMBER_TELP'          => $guest->MEMBER_TELP,
                    'MEMBER_NPWP'          => $guest->MEMBER_NPWP,
                    'USER_CREATE'          => 'web',
                    'MEMBER_IS_ACTIVE'=> 1,
                    'MEMBER_ACTIVE_FROM' => now(),
                    'MEMBER_ACTIVE_TO'   => now()->addYear(),
                    'DATE_CREATE'        => now()
                ]
            );

            DB::table('users')->where('member_id', $guest->MEMBER_ID)->update([
                'member_card_no' => $dataFromBackend2['member_card_no'] ?? null,
                'updated_at' => now(),
            ]);
            Log::info('Berhasil update tabel users', [
                'MEMBER_ID' => $guest->MEMBER_ID,
                'member_card_no' => $dataFromBackend2['member_card_no'] ?? null,
            ]);

            Log::info('Berhasil update/insert ke tabel user_profil', ['MEMBER_ID' => $guest->MEMBER_ID]);

            $guest->delete();
            Log::info('Guest dihapus', ['MEMBER_ID' => $guest->MEMBER_ID]);

            return response()->json([
                'success' => true,
                'message' => 'Member berhasil diaktifkan',
                'member_card_no' => $dataFromBackend2['member_card_no'] ?? null,
                'MEMBER_TYPE'    => $tipeMember,
            ]);

        } catch (\Exception $e) {
            Log::error('Error di fungsi activate: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error koneksi ke backend kedua: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                Log::warning('User belum login saat akses me');
                return response()->json([
                    'success' => false,
                    'message' => 'User belum login',
                    'data'    => null,
                ], 401);
            }

            $guest = Guest::where('MEMBER_ID', $user->member_id)->first();

            if (!$guest) {
                Log::warning('User belum isi form member', ['member_id' => $user->member_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Belum isi form member',
                    'data'    => null,
                ], 404);
            }

            Log::info('Berhasil ambil data guest untuk user', ['member_id' => $user->member_id]);

            return response()->json([
                'success' => true,
                'data'    => $guest,
            ]);
        } catch (\Exception $e) {
            Log::error('Error di fungsi me: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
            ], 500);
        }
    }
}
