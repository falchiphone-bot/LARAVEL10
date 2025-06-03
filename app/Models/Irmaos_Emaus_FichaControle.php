<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Irmaos_Emaus_FichaControle extends Model
{
    protected $table = 'Irmaos_Emaus_FichaControle';
    public $timestamps = true;
    protected $fillable =
    [
    'Empresa',
    'Nome',
    'Nascimento',
    'CidadeNaturalidade',
    'UF_Naturalidade',
    'Mae',
    'Pai',
    'Rg',
    'Cpf',
    'Nis',
    'Escolaridade',
    'EntradaPrimeiraVez',
    'SaidaPrimeiraVez',
    'Prontuario',
    'Livro',
    'Folha',
    'Entrada',
    'Saida',
    'user_created',
    'user_updated',
    'idServicos',
  ];

    protected $casts = [
        'Nome' => 'string',
        'Nascimento' => 'date',
        'CidadeNaturalidade' => 'string',
        'UF_Naturalidade' => 'string',
        'Mae' => 'string',
        'Pai' => 'string',
        'Rg' => 'string',
        'Cpf' => 'string',
        'Nis' => 'string',
        'Escolaridade' => 'string',
        'EntradaPrimeiraVez' => 'date',
        'SaidaPrimeiraVez' => 'date',
        'Prontuario' => 'string',
        'Livro' => 'string',
        'Folha' => 'string',
        'Entrada' => 'date',
        'Saida' => 'date',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];

    public function Irmaos_EmausServicos()
    {
        return $this->belongsTo(Irmaos_EmausServicos::class, 'idServicos');
    }

    public function arquivos()
    {
        return $this->hasMany(Irmaos_Emaus_FichaControleArquivo::class, 'ficha_id');
    }
}
