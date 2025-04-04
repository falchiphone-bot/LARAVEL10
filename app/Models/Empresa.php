<?php
/// efetuado por Pedro Roberto Falchi
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Empresa extends Model
{
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
        'ClassificaCaixaGeral',
    ];

    protected $casts = [
        'Bloqueiodataanterior' => 'date',
        'Created' => 'datetime',
    ];

    public $timestamps = false;

    // public function  RelacionadaNaEmpresa(): HasOne
    // {
    //     return $this->hasOne(Faturamentos::class, 'EmpresaID', 'ID');
    // }

    /**
     * The EmpresaUsuario that belong to the Empresa
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function EmpresaUsuario()
    {
        return $this->belongsToMany(User::class, 'Contabilidade.EmpresasUsuarios', 'EmpresaID', 'UsuarioID');
    }


}
