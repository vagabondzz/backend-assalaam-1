<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserChatController extends Controller
{
    /**
     * Kirim pesan dari user ke admin
     */
    public function sendMessage(Request $request)
    {
        $request->validate(['message' => 'required|string|max:1000']);
    
        $user = Auth::user();
    
        // cari admin pertama (atau semua admin jika multi-admin)
        $admin = User::where('role', 'admin')->firstOrFail();
    
        $message = Message::create([
            'receiver_id'   => $admin->id,  // penerima = admin
            'sender_id'     => $user->id,   // pengirim = user
            'message'       => $request->message,
            'is_from_admin' => false,       // karena user yg kirim
        ]);
    
        broadcast(new MessageSent($message, 'user-global'))->toOthers();
    
        return response()->json($message);
    }
    
    /**
     * Ambil semua pesan antara user login dan admin
     */
    public function getMessages()
    {
        $userId  = Auth::id();
    
        // dukung multi-admin
        $adminIds = User::where('role', 'admin')->pluck('id');
    
        $messages = Message::where(function($q) use ($userId, $adminIds) {
                                // Pesan dari user ke admin
                                $q->where('sender_id', $userId)
                                  ->whereIn('receiver_id', $adminIds);
                            })
                            ->orWhere(function($q) use ($userId, $adminIds) {
                                // Pesan dari admin ke user
                                $q->whereIn('sender_id', $adminIds)
                                  ->where('receiver_id', $userId);
                            })
                            ->orderBy('created_at', 'asc')
                            ->get();
    
        return response()->json($messages);
    }
    

    /**
     * Ambil semua pesan yang dikirim admin ke user login
     */
    public function getAdminMessages()
    {
        $userId  = Auth::id();
        $adminId = User::where('role', 'admin')->value('id');

        $messages = Message::where('receiver_id', $userId)
                            ->where('sender_id', $adminId)
                            ->orderBy('created_at', 'asc')
                            ->get();

        return response()->json($messages);
    }
}
