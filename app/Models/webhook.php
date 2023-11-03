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

        'image_id',
        'image_sha256',
        'image_caption',
        'image_mime_type',

        'document_filename',
        'document_id',
        'document_sha256',
        'document_mime_type',

        'video_filename',
        'video_caption',
        'video_id',
        'video_sha256',
        'video_mime_type',

        'sticker_id',
        'sticker_sha256',
        'sticker_mime_type',
        'sticker_animated',

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
        'changes_value_ban_info_waba_ban_state',
        'changes_value_ban_info_waba_ban_date',
        'url_arquivo',
    ];

}
