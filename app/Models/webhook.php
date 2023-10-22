<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $table = 'webhooks'; // Nome da tabela no banco de dados
    public $timestamps = true;

    protected $fillable = [
        'type',
        'webhook',
        'user_updated',
        'contactName',
        'waId',
        'body',
        'mime_type',
        'filename',
        'image_mime_type',
        'caption',
        'status',
        'recipient_id',
        'conversation_id',
        'messagesType',
        'messagesFrom',
        'messagesTimestamp',
        'messages_ButtonPayload',
        'messages_ButtonText',
        'field',
        'event',
        'message_template_id',
        'message_template_name',
        'message_template_language',
        'reason',
        'messages_id',
    ];

    // Defina aqui quaisquer relações com outros modelos, se necessário.
}
