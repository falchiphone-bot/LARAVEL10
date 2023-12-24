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

    public static function EnviaMensagemFlowAlterarCpf($recipient_id, $entry_id)
    {
        
      Log::info('Telefone contato:' . $recipient_id . ' canal:'. $entry_id);

        $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
        ->OrderBy('usuario')
        ->get()
        ->first();
        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
        $Token = $WebhookConfig->token24horas;


        $client = new Client();
        $requestData = [];

        $requestData = [
          'messaging_product' => 'whatsapp',
          'recipient_type' => 'individual',
          'to' => $recipient_id,
          'type' => 'template',
          'template' => [
              'name' => 'cadastro_alterar_cpf',
              'language' => [
                  'code' => 'pt_BR'
              ],
              'components' => [
                  [
                      'type' => 'button',
                      'sub_type' => 'flow',
                      'index' => '0',
                      'parameters' => [
                          [
                              'type' => 'action',
                              'action' => [
                                  'flow_token' => '372275572014981'
                              ]
                          ]
                      ]
                  ]
              ]
          ]
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

            $contatos = WebhookContact::where('recipient_id', $recipient_id)
            ->where('entry_id', $entry_id)
            ->first();

            $newWebhook = webhook::create([
              'webhook' => json_encode($requestData) ?? null,
              'value_messaging_product' => $requestData['messaging_product'] ?? null,
              'object' => $requestData['messaging_product'] ?? null,
              'entry_id' => $entry_id?? null,
              'contactName' => $contatos->contactName ?? null,
              'recipient_id' => $requestData['to'] ?? null,
              'type' => $requestData['type'] ?? null,
              'messagesType' => $requestData['type'] ?? null,
              'body' => 'Enviado o flow  cadastro_alterar_cpf, token 372275572014981',
              'status' => 'sent' ?? null,
              'user_atendimento' => Auth::user()->email,
              'flow_token' => '372275572014981',
              'flow_description'=> 'cadastro_alterar_cpf',
          ]);
        }    
 
    }

}

