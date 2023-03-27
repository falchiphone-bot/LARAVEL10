<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaUsuario extends Model
{
    protected $table = 'Contabilidade.EmpresasUsuarios';
    public $timestamps = false;

    protected $fillable = [
        'EmpresaID',
        'UsuarioID',
        'Criar',
        'Ler',
        'Alterar',
        'Excluir',
        'Administrador',

    ];

    protected $casts = [
        'EmpresaID' => 'integer',
        'UsuarioID' => 'integer',
        'Criar' => 'integer',
        'Ler' => 'integer',
        'Alterar' => 'integer',
        'Excluir' => 'integer',
        'Administrador' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'EmpresaID');
    }
}
