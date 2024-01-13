<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;


class webhookContact extends Model
{
    use HasFactory;

    protected $table = 'webhooksContacts'; // Nome da tabela no banco de dados
    public $timestamps = true;

    protected $fillable = [
        'user_updated',
        'contactName',
        'recipient_id',
        'status_mensagem_enviada',
        'status_mensagem_entregue',
        'ultima_leitura',
        'ultima_entrega',
        'quantidade_nao_lida',
        'user_atendimento',
        'pagina_refresh',
        'transferido_para',
        'timestamp',
        'entry_id',
        'ocultar_lista_atendimento',
        'alerta_mensagem_recebida',
        'bloquear_entrada_mensagem',

    ];

    public function TelefoneWhatsApp(): HasOne
    {
        return $this->hasOne(webhookconfig::class, 'identificacaocontawhatsappbusiness', 'entry_id');
    }

}
