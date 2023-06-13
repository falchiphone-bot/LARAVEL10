<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoRepresentante extends Model
{
    protected $table = 'TipoRepresentante';
    public $timestamps = true;
    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => 'string',
        'tipo_representante' => 'int',
    ];
}
