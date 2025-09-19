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
        'pix_nome',
    'forma_pagamento_nome',
        'documento',
    'cpf',
        'email',
        'telefone',
        'cidade',
        'uf',
        'pais',
    'valor_salario',
        'dia_pagamento',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'nome' => 'string',
        'documento' => 'string',
    'cpf' => 'string',
        'pix_nome' => 'string',
    'forma_pagamento_nome' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'cidade' => 'string',
        'uf' => 'string',
        'pais' => 'string',
        'valor_salario' => 'decimal:2',
        'dia_pagamento' => 'integer',
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

    public function pix()
    {
        return $this->belongsTo(\App\Models\Pix::class, 'pix_nome', 'nome');
    }

    public function formaPagamento()
    {
        return $this->belongsTo(\App\Models\FormaPagamento::class, 'forma_pagamento_nome', 'nome');
    }
}
