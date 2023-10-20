<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class webhook extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
    'webhook',
    'type',
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
    'messages_id',
   ];

    protected $tableName = 'webhooks';

}
