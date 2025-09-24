<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminChatController extends Controller
{
    /**
     * Admin kirim pesan ke user tertentu
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'user_id' => 'required|exists:users,id',
        ]);

        $admin = Auth::user();

        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Hanya admin yang bisa mengirim pesan'], 403);
        }

        $user = User::findOrFail($request->user_id);

        if ($user->role !== 'user') {
            return response()->json(['error' => 'Pesan hanya bisa dikirim ke user'], 422);
        }

        // Simpan pesan (admin → user), unread untuk user
        $message = Message::create([
            'sender_id'     => $admin->id,
            'receiver_id'   => $user->id,
            'message'       => $request->message,
            'is_from_admin' => true,
            'is_read'       => false, // unread
        ]);

        // Broadcast ke channel user & global admin
        broadcast(new MessageSent($message, "chat.{$user->id}"))->toOthers();
        broadcast(new MessageSent($message, 'admin-global'))->toOthers();

        return response()->json($message);
    }

    /**
     * Ambil semua pesan antara admin dan user tertentu
     * sekaligus tandai pesan user → admin sebagai read
     */
    public function getMessages($userId)
    {
        $adminId = Auth::id();

        $messages = Message::where(function ($q) use ($adminId, $userId) {
                $q->where('sender_id', $userId)
                  ->where('receiver_id', $adminId);
            })
            ->orWhere(function ($q) use ($adminId, $userId) {
                $q->where('sender_id', $adminId)
                  ->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Tandai semua pesan dari user → admin sebagai read
        Message::where('sender_id', $userId)
               ->where('receiver_id', $adminId)
               ->update(['is_read' => true]);

        return response()->json($messages);
    }

    /**
     * Daftar semua user yang pernah chat dengan admin + unread count
     */
    public function getUsers()
    {
        $adminId = Auth::id();

        // Ambil semua user_id lawan bicara
        $userIds = Message::where('sender_id', $adminId)
                        ->pluck('receiver_id')
                        ->merge(
                            Message::where('receiver_id', $adminId)
                                   ->pluck('sender_id')
                        )
                        ->unique()
                        ->reject(fn($id) => $id == $adminId)
                        ->values();

        $users = User::whereIn('id', $userIds)
                    ->where('role', 'user')
                    ->select('id', 'name', 'last_seen_at')
                    ->get()
                    ->map(function($user) use ($adminId) {
                        // Hitung unread
                        $user->unreadCount = Message::where('sender_id', $user->id)
                                                    ->where('receiver_id', $adminId)
                                                    ->where('is_read', false)
                                                    ->count();
                        return $user;
                    });

        return response()->json(['data' => $users]);
    }
}
