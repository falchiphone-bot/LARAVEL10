<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormandoBasePosicoes extends Model
{
    protected $table = 'formandobaseposicoes';
    public $timestamps = true;
    protected $fillable = ['formandobase_id', 'posicao_id', 'user_created', 'user_updated' ];

    protected $casts = [
        'formandobase_id' => 'int',
        'posicao_id' => 'int',
        'created_at' => 'string',
        'updated_at' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];


    public function MostraNome(): HasOne
    {
        return $this->hasOne(TipoEsporte::class, 'id', 'tipo_esporte');
    }
}
