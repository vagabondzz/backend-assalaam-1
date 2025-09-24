<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $channelName;

    /**
     * @param Message $message
     * @param string|null $channelName Optional custom channel (misal: 'admin-global')
     */
    public function __construct(Message $message, $channelName = null)
    {
        $this->message = $message;

        // Pakai sender_id sebagai default channel jika user_id tidak ada
        $userId = $message->sender_id ?? $message->user_id ?? null;

        // Tentukan channel broadcast
        $this->channelName = $channelName ?? 'chat.' . $userId;
    }

    /**
     * Tentukan channel broadcast
     */
    public function broadcastOn()
    {
        return new PrivateChannel($this->channelName);
    }

    /**
     * Payload broadcast
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id, // pastikan ada sender_id
            'receiver_id' => $this->message->receiver_id,
            'message' => $this->message->message,
            'is_from_admin' => $this->message->is_from_admin,
            'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }
}
