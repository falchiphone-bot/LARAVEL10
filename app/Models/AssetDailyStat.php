<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDailyStat extends Model
{
    use HasFactory;

    protected $table = 'asset_daily_stats';

    // Formato ISO 8601 com microsegundos (6 dígitos). SQL Server aceita para DATETIME2.
    protected $dateFormat = 'Y-m-d\TH:i:s.u';

    protected $fillable = [
        'symbol',
        'date',
        'mean',
        'median',
        'p5',
        'p95',
        'close_value',
        'is_accurate',
    ];

    protected $casts = [
        'date' => 'datetime',
        'mean' => 'decimal:6',
        'median' => 'decimal:6',
        'p5' => 'decimal:6',
        'p95' => 'decimal:6',
        'close_value' => 'decimal:6',
        'is_accurate' => 'boolean',
    ];
}
