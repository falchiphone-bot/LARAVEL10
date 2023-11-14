<?php
namespace App\Services;
use GuzzleHttp\Client;

use App\Models\WebhookContact;
use App\Models\WebhookConfig;
use Illuminate\Support\Facades\Auth;

class WebhookServico
{
    public static function AtualizaOuCriaWebhookContact($recipient_id, $contactName)
    {
        $newWebhookContact = WebhookContact::where('recipient_id', $recipient_id)->first();

        if ($newWebhookContact) {
            $newWebhookContact->update([
                // 'contactName' => $contactName ?? null,
                'user_updated' => Auth::user()->email ?? null,
            ]);
        } else {
            $newWebhookContact = WebhookContact::create([
                'contactName' => $contactName ?? null,
                'recipient_id' => $recipient_id ?? null,
                'user_updated' => Auth::user()->email ?? null,
            ]);
        }

        return $newWebhookContact;
    }


    public static function Agradecimento_por_ter_lido_mensagem_recebida($recipient_id, $accessToken)
    {

        $client = new Client();
        // $phone = $recipient_id;
        $phone = "5517997662949";
        $client = new Client();
        $requestData = [];
        $message = 'Agradecemos por ter visto nossa mensagem!';
        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];
        $response = $client->post(
            'https://graph.facebook.com/v17.0/147126925154132/messages',
            [

                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
            ]
        );

        if ($response->getStatusCode() == 200) {
            // $responseData = json_decode($response->getBody());
            // $newWebhook = webhook::create([
            //     'webhook' => json_encode($requestData) ?? null,
            //     'value_messaging_product' => $requestData['messaging_product'] ?? null,
            //     'object' => $requestData['messaging_product'] ?? null,
            //     'contactName' => $model->contactName ?? null,
            //     'recipient_id' => $requestData['to'] ?? null,
            //     'type' => $requestData['type'] ?? null,
            //     'body' => $requestData['text']['body'] ?? null,
            //     'status' => 'sent' ?? null,
            // ]);
            }

    }

    public static function Token24horas()
    {
        $WebhookConfig =  WebhookConfig::OrderBy('usuario')->get()->first();
        $accessToken = $WebhookConfig->token24horas;


        return $accessToken;

    }

    public static function phone_number_id()
    {
        $WebhookConfig  =  WebhookConfig::OrderBy('usuario')->get()->first();
        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;


        return $phone_number_id;

    }

    public static function grava_user_atendimento($id)
    {

        $Usuario_sistema = Auth::user()->email;

        $User_Atendente = WebhookContact::where('recipient_id', $id)->first();
        if (!empty($User_Atendente->user_atendimento)) {

            if($User_Atendente->user_atendimento !== $Usuario_sistema)
            {

                if (session('usuarioatendente') == null) {
                    session(['usuarioatendente' => 'Cliente sendo atendido por: ' . $User_Atendente->user_atendimento]);
                }


                return;
            }
        }

        else
        {
            if ($User_Atendente ) {
            $User_Atendente->update([
                'user_atendimento' => $Usuario_sistema,
            ]);
           }
        }

        return;

    }
}
