<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LancamentoComentario extends Model
{
    use HasFactory;

    protected $primaryKey = "ID";
    protected $table = 'Contabilidade.LancamentosComentarios';
    public $timestamps = false;

    public $fillable = [
        'LancamentoID',
        'Descricao',
        'UsuarioID',
        'Created',
        'Visualizado',
    ];

    protected $casts = [
        'Created' => 'date:Y-m-d',
    ];

    /**
     * Get the user associated with the LancamentoComentario
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'UsuarioID');
    }

}
