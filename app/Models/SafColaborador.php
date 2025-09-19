<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafColaborador extends Model
{
    protected $table = 'saf_colaboradores';
    public $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'nome',
        'representante_id',
        'funcao_profissional_id',
        'saf_tipo_prestador_id',
        'saf_faixa_salarial_id',
        'documento',
    'cpf',
        'email',
        'telefone',
        'cidade',
        'uf',
        'pais',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'nome' => 'string',
        'documento' => 'string',
    'cpf' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'cidade' => 'string',
        'uf' => 'string',
        'pais' => 'string',
        'ativo' => 'boolean',
    ];

    public function representante()
    {
        return $this->belongsTo(\App\Models\Representantes::class, 'representante_id');
    }

    public function funcaoProfissional()
    {
        return $this->belongsTo(\App\Models\FuncaoProfissional::class, 'funcao_profissional_id');
    }

    public function tipoPrestador()
    {
        return $this->belongsTo(\App\Models\SafTipoPrestador::class, 'saf_tipo_prestador_id');
    }

    public function faixaSalarial()
    {
        return $this->belongsTo(\App\Models\SafFaixaSalarial::class, 'saf_faixa_salarial_id');
    }
}
