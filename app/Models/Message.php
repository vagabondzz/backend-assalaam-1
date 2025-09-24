<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    // Kolom yang bisa diisi mass-assignment
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'is_from_admin',
    ];

    /**
     * Relasi ke user pengirim
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relasi ke user penerima
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Cek apakah pesan ini dari admin
     */
    public function isFromAdmin(): bool
    {
        return (bool) $this->is_from_admin;
    }

    protected $casts = [
        'is_from_admin' => 'boolean',
    ];
}
