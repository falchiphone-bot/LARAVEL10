<?php
/// efetuado por Pedro Roberto Falchi
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    // protected $casts = [
    //     'Bloqueiodataanterior' => 'datetime:d/m/Y',
    // ];

    public $timestamps = false;

}
