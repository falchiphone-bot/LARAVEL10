<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenAIChatRecord extends Model
{
    use HasFactory;

    // Corrige nome da tabela (Laravel inferiria open_a_i_chat_records)
    protected $table = 'openai_chat_records';

    protected $fillable = [
        'chat_id',
        'user_id',
        'occurred_at',
        'amount',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(OpenAIChat::class, 'chat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
