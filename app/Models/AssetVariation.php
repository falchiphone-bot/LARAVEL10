<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetVariation extends Model
{
    protected $fillable = [
        'asset_id',
        'asset_code',
        'chat_id',
        'month',
        'year',
        'variation',
    ];
}
