<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenAICodeOrder extends Model
{
    use HasFactory;

    protected $table = 'open_a_i_code_orders';

    // SQL Server: evitar ambiguidade de DATEFORMAT usando ISO 8601 com 'T' literal
    // e sem frações de segundo. (ex.: 2025-09-15T22:23:17)
    protected $dateFormat = 'Y-m-d\\TH:i:s';

    protected $fillable = [
        'user_id', 'chat_id', 'code', 'type', 'quantity', 'value',
        'quote_value', 'quote_updated_at',
    ];

    protected $casts = [
        'quote_updated_at' => 'datetime:Y-m-d\TH:i:s',
    ];

    public function chat()
    {
        return $this->belongsTo(OpenAIChat::class, 'chat_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
