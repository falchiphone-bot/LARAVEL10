<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Irmaos_EmausServicos extends Model
{
    protected $table = 'Irmaos_EmausServicos';
    public $timestamps = true;
    protected $fillable = ['nomeServico', 'user_created', 'user_updated'];

    protected $casts = [
        'nomeServico' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];
}
