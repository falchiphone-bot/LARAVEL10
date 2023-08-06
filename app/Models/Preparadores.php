<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Preparadores extends Model
{
    protected $table = 'Preparadores';
    public $timestamps = true;
    protected $fillable = ['nome', 'email', 'telefone','licencaCBF'];

    protected $casts = [
        'nome' => 'string',
    ];
}
