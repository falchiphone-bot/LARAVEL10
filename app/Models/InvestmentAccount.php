<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'total_invested',
        'account_name',
        'broker',
    ];

    protected $casts = [
        'date' => 'date',
        'total_invested' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
