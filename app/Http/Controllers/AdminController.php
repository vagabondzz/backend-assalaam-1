<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $admins = User::where('role', 'admin')
            ->select('id','name','email','last_seen_at')
            ->orderByDesc('last_seen_at')
            ->get()
            ->map(function ($admin) {
                return [
                    'id'           => $admin->id,
                    'name'         => $admin->name,
                    'email'        => $admin->email,
                    'last_seen_at' => $admin->last_seen_at,
                    'is_online'    => $admin->last_seen_at &&
                                      $admin->last_seen_at->gt(now()->subMinutes(5)),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $admins
        ]);
    }
}
