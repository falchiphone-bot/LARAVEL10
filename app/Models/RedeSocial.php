<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RedeSocial extends Model
{
    protected $table = 'RedeSocial';
    public $timestamps = true;
    protected $fillable = ['nome', 'url', 'user_created','user_updated'];

    protected $casts = [
        'nome' => 'string',
    ];
}
