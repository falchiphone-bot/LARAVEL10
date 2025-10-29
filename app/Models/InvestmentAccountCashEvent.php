<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvestmentAccountCashEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id','account_id','event_date','settlement_date','category','title','detail','amount','target_amount','target_probability_pct','currency','status','source','group_hash','forecast_at'
    ];
    protected $casts = [
        'event_date'=>'date',
        'settlement_date'=>'date',
        'amount'=>'float',
        'target_amount'=>'float',
        'target_probability_pct'=>'float',
        'forecast_at' => 'datetime'
    ];
    public function account(){ return $this->belongsTo(InvestmentAccount::class,'account_id'); }
    public function user(){ return $this->belongsTo(User::class,'user_id'); }
}
