<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    /**
     * Tampilkan daftar transaksi dengan info member (pagination & search)
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $search = $request->input('search', '');

        // Query dengan eager loading member
        $query = Transaksi::with('member')->orderBy('TRANS_DATE', 'desc');

        // Filter berdasarkan nama atau nomor card member
        if ($search) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('MEMBER_NAME', 'like', "%$search%")
                    ->orWhere('MEMBER_CARD_NO', 'like', "%$search%");
            });
        }

        // Pagination
        $transaksi = $query->paginate($perPage);

        // Transform data untuk response
        $transaksi->getCollection()->transform(function ($t) {
            return [
                'trans_no' => $t->TRANS_NO,
                'trans_date' => $t->TRANS_DATE,
                'member_id' => $t->MEMBER_ID,
                'member_name' => $t->member?->MEMBER_NAME ?? '-',
                'member_card_no' => $t->member?->MEMBER_CARD_NO ?? '-',
                'trans_total' => $t->TRANS_TOTAL_TRANSACTION,
                'trans_poin' => $t->trans_poin_member ?? 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $transaksi->items(),
                'current_page' => $transaksi->currentPage(),
                'last_page' => $transaksi->lastPage(),
                'per_page' => $transaksi->perPage(),
                'total' => $transaksi->total(),
            ],
        ]);
    }

    /**
     * Tampilkan detail transaksi berdasarkan nomor transaksi
     */
    public function show($transNo)
    {
        $transaksi = Transaksi::with('member')->where('TRANS_NO', $transNo)->first();

        if (! $transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'trans_no' => $transaksi->TRANS_NO,
                'trans_date' => $transaksi->TRANS_DATE,
                'member_id' => $transaksi->MEMBER_ID,
                'member_name' => $transaksi->member?->MEMBER_NAME ?? '-',
                'member_card_no' => $transaksi->member?->MEMBER_CARD_NO ?? '-',
                'trans_total' => $transaksi->TRANS_TOTAL_TRANSACTION,
                'trans_poin' => $transaksi->trans_poin_member ?? 0,
            ],
        ]);
    }
}
