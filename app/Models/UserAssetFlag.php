<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssetFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'code', 'no_buy',
    ];

    protected $casts = [
        'no_buy' => 'boolean',
    ];
}
