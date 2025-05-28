<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Irmaos_Emaus_RelatorioPia extends Model
{
    protected $table = 'Irmaos_Emaus_RelatorioPia';
    public $timestamps = true;
    protected $fillable = ['Empresa','idFichaControle',
                            'idIrmaos_EmausPia','Data','Anotacoes', 'user_created',
                           'user_updated'];

    protected $casts = [
        'Empresa' => 'string',
        'idFichaControle' => 'string',
        'idIrmaos_EmausPia' => 'string',
        'Data' => 'date',
        'Anotacoes' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];

    public function FichaControle()
    {
        return $this->belongsTo('App\Models\Irmaos_Emaus_FichaControle', 'idFichaControle');
    }
    public function Irmaos_EmausPia()
    {
        return $this->belongsTo('App\Models\Irmaos_EmausPia', 'idIrmaos_EmausPia');
    }

}

