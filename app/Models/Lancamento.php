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
        'Notificacao',
        'DiasNotificacaoAntesVencimento',
    ];

    protected $casts = [
        'DataContabilidade' => 'date:Y-m-d',
    ];

    /**
     * Get the Empresa associated with the Lancamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function Empresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'ID', 'EmpresaID');
    }

    /**
     * Get the ContaDebito associated with the Lancamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ContaDebito(): HasOne
    {
        return $this->hasOne(Conta::class, 'ID', 'ContaDebitoID');
    }

    /**
     * Get the ContaCredito associated with the Lancamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ContaCredito(): HasOne
    {
        return $this->hasOne(Conta::class, 'ID', 'ContaCreditoID');
    }

    /**
     * Get the SolicitacaoExclusao associated with the Lancamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function SolicitacaoExclusao(): HasOne
    {
        return $this->hasOne(SolicitacaoExclusao::class, 'TableID', 'ID');
    }


    public function getDataContabilidadeAttribute($value)
    {
        return Carbon::createFromDate($value);
    }
    public function setDataContabilidadeAttribute($value)
    {
        return Carbon::createFromDate($value)->format('d/m/Y');
    }
}
