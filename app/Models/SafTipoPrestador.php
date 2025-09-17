<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafTipoPrestador extends Model
{
    protected $table = 'saf_tipos_prestadores';
    public $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s.u'; // datetime2(7)

    protected $fillable = [
        'nome','cidade','uf','pais','funcao_profissional_id',
    ];

    protected $casts = [
        'nome' => 'string',
        'cidade' => 'string',
        'uf' => 'string',
        'pais' => 'string',
    ];

    public function funcaoProfissional()
    {
        return $this->belongsTo(\App\Models\FuncaoProfissional::class, 'funcao_profissional_id');
    }

    public function faixasSalariais()
    {
        return $this->hasMany(\App\Models\SafFaixaSalarial::class, 'saf_tipo_prestador_id');
    }
}
