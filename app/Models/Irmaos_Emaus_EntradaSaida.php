<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Irmaos_Emaus_EntradaSaida extends Model
{
    protected $table = 'Irmaos_Emaus_EntradaSaida';
    public $timestamps = true;
    protected $fillable = ['Empresa','idFichaControle',
     'TipoEntradaSaida','DataEntradaSaida',
                           'Anotacoes', 'user_created',
                           'user_updated'];

    protected $casts = [
        'Empresa' => 'string',
        'idFichaControle' => 'string',
        'TipoEntradaSaida' => 'string',
        'DataEntradaSaida' => 'date',
        'Anotacoes' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];

    public function FichaControle()
    {
        return $this->belongsTo('App\Models\Irmaos_Emaus_FichaControle', 'idFichaControle');
    }
    
}

