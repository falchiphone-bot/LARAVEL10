<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class WebhookTemplate extends Model
{
    protected $table = 'WebhooksTemplate';
    public $timestamps = true;
    protected $fillable = ['name', 'language','user_updated'];

    protected $casts = [
        'name' => 'string',
        'language' => 'string'
    ];
}
