<?php
namespace App\Services;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\webhook;
use App\Models\WebhookContact;
use App\Models\WebhookConfig;
use App\Models\WebhookTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WebhookContactsEnviarFlow
{

    // public static function EnviaMensagemFlowAlterarCpf($entry, $messagesFrom, $phone_number_id, $nome_contato, $message)
    public static function EnviaMensagemFlowAlterarCpf()
    {
        
      $messagesFrom = '5517997662949';
      $phone_number_id = '179589645239799';
    
      Log::info('Identificação:' . $phone_number_id);

        $WebhookConfig = WebhookConfig::Where('identificacaonumerotelefone', $phone_number_id)
        ->OrderBy('usuario')
        ->get()
        ->first();
  

        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;

        $Token = $WebhookConfig->token24horas;

        $client = new Client();
        $requestData = [];

        $requestData = [
          "messaging_product": "whatsapp",
          "recipient_type": "individual",
          "to": $messagesFrom,
          "type": "template",
          "template": {
            "name": "cadastro_alterar_cpf",
            "language": {
              "code": "pt_BR"
            },
            "components": [
              {
                "type": "button",
                "sub_type": "flow",
                "index": "0",
                "parameters": [
                  {
                    "type": "action",
                    "action": {
                      "flow_token": "372275572014981" 
                    }
                  }
                ]
              }
            ]
          }
        ];

        $response = $client->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $Token,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);
   
        if ($response->getStatusCode() == 200) {
            Log::info('Flow enviado com sucesso!');

        }
    }

}

