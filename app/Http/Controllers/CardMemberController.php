<?php

namespace App\Http\Controllers;

use App\Models\CardMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CardMemberController extends Controller
{
    /**
     * Menampilkan formulir member
     */
    public function formulirMember()
    {
        return view('auth.form');
    }

    /**
     * Membuat atau memperbarui data member
     */
    public function registerOrUpdate(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama' => 'required|string|max:50',
            'no_member' => 'nullable|string|unique:card_members,no_member,'.($user->cardMember->id ?? 'NULL'),
            'tempat_lahir' => 'required|string|max:50',
            'tanggal_lahir' => 'required|date',
            'no_identitas' => 'nullable|string|unique:card_members,no_identitas,'.($user->cardMember->id ?? 'NULL'),
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'required|string|max:250',
            'rt_rw' => 'required|string|max:20',
            'kelurahan' => 'required|string|max:50',
            'kecamatan' => 'required|string|max:50',
            'kota' => 'required|string|max:50',
            'kode_pos' => 'required|string|max:10',
            'no_hp' => 'required|string|max:20',
            'status' => 'required|string|max:20',
            'jumlah_tanggungan' => 'required|numeric',
            'pendapatan' => 'required|numeric',
            'npwp' => 'nullable|string|max:50',
            'kewarganegaraan' => 'nullable|string|max:50',
            'agama' => 'nullable|string|max:20',
            'file' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle upload file
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->storeAs('member-profile', $filename, 'public');

            // Hapus file lama jika ada
            if ($user->cardMember && $user->cardMember->member_profile) {
                Storage::disk('public')->delete('member-profile/'.$user->cardMember->member_profile);
            }

            $validated['member_profile'] = $filename;
        }

        $validated['validation'] = false; // default user biasa tidak bisa validasi

        // Buat atau update member
        if ($user->cardMember) {
            $user->cardMember->update($validated);
            $member = $user->cardMember;
            $message = 'Profil member berhasil diperbarui';
        } else {
            $validated['user_id'] = $user->id;
            $member = CardMember::create($validated);
            $message = 'Profil member berhasil dibuat';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $member,
        ]);
    }

    /**
     * Update status member (Admin)
     */
    public function updateMemberStatus(Request $request)
    {
        $card_member = CardMember::find($request->id_member);

        if (! $card_member) {
            return redirect()->back()->with('error', 'Member tidak ditemukan');
        }

        if ($card_member->is_active && $card_member->active_end && $card_member->active_end > Carbon::today()) {
            return redirect()->back()->with('error', 'Member sudah aktif');
        }

        $card_member->update([
            'active_start' => Carbon::today()->format('Y-m-d'),
            'active_end' => Carbon::today()->addMonth(3)->format('Y-m-d'),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Status member diperbarui');
    }

    /**
     * Hapus member
     */
    public function delete(CardMember $card_member)
    {
        // Hapus file member jika ada
        if ($card_member->member_profile) {
            Storage::disk('public')->delete('member-profile/'.$card_member->member_profile);
        }

        $card_member->delete();

        return redirect()->back()->with('success', 'Data member berhasil dihapus');
    }

    /**
     * Tabel member
     */
    public function showMembers()
    {
        $query = CardMember::query();

        if ($search = request()->input('search')) {
            $query->where('nama', 'like', "%$search%")
                ->orWhere('no_member', 'like', "%$search%");
        }

        $members = $query->latest()->paginate(10);

        return view('auth.table', compact('members'));
    }

    /**
     * Menampilkan profile member untuk user
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan atau token tidak valid.',
            ], 401);
        }

        $member = CardMember::where('user_id', $user->id)->first();

        if (! $member) {
            return response()->json([
                'success' => true,
                'exists' => false,
                'data' => [
                    'nama' => $user->name,
                    'email' => $user->email,
                    'status' => 'Belum menjadi member',
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => true,
            'data' => [
                'nama' => $member->nama,
                'no_member' => $member->no_member,
                'tempat_lahir' => $member->tempat_lahir,
                'tanggal_lahir' => $member->tanggal_lahir,
                'no_identitas' => $member->no_identitas,
                'jenis_kelamin' => $member->jenis_kelamin,
                'alamat' => $member->alamat,
                'rt_rw' => $member->rt_rw,
                'kelurahan' => $member->kelurahan,
                'kecamatan' => $member->kecamatan,
                'kota' => $member->kota,
                'kode_pos' => $member->kode_pos,
                'no_hp' => $member->no_hp,
                'status' => $member->status,
                'jumlah_tanggungan' => $member->jumlah_tanggungan,
                'pendapatan' => $member->pendapatan,
                'npwp' => $member->npwp,
                'kewarganegaraan' => $member->kewarganegaraan,
                'agama' => $member->agama,
                'member_profile' => $member->member_profile,
            ],
        ]);
    }
}
