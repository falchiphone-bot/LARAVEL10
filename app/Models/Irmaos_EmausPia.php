<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Irmaos_EmausPia extends Model
{
    protected $table = 'Irmaos_EmausPia';
    public $timestamps = true;
    protected $fillable = ['empresa','nomePia', 'user_created', 'user_updated'];

    protected $casts = [
        'empresa' => 'string',
        'nomePia' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];
}
