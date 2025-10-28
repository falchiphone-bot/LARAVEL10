<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentAccountCashMatch extends Model
{
    use HasFactory;

    protected $table = 'investment_account_cash_matches';

    protected $fillable = [
        'user_id',
        'buy_event_id',
        'sell_event_id',
        'qty',
    ];
}
