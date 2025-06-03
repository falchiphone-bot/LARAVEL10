<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Irmaos_Emaus_FichaControleArquivo extends Model
{
    protected $table = 'Irmaos_Emaus_FichaControleArquivo';
    public $timestamps = true;


  protected $fillable = ['caminho', 'nomeArquivo','ficha_id', 'user_created', 'user_updated'];

    protected $casts = [
        'ficha_id' => 'integer',
        'caminho' => 'string',
        'nomeArquivo' => 'string',
    ];



    public function Irmaos_EmausServicos()
    {
        return $this->belongsTo(Irmaos_Emaus_FichaControle::class, 'ficha_id');
    }
}

