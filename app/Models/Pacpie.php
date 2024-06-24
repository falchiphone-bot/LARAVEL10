<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pacpie extends Model
{
    protected $table = 'Pacpie';
    public $timestamps = true;
    protected $fillable = ['nome', 'EmpresaID', 'cnpj','email', 'telefone','user_created','user_updated'];

    protected $casts = [
        'nome' => 'string',
        'cnpj' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'EmpresaID' => 'int'
    ];


    public function MostraEmpresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'id', 'EmpresaID');
    }


}
