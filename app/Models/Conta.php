<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conta extends Model
{
    protected $primaryKey = "ID";
    protected $table = 'Contabilidade.Contas';

    public $timestamps = false;

    protected $fillable = [
        'ID',
        'EmpresaID',
        'Created',
        'Modified',
        'Planocontas_id',
        'Usuarios_id',
        'Bloqueiodataanterior',
        'Contapagamento',
        'BalancoEncerrado',
        'ContaPublica',
        'Nota',
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
        'Bloqueiodataanterior' => 'integer',
        'Contapagamento' => 'integer',
        'BalancoEncerrado' => 'integer',
        'ContaPublica' => 'integer',
        'Nota' => 'integer',
    ];

    /**
     * Get the PlanoConta associated with the Conta
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function PlanoConta(): HasOne
    {
        return $this->hasOne(PlanoConta::class, 'ID', 'Planocontas_id');
    }
}
