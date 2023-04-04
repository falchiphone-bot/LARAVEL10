<?php

namespace App\Models\Atletas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobrancaSicredi extends Model
{
    protected $connection = 'atletas';
    protected $table = 'CobrancaSicredi';
    protected $primaryKey = "NossoNumero";

    public $timestamps = false;

    protected $fillable = [
        'NossoNumero',
        'Carteira',
        'NumeroDocumento',
        'Pagador',
        'DataEmissao',
        'DataVencimento',
        'Valor',
        'Liquidacao',
        'DataLiquidacao',
        'SituacaoTitulo',
        'Motivo',
        'Associado',
        'Conta',
        'Beneficiario',
        'Cobrando',
        'CobrandoEm',
        'PrevisaoPgto',
        'MovimentoPorUser',
        'MovimentoEm',
        'Atualizado',
        'QuitadoIXC',
        'status_internet',
        'BaixarBanco',
    ];
}
