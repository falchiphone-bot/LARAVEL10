<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenAIChatAttachment extends Model
{
    use HasFactory;

    protected $table = 'openai_chat_attachments';

    protected $fillable = [
        'chat_id',
        'user_id',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'message_index',
        'created_at',
        'updated_at',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    public function chat(): BelongsTo
    {
        return $this->belongsTo(OpenAIChat::class, 'chat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
