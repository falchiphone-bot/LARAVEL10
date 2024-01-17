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

class WebhookMensagensAprovadas
{


    public static function AvaliacaoBreve($recipient_id, $entry_id)
    {
        $name = 'avaliacao_em_breve';
        WebhookMensagensAprovadas::EnviaMensagemAprovadaSalva($recipient_id, $entry_id, $name );
    }

    public static function avaliacao_29012024_ate_01032024($recipient_id, $entry_id)
    {
        $name = 'avaliacao_29012024_ate_01032024_3';
        WebhookMensagensAprovadas::EnviaMensagemAprovadaSalva($recipient_id, $entry_id, $name );
    }



      public static function pagina_tanabi_saf_facebook ($recipient_id, $entry_id)
      {
        $name = 'pagina_tanabi_saf_facebook';
        WebhookMensagensAprovadas::EnviaMensagemAprovadaSalva($recipient_id, $entry_id, $name );
      }


      public static function EnviaMensagemAprovadaSalva($recipient_id, $entry_id,  $name)
      {
        $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
        ->OrderBy('usuario')
        ->get()
        ->first();

        Log::info('ENTRY_ID:'. $entry_id);
        Log::info('RECIPIENT_ID:'. $recipient_id);

        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
        $Token = $WebhookConfig->token24horas;

        $client = new Client();
        $requestData = [];



        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $recipient_id,
            'type' => 'template',
            'template' => [
                'name' => $name,
                'language' => [
                    'code' => 'pt_BR',
                ],
            ],
        ];

        Log::info('Template:'. $name);

            $response = $client->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $Token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
            ]);

            if ($response->getStatusCode() == 200) {
                Log::info('Mensagem enviado com sucesso!');

                $contatos = WebhookContact::where('recipient_id', $recipient_id)
                ->where('entry_id', $entry_id)
                ->first();

                $newWebhook = webhook::create([
                  'webhook' => json_encode($requestData) ?? null,
                  'value_messaging_product' => $requestData['messaging_product'] ?? null,
                  'object' => $requestData['messaging_product'] ?? null,
                  'entry_id' => $entry_id ?? null,
                  'contactName' => $contatos->contactName ?? null,
                  'recipient_id' => $requestData['to'] ?? null,
                  'type' => $requestData['type'] ?? null,
                  'messagesType' => $requestData['type'] ?? null,
                  'body' => $name ?? null,
                  'status' => 'sent' ?? null,
                  'user_atendimento' => Auth::user()->email ?? null,
                  'flow_token' => $flow_token ?? null,
                  'flow_description'=> $flow_description ?? null,
              ]);
            }

      }



}

