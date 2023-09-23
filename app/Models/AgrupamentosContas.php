<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AgrupamentosContas extends Model
{
    protected $table = 'Contabilidade.Agrupamentos';
    public $timestamps = true;
    protected $fillable = ['nome', 'observacao', 'user_created','user_updated'];

    protected $casts = [
        'nome' => 'string',
    ];
}
