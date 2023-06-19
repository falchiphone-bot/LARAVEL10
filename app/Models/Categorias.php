<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Categorias extends Model
{
    protected $table = 'categorias';
    public $timestamps = true;
    protected $fillable = ['nome', 'tipo_esporte','anobase', 'user_created', 'user_updated' ];

    protected $casts = [
        'nome' => 'string',
        'anobase' => 'int',
        'tipo_esporte' => 'int',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];


    public function MostraCategoria(): HasOne
    {
        return $this->hasOne(TipoEsporte::class, 'id', 'tipo_esporte');
    }
}
