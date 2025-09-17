<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafFaixaSalarial extends Model
{
    protected $table = 'saf_faixas_salariais';
    public $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'nome',
        'funcao_profissional_id',
        'saf_tipo_prestador_id',
        'senioridade',
        'tipo_contrato',
        'periodicidade',
        'valor_minimo',
        'valor_maximo',
        'moeda',
        'vigencia_inicio',
        'vigencia_fim',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'nome' => 'string',
        'senioridade' => 'string',
        'tipo_contrato' => 'string',
        'periodicidade' => 'string',
        'valor_minimo' => 'decimal:4',
        'valor_maximo' => 'decimal:4',
        'moeda' => 'string',
        'vigencia_inicio' => 'datetime',
        'vigencia_fim' => 'datetime',
        'ativo' => 'boolean',
    ];

    public function funcaoProfissional()
    {
        return $this->belongsTo(\App\Models\FuncaoProfissional::class, 'funcao_profissional_id');
    }

    public function tipoPrestador()
    {
        return $this->belongsTo(\App\Models\SafTipoPrestador::class, 'saf_tipo_prestador_id');
    }
}
