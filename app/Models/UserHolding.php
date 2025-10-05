<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserHolding extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'account_id', 'code', 'quantity', 'avg_price', 'invested_value', 'current_price', 'currency'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'quantity' => 'float',
        'avg_price' => 'float',
        'invested_value' => 'float',
        'current_price' => 'float',
    ];

    public function account()
    {
        return $this->belongsTo(InvestmentAccount::class, 'account_id');
    }
}
