<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;


class ContasPagar extends Model
{
    protected $primaryKey = "ID";
    protected $table = 'Contabilidade.ContasPagar';

    public $timestamps = false;

    protected $fillable = [
        'ID',
        'Descricao',
        'DataDocumento',
        'DataVencimento',
        'DataProgramacao',
        'Valor',
        'ContaFornecedorID',
        'ContaPagamentoID',
        'LancamentoID',
        'EmpresaID',
        'UsuarioID',
        'Created',
        'Fixo',
        'NumTitulo',
        'Conferido',
        'Pago',
        'IDExtratoBradescoPJ',
        'IDExtratoSicredi',
        'IDSaldos',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'EmpresaID' => 'integer',
        'Created' => 'integer',
        'Modified' => 'integer',
        'Planocontas_id' => 'integer',
        'Usuarios_id' => 'integer',
        'Bloqueiodataanterior' => 'date',
        'Contapagamento' => 'integer',
        'BalancoEncerrado' => 'integer',
        'ContaPublica' => 'integer',
        'Nota' => 'integer',
    ];


    // public function PlanoConta(): BelongsTo
    // {
    //     return $this->belongsTo(PlanoConta::class, 'Planocontas_id','ID');
    // }

    // public function Empresa(): HasOne
    // {
    //     return $this->hasOne(Empresa::class,'ID','EmpresaID');
    // }
}
