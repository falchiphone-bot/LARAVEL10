<?php

namespace App\Models\Ixc;

use App\Helpers\Enumerators;
use App\Models\Atendimento\Atendimento;
use App\Models\Comissoes\Pagamento;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientContract extends Model
{
    /**
     * @var string
     */
    public $table = 'cliente_contrato';

    /**
     * @var string
     */
    public $connection = 'ixc';
    protected $casts = [
        'id' => 'integer',
        'ultima_conexao_inicial' => 'date',
        'ultima_conexao_final' => 'date',
        'pago_ate_data' => 'date',
        'nao_bloquear_ate' => 'date',
        'data_ativacao' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientIxc::class, 'id_cliente');
    }
}
