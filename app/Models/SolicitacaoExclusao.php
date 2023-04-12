<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoExclusao extends Model
{
    protected $primaryKey = "ID";
    protected $table = 'Contabilidade.Solicitacoes';


    public $fillables = [
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


    
}
