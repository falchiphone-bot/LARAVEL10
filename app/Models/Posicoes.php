<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Posicoes extends Model
{
    protected $table = 'posicoes';
    public $timestamps = false;
    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => 'string',
    ];
}
