<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lancamento extends Model
{
    protected $primaryKey = "ID";
    protected $table = 'Contabilidade.Lancamentos';
    public $timestamps = false;

    protected $fillable = [
        'ID',
        'Valor',
        'EmpresaID',
        'ContaDebitoID',
        'ContaCreditoID',
        'Usuarios_id',
        'DataContabilidade',
        'Created',
        'HistoricoID',
        'Complemento',
        'Descricao',
        'IDDocumentoEmpresa',
        'IdSolicitacaoEmpresarial',
        'Conferido',
        'SaidasGeral',
        'EntradasGeral',
        'Notificacao',
        'DiasNotificacaoAntesVencimento',
        'Investimentos',
        'Transferencias'
    ];

    protected $casts = [
        'DataContabilidade' => 'date:Y-m-d',
        'Conferido' => 'boolean',
        'SaidasGeral' => 'boolean',
        'EntradasGeral' => 'boolean',
        'Notificacao' => 'boolean',
        'Investimentos' => 'boolean',
        'Transferencias' => 'boolean',
    ];

    public function Empresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'ID', 'EmpresaID');
    }

    public function ContaDebito(): HasOne
    {
        return $this->hasOne(Conta::class, 'ID', 'ContaDebitoID');
    }

    public function ContaCredito(): HasOne
    {
        return $this->hasOne(Conta::class, 'ID', 'ContaCreditoID');
    }

    public function SolicitacaoExclusao(): HasOne
    {
        return $this->hasOne(SolicitacaoExclusao::class, 'TableID', 'ID');
    }

    public function getDataContabilidadeAttribute($value)
    {
        return Carbon::createFromDate($value);
    }

    /**
     * Get all of the Arquivos for the Lancamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function arquivos()
    {
        return $this->hasMany(LancamentoDocumento::class, 'LancamentoID', 'ID');
    }
    public function Historico(): HasOne
    {
        return $this->hasOne(Historicos::class, 'ID', 'HistoricoID');
    }
    public function ContasPagarArquivo(): HasOne
    {
        return $this->hasOne(ContasPagar::class, 'LancamentoID', 'ID');
    }


    // public function setDataContabilidadeAttribute($value)
    // {
    //     return Carbon::createFromDate($value)->format('d/m/Y');
    // }
}
