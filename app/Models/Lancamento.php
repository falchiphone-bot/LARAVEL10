<?php

namespace App\Models;

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
        'DataContabilidade' => 'date',
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
}
