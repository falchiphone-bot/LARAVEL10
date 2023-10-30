<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class webhookConfig extends Model
{
    use HasFactory;

    protected $table = 'webhooksConfig'; // Nome da tabela no banco de dados
    public $timestamps = true;

    protected $fillable = [
        'user_updated',
        'token24horas',
        'tokenpermanenteusuario',
        'usuario',
        'identificacaonumerotelefone',
        'identificacaocontawhatsappbusiness',
    ];

}
