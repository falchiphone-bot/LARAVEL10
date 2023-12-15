<?php
namespace App\Services;
use App\Models\webhookAtendimentoEncerrado;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\webhook;
use App\Models\WebhookContact;
use App\Models\WebhookConfig;
use App\Models\WebhookTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class WebhookContactsServico
{
    public static function FiltraCanaisUsuariosAtivos()
{
    $result = []; // Array para armazenar os resultados

    if (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127') && Gate::allows('WHATSAPP_ENTRY_ID_189514994242034')) {
        $RegistrosContatos = webhookContact::
            where(function ($query) {
                $query->whereNull('ocultar_lista_atendimento')
                    ->orWhere('ocultar_lista_atendimento', 0);
            })
            ->whereIn('entry_id', ['189514994242034', '167722543083127'])
            ->orderby('updated_at', 'desc')
            ->get();

        $QuantidadeCanalAtendimento = 2;
    } elseif (Gate::allows('WHATSAPP_ENTRY_ID_189514994242034')) {
        $RegistrosContatos = webhookContact::
            where(function ($query) {
                $query->whereNull('ocultar_lista_atendimento')
                    ->orWhere('ocultar_lista_atendimento', 0);
            })
            ->whereIn('entry_id', ['189514994242034'])
            ->orderby('updated_at', 'desc')
            ->get();
        $QuantidadeCanalAtendimento = 1;
    } elseif (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127')) {
        $RegistrosContatos = webhookContact::
            where(function ($query) {
                $query->whereNull('ocultar_lista_atendimento')
                    ->orWhere('ocultar_lista_atendimento', 0);
            })
            ->whereIn('entry_id', ['167722543083127'])
            ->orderby('updated_at', 'desc')
            ->get();
        $QuantidadeCanalAtendimento = 1;
    }

    // Adicione as variáveis aos resultados
    $result['RegistrosContatos'] = $RegistrosContatos;
    $result['QuantidadeCanalAtendimento'] = $QuantidadeCanalAtendimento;

    // Retorne os resultados para a controller
    return $result;
}



}

