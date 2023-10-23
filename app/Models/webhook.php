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
        'entry_id',
        'entry_time',
        'object',
        'value_messaging_product',
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
        'context_From',
        'context_Id',
        'messages_ButtonPayload',
        'messages_ButtonText',
        'changes_field',
        'event',
        'message_template_id',
        'message_template_name',
        'message_template_language',
        'reason',
        'messages_id',
        'changes_metadata_value_display_phone_number',
        'changes_metadata_value_phone_number_id',
    ];

}
