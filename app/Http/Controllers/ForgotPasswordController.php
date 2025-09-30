<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Carbon\Carbon;
use App\Models\User;
use App\Mail\ResetPasswordMail;

class ForgotPasswordController extends Controller
{
    public function resetByMember(Request $request)
    {
        $data = $request->validate([
            'email'     => ['required', 'email'],
            'member_id' => ['required', 'string'],
        ]);

        Log::info('Mulai proses reset password', ['request' => $data]);

        try {
            // Pakai model User (Eloquent)
            $user = User::where('email', $data['email'])
                ->where('member_card_no', $data['member_id'])
                ->first();

            if (!$user) {
                Log::warning('User tidak ditemukan', [
                    'email' => $data['email'], 
                    'member_id' => $data['member_id']
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau nomor member tidak ditemukan.'
                ], 404);
            }

            // generate password default baru
            $defaultPasswordPlain = Str::random(10);

            // update password dengan Eloquent
            $user->update([
                'password'   => Hash::make($defaultPasswordPlain),
                'updated_at' => Carbon::now(),
            ]);

            Log::info('Password berhasil diupdate', ['user_id' => $user->id]);

            // kirim email reset password
            Mail::to($user->email)->send(
                new ResetPasswordMail($user->email, $data['member_id'], $defaultPasswordPlain)
            );

            Log::info('Email reset password terkirim', ['email' => $user->email]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset. Silakan cek email Anda.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat reset password', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses permintaan.'
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'], 
            // pastikan new_password_confirmation ikut dikirim dari frontend
        ]);

        $user = Auth::user(); // ambil user yang sedang login

        // cek password lama
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai.'
            ], 400);
        }

        // update password baru pakai Eloquent
        $user->update([
            'password' => Hash::make($request->new_password),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.'
        ]);
    }
}
