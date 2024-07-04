<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormandoBaseWhatsapp extends Model
{
    protected $table = 'formandobasewhatsapp';
    public $timestamps = true;
    protected $fillable = [
    'nome',
    'cpf',
    'rg',
    'email',
    'telefone',
    'whatsapp',
    'representante_id',
    'EmpresaID',
    'entry_id',
    'user_created',
    'user_updated',
    'deleted_at',
    'nascimento',
    'nomeMae',
    'nomePai',
    'nomeResponsavel',
    'posicao',
    'flow_token',
    'flow_description',
    'codigo_registro',
    'cidade',
    'uf',
    'motivo_cadastro',
 ];

    protected $casts = [
        'nome' => 'string',
        'cpf' => 'string',
        'rg' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'whatsapp' => 'string',
        'representante_id' => 'int',
        'EmpresaID' => 'int',
        'entry_id' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
        'deleted_at' => 'datetime',
        'nascimento' => 'date',
        'nomeMae' => 'string',
        'nomePai' => 'string',
        'nomeResponsavel' => 'string',
        'posicao' => 'string',
        'flow_token' => 'string',
        'flow_description' => 'string',
        'codigo_registro' => 'string',
        'cidade' => 'string',
        'uf' => 'string',
        'motivo_cadastro' => 'string',

    ];


    public function MostraRepresentante(): HasOne
    {
        return $this->hasOne(Representantes::class, 'id', 'representante_id');
    }

    //  public function MostraFormandoBase(): HasOne
    //  {
    //     return $this->hasOne(FormandoBaseArquivo::class, 'formandobase_id', 'id');
    //  }


}
