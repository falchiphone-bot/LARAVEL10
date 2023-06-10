<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Posicoes extends Model
{
    protected $table = 'posicoes';
    public $timestamps = true;
    protected $fillable = ['nome', 'tipo_esporte' ];

    protected $casts = [
        'nome' => 'string',
        'tipo_esporte' => 'int',
    ];


    public function MostraNome(): HasOne
    {
        return $this->hasOne(TipoEsporte::class, 'id', 'tipo_esporte');
    }
}
