<?php
namespace App\Services;
use GuzzleHttp\Client;

use App\Models\WebhookContact;

class WebhookServico
{
    public static function AtualizaOuCriaWebhookContact($recipient_id, $contactName)
    {
        $newWebhookContact = WebhookContact::where('recipient_id', $recipient_id)->first();

        if ($newWebhookContact) {
            $newWebhookContact->update([
                // 'contactName' => $contactName ?? null,
                'user_updated' => auth()->user()->email ?? null,
            ]);
        } else {
            $newWebhookContact = WebhookContact::create([
                'contactName' => $contactName ?? null,
                'recipient_id' => $recipient_id ?? null,
                'user_updated' => auth()->user()->email ?? null,
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

}
