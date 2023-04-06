<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogLiquidacaoDiario extends Model
{
    protected $table = 'Contabilidade.LogsConsultaSicred';

    public $fillable = [
        'log_consulta_sicred_id',
        'cooperativa',
        'codigoBeneficiario',
        'cooperativaPostoBeneficiario',
        'nossoNumero',
        'seuNumero',
        'tipoCarteira',
        'dataPagamento',
        'valor',
        'valorLiquidado',
        'jurosLiquido',
        'descontoLiquido',
        'multaLiquida',
        'abatimentoLiquido',
        'tipoLiquidacao',
    ];
}
