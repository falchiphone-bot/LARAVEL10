<?php
/// efetuado por Pedro Roberto Falchi
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    public $Connection = 'sqlsrv_clube';


    protected $primaryKey = "ID";
    protected $table = 'Contabilidade.Empresas';

    protected $fillable = [
        'Cnpj',
        'Ie',
        'Created',
        'Descricao',
        'Bloqueiodataanterior',
        'X',
        'Bloqueio',
    ];



    public $timestamps = false;
}
