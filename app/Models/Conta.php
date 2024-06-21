<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'Bloqueiodataanterior' => 'date',
        'Contapagamento' => 'integer',
        'BalancoEncerrado' => 'integer',
        'ContaPublica' => 'integer',
        'Nota' => 'integer',
    ];

    /**
     * Get the PlanoConta that owns the Conta
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function PlanoConta(): BelongsTo
    {
        return $this->belongsTo(PlanoConta::class, 'Planocontas_id','ID');
    }

    public function Empresa(): HasOne
    {
        return $this->hasOne(Empresa::class,'ID','EmpresaID');
    }
     
}
