<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoEsporte extends Model
{
    protected $table = 'TipoEsporte';
    public $timestamps = true;
    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => 'string',
    ];
}
