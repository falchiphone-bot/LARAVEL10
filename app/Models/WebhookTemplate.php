<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Api\Model\WebhooksTemplate;

class WebhookTemplate extends Model
{
    protected $table = 'WebhooksTemplate';
    public $timestamps = true;
    protected $fillable = ['name', 'language','user_updated', 'texto'];

    protected $casts = [
        'name' => 'string',
        'language' => 'string',
        'texto' => 'string'
    ];
}
