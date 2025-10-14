<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitacaoExclusao extends Model
{
    protected $primaryKey = "ID";
    protected $table = 'Contabilidade.Solicitacoes';

    public $timestamps = false;


    public $fillable = [
        "ID",
        "Tipo",
        "Descricao",
        "UsuarioID",
        "Status",
        "Created",
        "Table",
        "TableID",
        "ContaDebitoID",
        "ContaCreditoID",
    ];

    protected $casts = [
        'Created' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'UsuarioID');
    }


    public function lancamento(): HasOne
    {
        return $this->hasOne(lancamento::class, 'ID', 'TableID');
    }

}
