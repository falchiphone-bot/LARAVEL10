<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpenAIChat extends Model
{
    use HasFactory;

    protected $table = 'open_a_i_chats';

    protected $fillable = [
        'user_id',
        'title',
        'messages',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'messages' => 'array',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(OpenAIChatAttachment::class, 'chat_id');
    }
}
