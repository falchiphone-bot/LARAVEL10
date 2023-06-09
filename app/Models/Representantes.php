<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Representantes extends Model
{
    protected $table = 'representantes';
    public $timestamps = false;
    protected $fillable = ['nome', 'cpf', 'email', 'telefone'];

    protected $casts = [
        'nome' => 'string',
        'cpf' => 'string',
        'cnpj' => 'string',
        'email' => 'string',
        'telefone' => 'string'
    ];
}
