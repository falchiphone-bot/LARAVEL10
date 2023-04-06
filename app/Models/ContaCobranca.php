<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContaCobranca extends Model
{
    protected $conection = 'atletas';
    protected $table = 'contascobranca';
    // public $timestamps = false;

    public $fillable = [
        'EmpresaID',
        'conta',
        'agencia',
        'posto',
        'associadobeneficiario',
        'token_conta',
        'idDevSicredi',
        'ValorTarifaCobranca',
        'Credito_Cobranca',
        'Tarifa_Cobranca',
    ];

    /**
     * Get the contaDev associated with the ContaCobranca
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function devSicredi(): HasOne
    {
        return $this->hasOne(DevSicredi::class, 'id', 'idDevSicredi');
    }

    public function Empresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'id', 'EmpresaID');
    }
}
