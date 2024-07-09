<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pacpie extends Model
{
    protected $table = 'Pacpie';
    public $timestamps = true;
    protected $fillable = ['nome', 'EmpresaID',
    'cnpj',
    'email',
    'telefone',
    'user_created',
    'user_updated',
    'emailprimeirocontato',
    'emailcomfalhas',
    'origem_cadastro',
    'responsavel',
    'observacao',
    'promessa_aporte',
    'promessa_aporte_ano',
    'aportou',
    'aportou_ano',
    'aportou_valor',
    'retornoemailprimeirocontato'];

    protected $casts = [
        'nome' => 'string',
        'responsavel' => 'string',
        'cnpj' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'EmpresaID' => 'int',
        'emailprimeirocontato' => 'boolean',
        'retornoemailprimeirocontato' => 'boolean',
        'emailcomfalhas' => 'boolean',
        'origem_cadastro' => 'int',
        'observacao' => 'string',
        'promessa_aporte' => 'string',
        'promessa_aporte_ano' => 'string',
        'aportou' => 'string',
        'aportou_ano' => 'string',
        'aportou_valor' => 'float',

    ];


    public function MostraEmpresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'id', 'EmpresaID');
    }

    public function MostraOrigem(): HasOne
    {
        return $this->hasOne(OrigemPacpie::class, 'id', 'origem_cadastro');
    }


}
