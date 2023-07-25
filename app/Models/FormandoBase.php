<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormandoBase extends Model
{
    protected $table = 'formandobase';
    public $timestamps = true;
    protected $fillable = ['nome', 'cpf', 'rg', 'email', 'telefone','representante_id','EmpresaID', 'user_created', 'user_updated', 'deleted_at','nascimento' ];

    protected $casts = [
        'nome' => 'string',
        'cpf' => 'string',
        'rg' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'representante_id' => 'int',
        'EmpresaID' => 'int',
        'user_created' => 'string',
        'user_updated' => 'string',
        'deleted_at' => 'datetime',
        'nascimento' => 'date'
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
