<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenAIChatAttachment extends Model
{
    use HasFactory;

    protected $table = 'openai_chat_attachments';

    // Deixe o SQL Server preencher created_at/updated_at via default (useCurrent/GETDATE())
    public $timestamps = false;

    protected $fillable = [
        'chat_id',
        'user_id',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'message_index',
    ];

    // Opcional: casts explícitos
    protected $casts = [
        'chat_id' => 'integer',
        'user_id' => 'integer',
        'size' => 'integer',
        'message_index' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(OpenAIChat::class, 'chat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
