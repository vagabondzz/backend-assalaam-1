<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

class VerifyEmailController extends Controller
{
    public function __invoke($id, $hash)
    {
        // Ambil user dari database
        $user = User::findOrFail($id);

        // Cek hash untuk keamanan
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect('http://localhost:8080/login')
                ->with('error', 'Tautan verifikasi tidak valid.');
        }

        // Tandai email sebagai terverifikasi jika belum
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Redirect ke frontend login dengan query param
        return redirect('http://localhost:8080/login?verified=1');
    }

    // Resend tetap pakai auth middleware
    public function resend(\Illuminate\Http\Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah diverifikasi']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Tautan verifikasi baru telah dikirim']);
    }
}
