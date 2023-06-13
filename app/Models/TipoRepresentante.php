<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoRepresentante extends Model
{
    protected $table = 'tiporepresentante';
    public $timestamps = true;
    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => 'string',

    ];
}
