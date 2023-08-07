<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Preparadores extends Model
{
    protected $table = 'Preparadores';
    public $timestamps = true;
    protected $fillable = ['nome', 'email', 'telefone','licencaCBF','CargoProfissional','FuncaoProfissional'];

    protected $casts = [
        'nome' => 'string',
    ];

    public function mostraCargoProfissional(): HasOne
    {
        return $this->hasOne(CargoProfissional::class, 'id', 'CargoProfissional');
    }

    public function mostraFuncaoProfissional(): HasOne
    {
        return $this->hasOne(FuncaoProfissional::class, 'id', 'FuncaoProfissional');
    }

}
