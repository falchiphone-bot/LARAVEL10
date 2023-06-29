<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoArquivo extends Model
{
    protected $table = 'TipoArquivo';
    public $timestamps = true;
    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => 'string',
    ];
}
