<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Representantes extends Model
{
    protected $table = 'representantes';
    public $timestamps = true;
    protected $fillable = ['nome', 'EmpresaID','cpf', 'cnpj','email', 'telefone','tipo_representante','agente_fifa','oficial_cbf','sem_registro','user_created','user_updated'];

    protected $casts = [
        'nome' => 'string',
        'cpf' => 'string',
        'cnpj' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'tipo_representante' => 'int',
        'EmpresaID' => 'int',
        'agente_fifa' => 'boolean',
        'oficial_cbf' => 'boolean',
        'sem_registro' => 'boolean',
    ];


    public function MostraTipo(): HasOne
    {
        return $this->hasOne(TipoRepresentante::class, 'id', 'tipo_representante');
    }
    public function MostraEmpresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'id', 'EmpresaID');
    }


}
