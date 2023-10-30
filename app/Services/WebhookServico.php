<?php
namespace App\Services;

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


    public static function Agradecimento_por_ter_lido_mensagem_recebida($recipient_id)
    {
       
        $Contact = WebhookContact::where('recipient_id', $recipient_id)->first();


        return $Contact;
    }

}
