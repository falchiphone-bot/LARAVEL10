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

class WebhookServicoInteligencia
{


    public static function selecaotexto($entry)
    {
        Log::info('Em selecaotexto pelo procedimento de inteligencia artificial');
        $entry_id = $entry['id'] ?? null;
        $body  =  $entry[0]['changes'][0]['value']['messages'][0]['body'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $body = $entry['changes'][0]['value']['messages'][0]['text']['body'] ?? null;
        $recipient_id  =  $messagesFrom;
        
                 if($body == 'OPÇÃO' || $body == 'opção' || $body == 'Opção'
                 || $body == 'OPCAO' || $body == 'opcao' || $body == 'Opcao')
                    {
                        Log::info('Pesquisa:'.$body);
                        WebhookContactsEnviarFlow::
                        EnviaMensagemFlowMenuCadastroBasico($recipient_id, $entry_id);
                        // dd('achou!',$body, $recipient_id, $entry_id );
                    }
                // dd('NAO ACHADO',$body, $recipient_id, $entry_id);
    }



}

