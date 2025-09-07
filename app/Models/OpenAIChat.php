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
    'code',
        'messages',
    'target_min',
    'target_avg',
    'target_max',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'messages' => 'array',
    'target_min' => 'decimal:2',
    'target_avg' => 'decimal:2',
    'target_max' => 'decimal:2',
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

    public function type(): BelongsTo
    {
        return $this->belongsTo(OpenAIChatType::class, 'type_id');
    }

    public function records(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OpenAIChatRecord::class, 'chat_id');
    }
}
