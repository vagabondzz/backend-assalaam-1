<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $admin;

    public function __construct(User $admin)
    {
        $this->admin = [
            'id'        => $admin->id,
            'name'      => $admin->name,
            'email'     => $admin->email,
            'last_seen_at' => $admin->last_seen_at,
            'is_online' => $admin->last_seen_at && $admin->last_seen_at->gt(now()->subMinutes(5)),
        ];
    }

    public function broadcastOn()
    {
        // channel publik/umum untuk semua user
        return new Channel('admins');
    }
}
