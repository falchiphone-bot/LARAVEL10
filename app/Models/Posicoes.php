<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Posicoes extends Model
{
    protected $table = 'posicoes';
    public $timestamps = false;
    protected $fillable = ['nome', 'tipo_esporte' ];

    protected $casts = [
        'nome' => 'string',
        'tipo_esporte' => 'int',
    ];
}
