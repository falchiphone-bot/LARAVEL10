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

class WebhookServico
{
    public static function AtualizaOuCriaWebhookContact($recipient_id, $contactName, $messagesTimestamp)
    {
        $newWebhookContact = WebhookContact::where('recipient_id', $recipient_id)->first();

        if ($newWebhookContact) {
            $newWebhookContact->update([
                // 'contactName' => $contactName ?? null,
                'user_updated' => Auth::user()->email ?? null,
            ]);
        }
        else {
            $newWebhookContact = WebhookContact::create([
                'contactName' => $contactName ?? null,
                'recipient_id' => $recipient_id ?? null,
                'user_updated' => Auth::user()->email ?? null,
                'timestamp' => $messagesTimestamp,
            ]);
        }

        Log::info($messagesTimestamp);
        if($messagesTimestamp != null){
            $newWebhookContact->update([
                'timestamp' => $messagesTimestamp,
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
            
            }

    }

    public static function Token24horas()
    {
        
       $WebhookConfig =  WebhookConfig::Where('ativado','1')->OrderBy('usuario')->get()->first();
        $accessToken = $WebhookConfig->token24horas;
        return $accessToken;

    }

    public static function phone_number_id($entry_id)
    {
 
        $WebhookConfig =  WebhookConfig::Where('identificacaocontawhatsappbusiness',trim($entry_id))->first();
 
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
                'transferido_para' => null,
            ]);
           }
        }

        return;

    }

    public static function grava_user_encerramento_atendimento($id)
    {
        $User_Atendente = WebhookContact::where('recipient_id', $id)->first();

            if ($User_Atendente ) {
            $User_Atendente->update([
                'user_atendimento' =>  null,
                'quantidade_nao_lida' => 0,
                'transferido_para' => null,
            ]);
           }

           $webhookAtendimentoEncerrado = webhookAtendimentoEncerrado::create([
            'id_contact' => $User_Atendente->id  ?? null,
            'user_atendimento' => Auth::user()->email ?? null,
            'inicio_atendimento' => false,
            'fim_atendimento'   => true,
        ]);
        return;

    }

    public static function grava_user_inicio_atendimento($id)
    {
        $Usuario_sistema = Auth::user()->email;

        $User_Atendente = WebhookContact::where('recipient_id', $id)->first();

            if ($User_Atendente ) {
            $User_Atendente->update([
                'user_atendimento' => $Usuario_sistema,
                'quantidade_nao_lida' => $User_Atendente ->quantidade_nao_lida-1,
                'transferido_para' => NULL,
             ]);
           }


           $webhookAtendimentoEncerrado = webhookAtendimentoEncerrado::create([
            'id_contact' => $User_Atendente->id  ?? null,
            'user_atendimento' => Auth::user()->email ?? null,
            'inicio_atendimento' => true,
            'fim_atendimento'   => false,
        ]);

        return;

    }


    public function refreshpagina($id)
    {

        $User_Atendente = WebhookContact::where('recipient_id', $id)->first();

            if ($User_Atendente ) {

                if($User_Atendente->pagina_refresh == null || $User_Atendente->pagina_refresh == false)
                {
                    $atualiza_pagina = true;
                }
                else
                {
                    $atualiza_pagina = false;
                }


            $User_Atendente->update([
                'pagina_refresh' => $atualiza_pagina,
            ]);
           }

           return redirect()->back();

    }


    public static  function transferiratendimento($id, $UsuarioID )
    {
             $usuario = trim(Auth::user()->email);
             $User = user::where('email',$UsuarioID)->first();
             $NomeAtendente = $User->name;


            $WebhookConfig =  WebhookConfig::Where('ativado','1')->OrderBy('usuario')->get()->first();

             $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
             $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
             $Token = $WebhookConfig->token24horas;


            $webhootContact = webhookcontact::find($id);
            $client = new Client();
            $phone = $webhootContact->recipient_id; // Número de telefone de destino
            $client = new Client();
            $requestData = [];

     $message = "A nossa conversa foi transferida para outro usuário: "
                 . $NomeAtendente
                 .  ". Aguarde que já o mesmo atenderá! Caso queira prosseguir é só enviar alguma nova mensagem. Obrigado!";

                 if($webhootContact->user_atendimento !== Auth::user()->email)
                 {
                  $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ")";

                 }
        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];
    $response = $client->post(
     'https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages',
     [

                 'headers' => [
                     'Authorization' => 'Bearer ' . $Token,
                     'Content-Type' => 'application/json',
                 ],
                 'json' => $requestData,
             ]
         );
         // Verifique a resposta
         if ($response->getStatusCode() == 200) {

             $responseData = json_decode($response->getBody());
             // Faça algo com a resposta, se necessário
             // dd("Mensagem nova enviada", $responseData);
  ///////////////////Gravar
            //  $registro = webhookContact::where('recipient_id', $phone)->get()->first();
            $registro =  $webhootContact;

             $registro->update([
              'status_mensagem_enviada' => 0,
              'user_updated' => $usuario,
              'quantidade_nao_lida' => $registro->quantidade_nao_lida+1,
            ]);

            $registro->save();

             $newWebhook = webhook::create([
                 'webhook' =>  null,
                 'value_messaging_product' => $requestData['messaging_product'] ?? null,
                 'object' => $requestData['messaging_product'] ?? null,
                 'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                 'contactName' => $registro->contactName ?? null,
                 'recipient_id' => $requestData['to'] ?? null,
                 'type' => $requestData['type'] ?? null,
                 'messagesType' => $requestData['type'] ?? null,
                 'body' => $requestData['text']['body'] ?? null,
                 'status' => 'sent' ?? null,
                 'user_atendimento' => Auth::user()->email,
             ]);
            }
             return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone));
    }


    public static  function cancelartransferiratendimento($id,$UsuarioID)
    {
             $usuario = trim(Auth::user()->email);
             $User = user::where('email',$UsuarioID)->first();

             $webhootContact = webhookcontact::find($id);

             $NomeAtendente = $User->name;

            $WebhookConfig =  WebhookConfig::Where('ativado','1')->OrderBy('usuario')->get()->first();

             $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
             $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
             $Token = $WebhookConfig->token24horas;

             $webhootContact = webhookcontact::find($id);

            $client = new Client();
            $phone = $webhootContact->recipient_id; // Número de telefone de destino
            $client = new Client();
            $requestData = [];

             $message = "A transferência para o usuário " . $webhootContact->transferido_para
      . " foi cancelada. Continue com o atendente " . $usuario . "(comigo). Caso queira é só enviar alguma nova mensagem. Obrigado!";

      if($webhootContact->user_atendimento !== Auth::user()->email)
      {
       $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ")";
      }

        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];
    $response = $client->post(
     'https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages',
     [

                 'headers' => [
                     'Authorization' => 'Bearer ' . $Token,
                     'Content-Type' => 'application/json',
                 ],
                 'json' => $requestData,
             ]
         );
         // Verifique a resposta
         if ($response->getStatusCode() == 200) {
             $responseData = json_decode($response->getBody());
             // Faça algo com a resposta, se necessário
             // dd("Mensagem nova enviada", $responseData);
  ///////////////////Gravar
            //  $registro = webhookContact::where('recipient_id', $phone)->get()->first();
            $registro =  $webhootContact;

             $registro->update([
              'status_mensagem_enviada' => 0,
              'user_updated' => $usuario,
              'quantidade_nao_lida' => $registro->quantidade_nao_lida+1,
              'transferido_para' => null,
            ]);


            $registro->save();

             $newWebhook = webhook::create([
                 'webhook' =>  null,
                 'value_messaging_product' => $requestData['messaging_product'] ?? null,
                 'object' => $requestData['messaging_product'] ?? null,
                 'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                 'contactName' => $registro->contactName ?? null,
                 'recipient_id' => $requestData['to'] ?? null,
                 'type' => $requestData['type'] ?? null,
                 'messagesType' => $requestData['type'] ?? null,
                 'body' => $requestData['text']['body'] ?? null,
                 'status' => 'sent' ?? null,
                 'user_atendimento' => Auth::user()->email,
             ]);
            }
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone));
    }


    public static  function avisotransferiratendimento($id, $UsuarioID, $NomeAtendido, $idatendido)
    {
             $usuario = trim(Auth::user()->email);
             $User = user::where('whatsapp',$UsuarioID)->first();
             $NomeAtendente = $User->name;

             $WebhookConfig =  WebhookConfig::Where('ativado','1')->OrderBy('usuario')->get()->first();

             

             $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
             $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
             $Token = $WebhookConfig->token24horas;
             $webhootContact = webhookcontact::find($idatendido);

                $client = new Client();
                $phone = $User->whatsapp;
                $client = new Client();
                $requestData = [];

                $message = "Foi transferido para você, " . $NomeAtendente.  " um atendimento  "
                 .  ". Contato de nome " . $NomeAtendido . ", aguardando. Obrigado!";

                 if($webhootContact->user_atendimento !== Auth::user()->email)
                 {
                  $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ")";

                 }

        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];
    $response = $client->post(
     'https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages',
     [
                 'headers' => [
                     'Authorization' => 'Bearer ' . $Token,
                     'Content-Type' => 'application/json',
                 ],
                 'json' => $requestData,
             ]
         );
         
         if ($response->getStatusCode() == 200) {

             $responseData = json_decode($response->getBody());
             // Faça algo com a resposta, se necessário
             // dd("Mensagem nova enviada", $responseData);
  ///////////////////Gravar
            //  $registro = webhookContact::where('recipient_id', $phone)->get()->first();
            $registro =  $webhootContact;

             $registro->update([
              'status_mensagem_enviada' => 0,
              'user_updated' => $usuario,
              'quantidade_nao_lida' => $registro->quantidade_nao_lida+1,
            ]);
            $registro->save();

             $newWebhook = webhook::create([
                 'webhook' =>  null,
                 'value_messaging_product' => $requestData['messaging_product'] ?? null,
                 'object' => $requestData['messaging_product'] ?? null,
                 'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                 'contactName' => $registro->contactName ?? null,
                 'recipient_id' => $requestData['to'] ?? null,
                 'type' => $requestData['type'] ?? null,
                 'messagesType' => $requestData['type'] ?? null,
                 'body' => $requestData['text']['body'] ?? null,
                 'status' => 'sent' ?? null,
                 'user_atendimento' => Auth::user()->email,
             ]);
            }
             return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone));
    }


    public static  function avisocancelamentotransferiratendimento($id, $UsuarioID )
    {
             $usuario = trim(Auth::user()->email);
             $User = user::where('email',$UsuarioID)->first();
             $NomeAtendente = $User->name;


            $WebhookConfig =  WebhookConfig::Where('ativado','1')->OrderBy('usuario')->get()->first();

             $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
             $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
             $Token = $WebhookConfig->token24horas;


             $webhootContact = webhookcontact::find($id);


         $client = new Client();
         $phone = $User->whatsapp;
         $client = new Client();
         $requestData = [];

     $message = "Foi cancelado um atendimento transferido para você, " . $NomeAtendente .    ". Obrigado!";

                 if($webhootContact->user_atendimento !== Auth::user()->email)
                 {
                  $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ")";

                 }

     // ===================================== somente texto como resposta
        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];


    // =================================================================

    $response = $client->post(
     'https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages',
     [

                 'headers' => [
                     'Authorization' => 'Bearer ' . $Token,
                     'Content-Type' => 'application/json',
                 ],
                 'json' => $requestData,
             ]
         );
         // Verifique a resposta
         if ($response->getStatusCode() == 200) {

             $responseData = json_decode($response->getBody());
             // Faça algo com a resposta, se necessário
             // dd("Mensagem nova enviada", $responseData);



  ///////////////////Gravar
            //  $registro = webhookContact::where('recipient_id', $phone)->get()->first();
            $registro =  $webhootContact;

             $registro->update([
              'status_mensagem_enviada' => 0,
              'user_updated' => $usuario,
              'quantidade_nao_lida' => $registro->quantidade_nao_lida+1,
            ]);


            $registro->save();

             $newWebhook = webhook::create([
                 'webhook' =>  null,
                 'value_messaging_product' => $requestData['messaging_product'] ?? null,
                 'object' => $requestData['messaging_product'] ?? null,
                 'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                 'contactName' => $registro->contactName ?? null,
                 'recipient_id' => $requestData['to'] ?? null,
                 'type' => $requestData['type'] ?? null,
                 'messagesType' => $requestData['type'] ?? null,
                 'body' => $requestData['text']['body'] ?? null,
                 'status' => 'sent' ?? null,
                 'user_atendimento' => Auth::user()->email,
             ]);
            }

             return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone));
    }

    public static  function VerificaSessao(string $AvisoTransferencia, $idcontato )
    {
        $id = $AvisoTransferencia;

        $UsuarioID =  $id;
        $Atendido =  webhookContact::where('id', $idcontato)
        ->OrderBy('updated_at', 'desc')
        ->get()->first();

        $idatendido = $Atendido->id;
        $NomeAtendido = $Atendido->contactName;
        $NomeTransferido =  webhookContact::where('recipient_id', $id)
        ->OrderBy('updated_at', 'desc')
        ->get()->first();

        $tempo_em_segundos  = null;
        $tempo_em_horas = null;
        $tempo_em_minutos = null;
        if($NomeTransferido->timestamp)
        {
            $tempo_em_segundos = strtotime(now()) - $NomeTransferido->timestamp;
                        $tempo_em_horas = $tempo_em_segundos / 3600;
                        $tempo_em_minutos = $tempo_em_segundos / 60;
        }
        $numero = $tempo_em_horas;
        // Separar valores antes e depois do ponto
        $partes = explode('.', $numero);

        // Atribuir partes
        $parte_inteira = (int)$partes[0];
        $parte_decimal = isset($partes[1]) ? (float)('0.' . $partes[1]) : 0;

        // dd('parte inteira: ' . $parte_inteira, 'parte decimal: ' . $parte_decimal);

        if($parte_inteira>23){
            $Avisa = WebhookServico::Avisaparaatender($id, $NomeAtendido );
        }
        else
        {
           $TransfereAvisa = WebhookServico::avisotransferiratendimento($id, $UsuarioID,$NomeAtendido, $idatendido);
        }
        return $parte_inteira;
    }


    public static function Avisaparaatender($recipient_id, $NomeAtendido)
    {

        $accessToken = WebhookServico::Token24horas();
       $WebhookConfig =  WebhookConfig::Where('ativado','1')->OrderBy('usuario')->get()->first();
        $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
        // $Token = $WebhookConfig->token24horas;
        $template = WebhookTemplate::where('id', '9')
        ->get()
        ->first();
        $name = $template->name;
        $language = $template->language;
        $message = $template->texto;

        $client = new Client();
        $phone = $recipient_id;
        $client = new Client();
        $requestData = [];

        $requestData = [
            'messaging_product' => 'whatsapp', // Adicione o parâmetro messaging_product com um valor válido
            'to' => $recipient_id, // Número de telefone de destino
            'type' => 'template',
            'template' => [
                'name' => $name,
                'language' => [
                    'code' => $language,
                ],
            ],
        ];

        $response = $client->post(
            'https://graph.facebook.com/v17.0/' . $phone_number_id . '/messages',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
            ]
        );
        if ($response->getStatusCode() == 200) {

            $newWebhook = webhook::create([
                'webhook' =>  null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'contactName' => '',
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'messagesType' => 'template',
                'body' => $message . ' Atender: '. trim($NomeAtendido),
                'status' => 'sent' ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);
           }
    }



}
