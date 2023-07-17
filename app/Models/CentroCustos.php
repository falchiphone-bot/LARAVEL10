<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CentroCustos extends Model
{
    protected $table = 'contabilidade.centrocustos';
    protected $primaryKey = "ID";
    public $timestamps = false;
    protected $fillable = ['Descricao', 'EmpresaID','Modified','Created','UsuarioID','ContaPublica'];

    protected $casts = [
        'Descricao' => 'string',
    ];
}



