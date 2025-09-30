<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    
    protected $table = 'promo';

 
    public $timestamps = false;

  
    protected $fillable = [
        'path', 
        'created_at',
    ];

    
    protected $attributes = [
        'created_at' => null,
    ];

    
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
