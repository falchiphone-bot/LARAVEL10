<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpenAIChatType extends Model
{
    use HasFactory;

    protected $table = 'openai_chat_types';

    protected $fillable = [
        'name',
        'slug',
        'created_at',
        'updated_at',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    public function chats(): HasMany
    {
        return $this->hasMany(OpenAIChat::class, 'type_id');
    }
}
