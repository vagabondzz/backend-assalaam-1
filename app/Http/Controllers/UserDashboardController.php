<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserDashboardController extends Controller
{
    /**
     * Ambil seluruh data dashboard user/member
     */
    public function getDashboard()
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error'   => true,
                'message' => 'User tidak terautentikasi',
            ], 401);
        }

        // cek profil
        $profil = $user->profil;

        if (!$profil) {
            return response()->json([
                'member' => [
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'no_member' => '-',
                    'status'    => 'Belum menjadi member',
                    'is_active' => false,
                ],
                'transactions'    => [],
                'total_poin'      => 0,
                'total_transaksi' => 0,
                'total_kupon'     => 0,
                'monthly_chart'   => array_fill(0, 12, 0),
                'message'         => 'Data user_profil tidak ditemukan',
            ]);
        }

        // ambil transaksi dari backend 2
        $transactionsRaw = collect();
        try {
            $token = JWTAuth::getToken();
            $response = Http::withToken($token)
                            ->get("http://127.0.0.1:8002/api/member/{$profil->MEMBER_ID}/transactions");

            if ($response->ok()) {
                $transactionsRaw = collect($response->json());
            } else {
                Log::warning('Gagal ambil transaksi dari backend 2', [
                    'user_id' => $user->id,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error saat request transaksi ke backend 2', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }

        // Mapping transaksi
        $transactions = $transactionsRaw->map(function ($trx) {
            return [
                'id'     => $trx['TRANS_NO'] ?? null,
                'date'   => isset($trx['TRANS_DATE']) ? Carbon::parse($trx['TRANS_DATE'])->format('d-m-Y') : null,
                'amount' => $trx['TRANS_TOTAL_TRANSACTION'] ?? $trx['TRANS_TOTAL_BAYAR'] ?? 0,
                'point'  => $trx['trans_poin_member'] ?? 0,
                'coupon' => $trx['TRANS_KUPON_UNDIAN'] ?? 0, // <-- ambil kupon undian
            ];
        });

        $totalPoints       = $transactions->sum('point');
        $totalTransactions = $transactions->count();
        $totalCoupons      = $transactions->sum('coupon'); // <-- total kupon dari transaksi

        $monthlyChart = array_fill(0, 12, 0);
        foreach ($transactions as $trx) {
            if (!empty($trx['date'])) {
                $month = Carbon::parse($trx['date'])->month - 1;
                $monthlyChart[$month] += $trx['point'] ?? 0;
            }
        }

        $status = !empty($profil->MEMBER_IS_ACTIVE) && $profil->MEMBER_IS_ACTIVE == 1
            ? 'Aktif'
            : 'Pending';

        return response()->json([
            'member' => [
                'name'        => $profil->MEMBER_NAME ?? $user->name,
                'email'       => $user->email,
                'no_member'   => $profil->MEMBER_CARD_NO ?? '-',
                'status'      => $status,
                'is_active'   => (bool) $profil->MEMBER_IS_ACTIVE,
                'active_start'=> $profil->MEMBER_ACTIVE_FROM,
                'active_end'  => $profil->MEMBER_ACTIVE_TO,
                'total_poin'  => $profil->MEMBER_POIN ?? $totalPoints,
                'total_kupon' => $totalCoupons,
            ],
            'transactions'     => $transactions,
            'total_poin'       => $totalPoints,
            'total_transaksi'  => $totalTransactions,
            'total_kupon'      => $totalCoupons,
            'monthly_chart'    => $monthlyChart,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error'   => true,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    
    public function getAllTransactions()
    {
        try {
            $user = Auth::user();
    
            if (!$user) {
                return response()->json([
                    'error' => true,
                    'message' => 'User tidak terautentikasi',
                ], 401);
            }
    
            if (!$user->member_id || !$user->member) {
                return response()->json([
                    'transactions' => [],
                    'total_kupon'  => 0,
                    'message' => 'User belum menjadi member',
                ], 200);
            }
    
            $member = $user->member;
    
            // Ambil semua transaksi milik member
            $transactionsRaw = $member->transaksi()
                ->orderBy('TRANS_DATE', 'desc')
                ->get();
    
            $transactions = $transactionsRaw->map(function ($trx) {
                return [
                    'id'     => $trx->TRANS_NO,
                    'date'   => $trx->TRANS_DATE ? Carbon::parse($trx->TRANS_DATE)->format('d-m-Y') : null,
                    'amount' => $trx->TRANS_TOTAL_TRANSACTION ?? $trx->TRANS_TOTAL_BAYAR ?? 0,
                    'point'  => $trx->trans_poin_member ?? 0,
                    'coupon' => $trx->TRANS_KUPON_UNDIAN ?? 0, // <-- ambil dari transaksi
                ];
            });
    
            // Total kupon dihitung dari semua transaksi
            $totalCoupons = $transactions->sum('coupon');
    
            return response()->json([
                'transactions'     => $transactions,
                'total_transaksi'  => $transactions->count(),
                'total_poin'       => $transactions->sum('point'),
                'total_kupon'      => $totalCoupons,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    
}
