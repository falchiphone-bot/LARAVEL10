<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LancamentoDocumento extends Model
{
    protected $table = 'Contabilidade.LancamentosDocumentos';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public $fillable = [
        'Rotulo',
        'LancamentoID',
        'Nome',
        'NomeLocalTimeStamps',
        'Created',
        'UsuarioID',
        'Ext',
        'Documento',
        'TipoArquivo',
        'ArquivoFisico',
        'Email1Vinculado',
        'AnotacoesGerais',
    ];

    protected $casts = [
        'Created' => 'datetime',
    ];

    /**
     * Get the user associated with the LancamentoDocumento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'ID', 'UsuarioID');
    }

    public function TipoArquivoNome(): HasOne
    {
        return $this->hasOne(TipoArquivo::class, 'id', 'TipoArquivo');
    }
    // public function MostraFormandoBase(): HasOne
    // {
    //     return $this->hasOne(FormandoBaseArquivo::class, 'formandobase_id', 'id');
    // }

}
