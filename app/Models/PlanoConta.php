<?php
/// efetuado por Pedro Roberto Falchi
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlanoConta extends Model
{
    protected $primaryKey = "ID";
    protected $table = 'Contabilidade.PlanoContas';

    protected $fillable = [
        'Descricao',
        'Created',
        'Modified',
        'Grau',
        'Codigo',
        'Tipo',
        'UsuarioID',
        'Bloqueiodataanterior',
        'X',
        'CalculoHabilitado',
        'CodigoSkala',
        'Agrupamento',
        'UsarDolar',
    ];


    protected $casts = [
        'UsarDolar' => 'boolean',
    'Created' => 'datetime',
    'Modified' => 'datetime',

    ];

    // protected $casts = [
    //     'Bloqueiodataanterior' => 'datetime:d/m/Y',
    // ];

    public $timestamps = false;


    public function MostraNome(): HasOne
    {
        return $this->hasOne(AgrupamentosContas::class, 'id', 'Agrupamento');
    }

}
