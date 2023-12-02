<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergiaInjetada extends Model
{
    use HasFactory;

    protected $table = 'energiainjetada';
    protected $primaryKey = 'id';
    public $timestamps = false; // Se você quiser controlar manualmente o created_at e updated_at, defina como true

    protected $fillable = [
        'created_at',
        'updated_at',
        'user_updated',
        'nome_operadora',
        'codigo_da_conta',
        'mes',
        'vencimento',
        'valor',
        'medidor',
        'data_leitura_anterior',
        'data_leitura_atual',
        'consumo_leitura_anterior',
        'consumo_leitura_atual',
        'energia_gerada_anterior',
        'energia_gerada_atual',
        'energia_injetada_te',
        'energia_injetada_te_valor',
        'energia_injetada_tusd',
        'energia_injetada_tusd_valor',
    ];
}


