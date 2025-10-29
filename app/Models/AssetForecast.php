<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'symbol', 'forecast_at',
    ];

    protected $casts = [
        'forecast_at' => 'datetime',
    ];
}
