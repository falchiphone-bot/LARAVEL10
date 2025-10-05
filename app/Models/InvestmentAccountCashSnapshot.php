<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvestmentAccountCashSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','account_id','snapshot_at','available_amount','future_amount','future_date','raw_hash'
    ];
    protected $casts = [
        'snapshot_at'=>'datetime',
        'available_amount'=>'float',
        'future_amount'=>'float',
        'future_date'=>'date'
    ];
    public function account(){ return $this->belongsTo(InvestmentAccount::class,'account_id'); }
    public function user(){ return $this->belongsTo(User::class,'user_id'); }
}
