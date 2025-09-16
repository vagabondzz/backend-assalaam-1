<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardMember extends Model
{
    use HasFactory;

    protected $table = 'card_members';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'no_member',
        'tempat_lahir',
        'tanggal_lahir',
        'no_identitas',
        'jenis_kelamin',
        'alamat',
        'rt_rw',
        'kelurahan',
        'kecamatan',
        'kota',
        'kode_pos',
        'no_hp',
        'status',
        'jumlah_tanggungan',
        'pendapatan',
        'npwp',
        'kewarganegaraan',
        'agama',
        'validation',
        'member_profile',
        'active_start',
        'active_end',
        'is_active',
        'user_id',
    ];

    // Relasi ke transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'card_member_id', 'id');
    }

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Getter is_active tanpa auto-save
    public function getIsActiveAttribute($value)
    {
        if ($this->active_end && Carbon::parse($this->active_end)->lt(Carbon::today()) && $value) {
            return false;
        }

        return $value;
    }
}
