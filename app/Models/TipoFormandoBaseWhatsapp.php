<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TipoFormandoBaseWhatsapp extends Model
{
    protected $table = 'TipoFormandoBaseWhatsapp';
    public $timestamps = true;
    protected $fillable = ['nome', 'EmpresaID','user_created','user_updated'];

    protected $casts = [
        'nome' => 'string',
        'EmpresaID' => 'int',
    ];

    public function MostraEmpresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'id', 'EmpresaID');
    }

    // public function MostraOrigem(): HasOne
    // {
    //     return $this->hasOne(Pacpie::class, 'origem_cadastro', "id");
    // }


}
