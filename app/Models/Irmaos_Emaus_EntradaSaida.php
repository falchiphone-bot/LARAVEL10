<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Irmaos_Emaus_EntradaSaida extends Model
{
    protected $table = 'Irmaos_EmausEntradaSaida';
    public $timestamps = true;
    protected $fillable = ['Empresa','idFichaControle', 'Entrada',
                           'Saida', 'Anotacoes', 'user_created',
                           'user_updated'];

    protected $casts = [
        'Empresa' => 'string',
        'idFichaControle' => 'numeric',
        'Entrada' => 'date',
        'Saida' => 'date',
        'Anotacoes' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];
}
