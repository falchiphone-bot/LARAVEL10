<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrigemPacpie extends Model
{
    protected $table = 'OrigemPacpie';
    public $timestamps = true;
    protected $fillable = ['nome', 'EmpresaID','email', 'telefone','user_created','user_updated'];

    protected $casts = [
        'nome' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'EmpresaID' => 'int',
    ];


    // public function MostraOrigem(): HasOne
    // {
    //     return $this->hasOne(Pacpie::class, 'origem_cadastro', "id");
    // }


}
