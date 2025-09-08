<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenAICodeOrder extends Model
{
    use HasFactory;

    protected $table = 'open_a_i_code_orders';

    protected $fillable = [
        'user_id', 'chat_id', 'code', 'type', 'quantity', 'value',
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
