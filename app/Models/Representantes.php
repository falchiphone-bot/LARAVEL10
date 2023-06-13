<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Representantes extends Model
{
    protected $table = 'representantes';
    public $timestamps = true;
    protected $fillable = ['nome', 'cpf', 'cnpj','email', 'telefone','tipo_representante'];

    protected $casts = [
        'nome' => 'string',
        'cpf' => 'string',
        'cnpj' => 'string',
        'email' => 'string',
        'telefone' => 'string'
    ];
}
