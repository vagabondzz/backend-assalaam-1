<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property CardMember|null $cardMember
 */
class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'member_id',
        'role',
        'profil_photo',
        'member_card_no',
        'api_token',
        'member_id',
        'last_seen_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * JWT Identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT Custom Claims
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relasi ke CardMember
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cardMember()
    {
        return $this->hasOne(CardMember::class, 'user_id', 'id');
    }

    public function guest() {
        return $this->hasOne(Guest::class, 'MEMBER_ID', 'member_id');
    }

    public function profil()
{
    return $this->hasOne(UserProfil::class, 'MEMBER_ID', 'member_id');
}

protected $casts = [
    'last_seen_at' => 'datetime',
];


}
