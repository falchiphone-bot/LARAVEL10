<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContasCentroCustos extends Model
{
    protected $table = 'contabilidade.contascentrocustos';
    protected $primaryKey = "ID";
    public $timestamps = false;
    protected $fillable = ['CentroCustoID', 'ContaID','UsuarioID','Created'];

    protected $casts = [
        'CentroCustoID' => 'int',
    ];


    public function MostraCentroCusto(): HasOne
    {
        return $this->hasOne(CentroCustos::class, 'ID', 'CentroCustoID');
    }
    public function MostraContaCentroCusto(): HasOne
    {
        return $this->hasOne(PlanoConta::class, 'ID', 'ContaID');
    }

}



