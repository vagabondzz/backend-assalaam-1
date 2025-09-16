<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuestController extends Controller
{
    protected $agamaUUIDMapping = [
        1 => '88923FFE-166A-4FDE-B135-2EF51112FCB0', // Islam
        2 => '860CD318-D524-4D75-85FC-CD6E8BAE4638', // Kristen
        3 => '98130F4C-C047-4471-84B0-5B580C9161C5', // Katolik
        4 => 'EAD4927B-66D5-4F5B-8162-4E4F1C47CCB4', // Budha
        5 => '5FA1757F-8D0E-4D2B-9C87-B8BC003896C7', // Hindu
        6 => 'C2B5D790-095E-48E9-8AF7-86A275F86E2A', // Kong Hu Cu
        7 => 'EAD2F256-091F-417B-B9F6-BD4170205922', // Lain-lain
    ];

    public function store(Request $request)
    {
        $request->validate([
            'MEMBER_NAME'           => 'required|string|max:50',
            'MEMBER_DATE_OF_BIRTH'  => 'required|date',
            'MEMBER_KTP_NO'         => 'required|string|max:50|unique:guest,MEMBER_KTP_NO',
            'MEMBER_ADDRESS'        => 'required|string|max:255',
            'MEMBER_TELP'           => 'required|string|max:20',
            'REF$AGAMA_ID'          => 'required|in:1,2,3,4,5,6,7',
        
            // opsional (boleh kosong)
            'MEMBER_PLACE_OF_BIRTH' => 'nullable|string|max:50',
            'MEMBER_SEX'            => 'nullable|in:0,1',
            'MEMBER_IS_WNI'         => 'nullable|in:0,1',
            'MEMBER_RT'             => 'nullable|string|max:10',
            'MEMBER_RW'             => 'nullable|string|max:10',
            'MEMBER_KELURAHAN'      => 'nullable|string|max:50',
            'MEMBER_KECAMATAN'      => 'nullable|string|max:50',
            'MEMBER_KOTA'           => 'nullable|string|max:50',
            'MEMBER_POST_CODE'      => 'nullable|string|max:10',
            'MEMBER_JML_TANGGUNGAN' => 'nullable|integer',
            'MEMBER_PENDAPATAN'     => 'nullable|numeric',
            'MEMBER_NPWP'           => 'nullable|string|max:50',
            'MEMBER_IS_MARRIED'     => 'nullable|in:0,1',
        ]);
        

        // Konversi angka ke UUID
        $agamaId   = (int) $request->input('REF$AGAMA_ID');
        $agamaUUID = $this->agamaUUIDMapping[$agamaId] ?? null;

        if (!$agamaUUID) {
            return response()->json([
                'success' => false,
                'message' => 'Agama tidak valid',
            ], 422);
        }

        $guest = Guest::create([
            'MEMBER_NAME'           => $request->MEMBER_NAME,
            'MEMBER_PLACE_OF_BIRTH' => $request->MEMBER_PLACE_OF_BIRTH,
            'MEMBER_DATE_OF_BIRTH'  => $request->MEMBER_DATE_OF_BIRTH,
            'MEMBER_KTP_NO'         => $request->MEMBER_KTP_NO,
            'MEMBER_SEX'            => $request->MEMBER_SEX,
            'MEMBER_ADDRESS'        => $request->MEMBER_ADDRESS,
            'MEMBER_KELURAHAN'      => $request->MEMBER_KELURAHAN,
            'MEMBER_POST_CODE'      => $request->MEMBER_POST_CODE,
            'MEMBER_KECAMATAN'      => $request->MEMBER_KECAMATAN,
            'MEMBER_KOTA'           => $request->MEMBER_KOTA,
            'MEMBER_RT'             => $request->MEMBER_RT,
            'MEMBER_RW'             => $request->MEMBER_RW,
            'MEMBER_TELP'           => $request->MEMBER_TELP,
            'MEMBER_JML_TANGGUNGAN' => $request->MEMBER_JML_TANGGUNGAN,
            'MEMBER_PENDAPATAN'     => $request->MEMBER_PENDAPATAN,
            'MEMBER_NPWP'           => $request->MEMBER_NPWP,
            'MEMBER_IS_MARRIED'     => $request->MEMBER_IS_MARRIED,
            'MEMBER_IS_WNI'         => $request->MEMBER_IS_WNI,
            'REF$AGAMA_ID'          => $agamaUUID,
            'DATE_CREATE'           => now(),
            'MEMBER_IS_ACTIVE'      => 0,
            'MEMBER_ACTIVE_FROM'    => null,
            'MEMBER_ACTIVE_TO'      => null,
        ]);

        $user = Auth::user();
        $user->member_id = $guest->MEMBER_ID;

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('profile', 'public');
            $user->profile_photo = $path;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Membership berhasil dibuat, tunggu validasi admin.',
            'data'    => $guest
        ], 201);
    }

    public function waitingApproval()
    {
        $calonMembers = Guest::where('MEMBER_IS_ACTIVE', 0)
            ->select(
                'MEMBER_ID',
                'MEMBER_NAME',
                'MEMBER_PLACE_OF_BIRTH',
                'MEMBER_DATE_OF_BIRTH',
                'MEMBER_KTP_NO',
                'MEMBER_SEX',
                'MEMBER_ADDRESS',
                'MEMBER_IS_MARRIED',
                'MEMBER_IS_WNI',
                'REF$AGAMA_ID',
                'DATE_CREATE'
            )
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar calon member yang menunggu validasi',
            'data'    => $calonMembers
        ]);
    }
}
