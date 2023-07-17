<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ContasCentroCustos extends Model
{
    protected $table = 'contabilidade.contascentrocustos';
    protected $primaryKey = "ID";
    public $timestamps = false;
    protected $fillable = ['CentroCustoID', 'ContaID','UsuarioID','Created'];

    protected $casts = [
        'CentroCustoID' => 'int',
    ];
}



