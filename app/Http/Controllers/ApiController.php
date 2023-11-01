<?php

namespace App\Http\Controllers;

use App\Models\webhook;
use App\Models\webhookContact;
use App\Models\WebhookTemplate;
use App\Models\WebhookConfig;
use App\Services\WebhookServico;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Calculation\Web;

class ApiController extends Controller
{
    //
    public function salvararquivoPostWebhook()
    {
        $storagePath = storage_path();
        $arquivo = "/app/PostWebhook.log";
        // Dados que você deseja salvar no arquivo de log
        $logData = "Mensagem de log: " . date('Y-m-d H:i:s') . " - Informação importante.\n";

        // Caminho para o arquivo de log
        $logFilePath = $storagePath . $arquivo;

        // Tente gravar os dados no arquivo de log
        if (file_put_contents($logFilePath, $logData, FILE_APPEND | LOCK_EX)) {
            echo "Dados gravados com sucesso no arquivo de log.";
        } else {
            echo "Erro ao gravar no arquivo de log.";
        }
        // dd("Verifique se salvou em /storage/app/contabilidade/PostWebhook.log ");

        $fileContent = file_get_contents(storage_path($arquivo));
        dd($fileContent);
    }

    public function index(Request $request)
    {
        $data = $request->all();

        $request_type = $request->method();

        //   Log::info($data);
        // return ;
        // return $data('hub_challenge');
        //////////////////////////////////////////////////////////////////////////
        $mergedData = array();
        $Achou = null;
        $entry_id = null;
        $entry_time = null;
        $object = null;
        $text = null;
        $body = null;
        $profile = null;
        $contactName = null;
        $filename = null;
        $caption = null;
        $waId = null;
        $body = null;
        $mime_type = null;
        $image_id = null;
        $image_sha256 = null;
        $image_mime_type = null;
        $statuses = null;
        $status =  'received';
        $recipient_id =  null;
        $conversation_id = null;
        $messages_id = null;
        $event =   null;
        $message_template_id =   null;
        $message_template_name =  null;
        $message_template_language =   null;
        $reason =   null;
        $messagesType = null;
        $messagesTimestamp = null;
        $messagesFrom = null;
        $context_From = null;
        $context_Id = null;
        $messages_ButtonPayload =  null;
        $messages_ButtonText = null;
        $changes_field = null;
        $value_messaging_product = null;
        $value = null;
        $changes_value_metadata_display_phone_number = null;
        $changes_value_metadata_phone_number_id = null;
        $changes_value_ban_info_waba_ban_state = null;
        $changes_value_ban_info_waba_ban_date = null;


        $object = $data['object'] ?? null;
        $entry = $data['entry'][0] ?? null;


        if ($entry) {

            $entry_id = $entry['id'] ?? null;
            $entry_time = $entry['time'] ?? null;

            $changes = $entry['changes'][0] ?? null;


            $image =  $data['entry'][0]['changes'][0]['value']['messages'][0]['image'] ?? null;
            if($image)
            {
                $image_sha256 = $image['sha256'];
                $image_id = $image['id'];

            }

            if ($changes) {
                $value = $changes['value'] ?? null;
                $changes_field = $changes['field'] ?? null;
                $contactName = $contactName ?? null;
                $waId = $waId ?? null;
                $body = $body ?? null;
                $text = $text ?? null;
                $mime_type = $mime_type ?? null;
                $filename = $filename ?? null;
                $image_mime_type = $image_mime_type ?? null;
                $caption = $caption ?? null;
                $status = $status ?? null;
                $recipient_id = $recipient_id ?? null;
                $conversation_id = $conversation_id ?? null;
                $messages_id = $messages_id ?? null;

                $contacts = $value['contacts'][0] ?? null;
                $messages = $value['messages'][0] ?? null;
                $statuses = $value['statuses'][0] ?? null;
                $ban_info = $value['ban_info'] ?? null;

                if ($messages) {
                    $messages_id = $messages['id'] ?? null;
                    $text = $messages['text'] ?? null;
                    // Log::info($text);
                    $body = $text['body'] ?? null;
                    $document = $messages['document'] ?? null;

                    $filename = $document['filename'] ?? null;
                    $mime_type = $document['mime_type'] ?? null;
                    $image = $messages['image']  ?? null;

                    $image_mime_type = $image['mime_type'] ?? null;

                    $caption = $image['caption'] ?? null;

                    $messagesFrom = $messages['from'] ?? null;
                    $messagesTimestamp = $messages['timestamp'] ?? null;
                    $messagesType = $messages['type'] ?? null;

                    $context = $messages['context'] ?? null;
                    if ($context !== null) {
                        $context_From = $context['from'] ?? null; // Acessa o campo 'payload' no botão
                        $context_Id = $context['id'] ?? null; // Acessa o campo 'text' no botão
                    }

                    $button = $messages['button'] ?? null; // Acessa o campo 'button' no array
                    if ($button !== null) {
                        $messages_ButtonPayload = $button['payload'] ?? null; // Acessa o campo 'payload' no botão
                        $messages_ButtonText = $button['text'] ?? null; // Acessa o campo 'text' no botão
                    }
                }

                if ($contacts) {
                    $profile = $contacts['profile'] ?? null;
                    $contactName = $profile['name'] ?? null;
                    $waId = $contacts['wa_id'] ?? null;
                }

                if ($statuses) {
                    $status = $statuses['status'] ?? null;
                    $recipient_id = $statuses['recipient_id'] ?? null;
                    $conversation_id = $statuses['id'] ?? null;
                }

                if ($value) {
                    $value_messaging_product = $value['messaging_product'] ?? null;
                    $event = $value['event'] ?? null;
                    $message_template_id = $value['message_template_id'] ?? null;
                    $message_template_name = $value['message_template_name'] ?? null;
                    $message_template_language = $value['message_template_language'] ?? null;
                    $reason = $value['reason'] ?? null;
                    $metadata = $value['metadata'] ?? null;
                    $changes_value_metadata_display_phone_number = $metadata['display_phone_number'] ?? null;
                    $changes_value_metadata_phone_number_id = $metadata['phone_number_id'] ?? null;
                    $ban_info = $value['ban_info'] ?? null;
                    $changes_value_ban_info_waba_ban_state = $ban_info['waba_ban_state'] ?? null;
                    $changes_value_ban_info_waba_ban_date =  $ban_info['waba_ban_date'] ?? null;
                }
            }
        }
        //////////////////////////////////////////////////////////////////////////
        if ($status == null) {
            $status = 'received';
        }


        // $messagesType = "button1";

        // Log::info($text);
        $dataString = $data;

        $jsonData = json_encode($data);
        Log::info($jsonData);

        $storagePath = storage_path();
        $arquivo = "/app/PostWebhook.log";
        $logData =   "=================================================\n"
            . "Mensagem de log: " . date('Y-m-d H:i:s') . "\n" . "_________________________________________________\n"
            . "webhook: " . $jsonData  . "\n"
            . "object: " . $object . "\n"
            . "messaging_product: " . $value_messaging_product . "\n"
            . "entry_id: " . $entry_id . "\n"
            . "entry_time: " . $entry_time . "\n"
            . "type: " . $request_type . "\n"
            . "contactName: " . $contactName . "\n"
            . "waId: " . $waId . "\n"
            . "body: " . $body . "\n"
            . "text: " . $body . "\n"
            . "mime_type: " . $mime_type . "\n"
            . "filename: " . $filename . "\n"
            . "image_id: " . $image_id . "\n"
            . "image_sha256: " . $image_sha256 . "\n"
            . "image_mime_type: " . $image_mime_type . "\n"
            . "caption: " . $caption . "\n"
            . "status: " . $status . "\n"
            . "recipient_id: " . $recipient_id . "\n"
            . "conversation_id: " . $conversation_id . "\n"
            . "MessagesId: " . $messages_id . "\n"
            . "MessagesType: " . $messagesType . "\n"
            . "MessagesFrom: " . $messagesFrom . "\n"
            . "MessagesTimestamp: " . $messagesTimestamp . "\n"
            . "changes_field: " . $changes_field . "\n"
            . "event: " . $event . "\n"
            . "message_template_id: " . $message_template_id . "\n"
            . "message_template_name: " . $message_template_name . "\n"
            . "message_template_language: " . $message_template_language . "\n"
            . "reason: " . $reason . "\n"
            . "context_From: " . $context_From . "\n"
            . "context_Id: " . $context_Id . "\n"
            . "messages_ButtonPayload: " . $messages_ButtonPayload . "\n"
            . "messages_ButtonText: " . $messages_ButtonText . "\n"
            . "changes_value_metadata_display_phone_number: " . $changes_value_metadata_display_phone_number . "\n"
            . "changes_value_metadata_phone_number_id: " . $changes_value_metadata_phone_number_id . "\n"
            . "changes_value_ban_info_waba_ban_state: " . $changes_value_ban_info_waba_ban_state . "\n"
            . "changes_value_ban_info_waba_ban_date:  " . $changes_value_ban_info_waba_ban_date . "\n"
            . "=================================================\n";

        // Caminho para o arquivo de log
        $logFilePath = $storagePath . $arquivo;

        // Tente gravar os dados no arquivo de log
        if (file_put_contents($logFilePath, $logData, FILE_APPEND | LOCK_EX)) {
            // echo "Dados gravados com sucesso no arquivo de log.";
        } else {
            // echo "Erro ao gravar no arquivo de log.";
        }
        // dd("Verifique se salvou em /storage/app/contabilidade/PostWebhook.log ");

        // $fileContent = file_get_contents(storage_path($arquivo ));
        // dd($fileContent);

        ///////////////////////////////////////////// GRAVAR EM BANCO DE DADOS
        //////////// Se acrescentar campos para gravar em BD,
        ///// lembrar de também inserir no model webhook
        ////// e colocar somente  ' = apóstrofo no webhook.
        ////// CUIDADO... NÃO COLOCAR ASPAS = "
        ////// E AQUI ABAIXO TAMBÉM


        $newWebhookContact = WebhookServico::AtualizaOuCriaWebhookContact($recipient_id, $contactName);




        $Achou = webhook::where('status',$status)
        ->where('messages_id',$messages_id)
        ->where('conversation_id',$conversation_id)
        ->first();

        if ($Achou
            && $Achou->status === $status
            && $Achou->messages_id === $messages_id
            && $Achou->conversation_id === $conversation_id) {
            Log::info('===============>>>> Achei um registro');
            Log::info('Telefone - waId = '. $waId);
            Log::info('Status - status = '. $status);
            Log::info('body - body = '. $body);
            Log::info('recipient_id - recipient_id = '. $recipient_id);
            Log::info('messages_id - messages_id = '. $messages_id);
            Log::info('messagesFrom - messagesFrom = '. $messagesFrom);
            return;
         }

// if($status == 'read'){
//             $WebhookConfig =  WebhookConfig::OrderBy('usuario')->get()->first();
//             $accessToken = $WebhookConfig->token24horas;
//             $leumensagem = WebhookServico::
//             Agradecimento_por_ter_lido_mensagem_recebida($recipient_id, $accessToken);
//         }

            if($status == 'read'){
                Log::info('===============>>>> READ');


            }

            $newWebhook = webhook::create([
            'webhook' => $jsonData ?? null,
            'entry_id' => $entry_id ?? null,
            'entry_time' => $entry_time ?? null,
            'object' => $object ?? null,
            'value_messaging_product' => $value_messaging_product ?? null,
            'type' => $request_type ?? null,
            'contactName' => $contactName ?? null,
            'waId' => $waId ?? null,
            'body' => $body ?? null,
            'text' => $text ?? null,
            'image_id' => $image_id ?? null,
            'image_sha256' => $image_sha256 ?? null,
            'mime_type' => $mime_type ?? null,
            'filename' => $filename ?? null,
            'image_id' => $image_id,
            'image_sha256'  => $image_sha256,
            'image_mime_type' => $image_mime_type ?? null,
            'caption' => $caption ?? null,
            'status' => $status ?? null,
            'recipient_id' => $recipient_id ?? null,
            'conversation_id' => $conversation_id ?? null,
            'messagesType' => $messagesType ?? null,
            'messages_id' => $messages_id ?? null,
            'messagesFrom' => $messagesFrom ?? null,
            'context_From' => $context_From ?? null,
            'context_Id' => $context_Id ?? null,
            'messages_ButtonPayload' => $messages_ButtonText ?? null,
            'messages_ButtonText' => $messages_ButtonText ?? null,
            'messagesTimestamp' => $messagesTimestamp ?? null,
            'changes_field' => $changes_field ?? null,
            'event' => $event ?? null,
            'message_template_id' => $message_template_id ?? null,
            'message_template_name' => $message_template_name ?? null,
            'message_template_language' => $message_template_language ?? null,
            'reason' => $reason ?? null,
            'changes_value_metadata_display_phone_number' => $changes_value_metadata_display_phone_number ?? null,
            'changes_value_metadata_phone_number_id' => $changes_value_metadata_phone_number_id ?? null,
            'changes_value_ban_info_waba_ban_state' => $changes_value_ban_info_waba_ban_state ?? null,
            'changes_value_ban_info_waba_ban_date' => $changes_value_ban_info_waba_ban_date ?? null,
            ]
            );

            Log::info('Inseri registro no bd: ' . $body);


        // $recipient_id = $recipient_id;
        // $contactName = $contactName;




        $value = $request['hub_challenge'];
        return response($value);
    }

    public function enviarMensagemNova()
    {
        // $accessToken = 'EAALZBJb4ieTcBO8Yemzg41ZASqQgq3KsH3ve15cW8DzWBtPnobeDW6uaJeOO5hfQ8yMZBJlsBuHDecUGeYrlAAhZAorUnOOJHfRJ5wqvUdAEOCJsLfvZC9EZBFZCQAOTtr0hheg3SAZA88Q0aK9EX6NMqygeRy9WDps094Rxhzx6mGmEsBr7EzZCeEls6uvrp9WlfmzMZCvvDZCMduMZAXLjio4ZBkzAIktiCzzvMysWpQDqZC1L9Ia94s9ZBhY'; // Substitua pelo seu token de acesso
        // $accessToken = 'EAAFPacE8OhcBO2ZCOyNEyeLuFG1s1gZCZBwTgwZBMgLpdtgMRVulaGVzo1ZB1Eddd5tq3ZCUvoO2CtsZB6rniI6VVbVQ9XHe5zJBZB5ARFVqGINLVtUC0RZBI5M3LOQrWZCrQsRHjaPPaWljZCftlv3GKZB0UpSTbWLbAXSqZC0cnCer2ge0lqlFRx7uEaZBzsrZBol2XjyuexEzlt2ceTPNBytXEn9m7MsNnchDHvrYw0ZD';

        $accessToken = 'EAAFPacE8OhcBOz023aCrHJFZCNZCX3qqQ8D7gaV1UqVCyvwyrIeQsvEDGGAAIZAaHO03fLImmHUInHWjzqJIrOQdPaRFy4ZCLp2ZAZApzpQfcXM63h0HvFwfAUVpdFclgS5UnmtJ7C2Dsbby26EcdiK80QeDffnTZAGM6JiwExhs1ICxzHVZCKgdZAgQCVWhbn3viLAyepcsjRTa4k8YoUKOMAWzs1uEaKxhlRw2LFuC1J41b6obhfrgc';

        $client = new Client();
        $phone = '5517997662949'; // Número de telefone de destino
        $message = 'Esta é uma mensagem LIVRE livre de teste'; // Sua mensagem de texto
        $client = new Client();

        $requestData = [];
        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone, // Número de telefone de destino
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



        // Verifique a resposta
        if ($response->getStatusCode() == 200) {
            $responseData = json_decode($response->getBody());
            // Faça algo com a resposta, se necessário
            // dd("Mensagem nova enviada", $responseData);

            ///////////////////Gravar
            /////////////// gravar mensagem aprovada
            $newWebhook = webhook::create([
                'webhook' => json_encode($requestData) ?? null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'body' => $requestData['text']['body'] ?? null,
                'status' => 'sent' ?? null,
            ]);

            $recipient_id = $requestData['to'];
            $contactName = null;
            $newWebhookContact = WebhookServico::updateOrCreateWebhookContact($recipient_id, $contactName);



            return redirect(route('whatsapp.indexlista'));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }


    public function enviarMensagemAprovadaAriane()
    {

        $accessToken = 'EAALZBJb4ieTcBO8Yemzg41ZASqQgq3KsH3ve15cW8DzWBtPnobeDW6uaJeOO5hfQ8yMZBJlsBuHDecUGeYrlAAhZAorUnOOJHfRJ5wqvUdAEOCJsLfvZC9EZBFZCQAOTtr0hheg3SAZA88Q0aK9EX6NMqygeRy9WDps094Rxhzx6mGmEsBr7EzZCeEls6uvrp9WlfmzMZCvvDZCMduMZAXLjio4ZBkzAIktiCzzvMysWpQDqZC1L9Ia94s9ZBhY'; // Substitua pelo seu token de acesso

        $client = new Client();

        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => '5517996733342', // Número de telefone de destino
            'type' => 'template',
            'template' => [
                'name' => 'agradecimento_pelo_contato',
                'language' => [
                    'code' => 'pt_BR',
                ],
            ],
        ];



        $response = $client->post('https://graph.facebook.com/v17.0/157689817424024/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);

        // Verifique a resposta
        if ($response->getStatusCode() == 200) {
            $responseData = json_decode($response->getBody());
            // Faça algo com a resposta, se necessário
            // dd("Mensagem aprovada enviada", $responseData);
            return redirect(route('whatsapp.indexlista'));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }

    public function enviarMensagemAprovadaAngelica()
    {

        $accessToken = 'EAALZBJb4ieTcBO8Yemzg41ZASqQgq3KsH3ve15cW8DzWBtPnobeDW6uaJeOO5hfQ8yMZBJlsBuHDecUGeYrlAAhZAorUnOOJHfRJ5wqvUdAEOCJsLfvZC9EZBFZCQAOTtr0hheg3SAZA88Q0aK9EX6NMqygeRy9WDps094Rxhzx6mGmEsBr7EzZCeEls6uvrp9WlfmzMZCvvDZCMduMZAXLjio4ZBkzAIktiCzzvMysWpQDqZC1L9Ia94s9ZBhY'; // Substitua pelo seu token de acesso

        $client = new Client();

        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => '5517997470064', // Número de telefone de destino
            'type' => 'template',
            'template' => [
                'name' => 'agradecimento_pelo_contato',
                'language' => [
                    'code' => 'pt_BR',
                ],
            ],
        ];



        $response = $client->post('https://graph.facebook.com/v17.0/125892007279954/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);

        // Verifique a resposta
        if ($response->getStatusCode() == 200) {
            $responseData = json_decode($response->getBody());
            // Faça algo com a resposta, se necessário
            // dd("Mensagem aprovada enviada", $responseData);
            return redirect(route('whatsapp.indexlista'));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }


    public function indexlista()
    {
        date_default_timezone_set('UTC');
        $model = webhook::orderBy("id", "desc")->get();
        // $model = webhook::where("id",158)->orderBy("id", "desc")->get();


        $mergedData = array(); // Inicialize o array $mergedData fora do loop foreach

        foreach ($model as $registro) {
            $profile = null;
            // $contactName = null;
            // $waId = null;
            $from = null;
            $body = null;
            $document = null;
            $filename = null;
            $mime_type = null;
            $image_mime_type = null;
            $statuses = null;
            $entry_id = null;
            $entry_time = null;
            $object = null;

            $changes_metadata_value_display_phone_number = null;
            $changes_metadata_value_phone_number_id = null;
            // $status =  null;
            // $recipient_id =  null;
            // // $conversation_id = null;
            // $messages_id = null;

            $jsonData = $registro->webhook;
            $data = json_decode($jsonData, true);

            $object = $data['object'] ?? null;
            $entry = $data['entry'][0] ?? null;

            // dd($data);

            if ($entry) {
                $entry_id = $entry['id'] ?? null;
                $entry_time = $entry['time'] ?? null;

                $changes = $entry['changes'][0] ?? null;
                // dd($data,  $object, $entry_id, $entry_time);

                if ($changes) {
                    $value = $changes['value'] ?? null;
                    $changes_field = $changes['field'] ?? null;
                    $contacts = $value['contacts'][0] ?? null;
                    $messages = $value['messages'][0] ?? null;
                    $context = $messages['context'][0] ?? null;

                    if ($messages) {
                        $messages_id = $messages['id'] ?? null;
                        $text = $messages['text'] ?? null;
                        $body = $text['body'] ?? null;
                        $document = $messages['document'] ?? null;

                        $filename = $document['filename'] ?? null;
                        $mime_type = $document['mime_type'] ?? null;
                        $image = $messages['image']  ?? null;

                        $image_mime_type = $image['mime_type'] ?? null;

                        $caption = $image['caption'] ?? null;

                        $messagesFrom = $messages['from'] ?? null;
                        $messagesTimestamp = $messages['timestamp'] ?? null;
                        $messagesType = $messages['type'] ?? null;


                        $context = $messages['context'] ?? null;
                        if ($context !== null) {
                            $context_From = $context['from'] ?? null; // Acessa o campo 'payload' no botão
                            $context_Id = $context['id'] ?? null; // Acessa o campo 'text' no botão
                        }

                        $button = $messages['button'] ?? null; // Acessa o campo 'button' no array

                        if ($button !== null) {
                            $button_payload = $button['payload'] ?? null; // Acessa o campo 'payload' no botão
                            $button_text = $button['text'] ?? null; // Acessa o campo 'text' no botão
                        }

                        // dd($messages,$context_From, $context_Id);

                        // dd($entry, $context, $button, $button_payload, $button_text);
                        //  dd($entry, $context, $button);

                    }
                    if ($contacts) {
                        $profile = $contacts['profile'] ?? null;
                        // $contactName = $profile['name'] ?? null;
                        // $waId = $contacts['wa_id'] ?? null;
                    }
                    if ($statuses) {
                        $status = $statuses['status'] ?? null;
                        // $recipient_id = $statuses['recipient_id'] ?? null;
                        // $conversation_id = $statuses['id'] ?? null;
                    }
                    if ($value) {
                        $value_messaging_product = $value['messaging_product'] ?? null;
                        $event = $value['event'] ?? null;
                        $message_template_id = $value['message_template_id'] ?? null;
                        $message_template_name = $value['message_template_name'] ?? null;
                        $message_template_language = $value['message_template_language'] ?? null;
                        $reason = $value['reason'] ?? null;
                        $ban_info = $value['ban_info'] ?? null;
                        $metadata = $value['metadata'] ?? null;

                        $changes_metadata_value_display_phone_number = $metadata['display_phone_number'] ?? null;
                        $changes_metadata_value_phone_number_id = $metadata['phone_number_id'] ?? null;

                        $changes_value_ban_info_waba_ban_state = $ban_info['waba_ban_state'] ?? null;
                        $changes_value_ban_info_waba_ban_date =  $ban_info['waba_ban_date'] ?? null;


                        // DD($value,  $metadata, $changes_metadata_value_display_phone_number, $changes_metadata_value_phone_number_id );
                        // dd($entry, $event, $ban_info, $changes_value_ban_info_waba_ban_state,  $changes_value_ban_info_waba_ban_date );
                    }
                    $newData = [
                        // "contactName" => $contactName ?? null,
                        // "waId" => $waId ?? null,
                        "body" => $body ?? null,
                        "text" => $text ?? null,
                        "mime_type" => $mime_type ?? null,
                        "filename" => $filename ?? null,
                        "image_mime_type" => $image_mime_type ?? null,
                        "caption" => $caption ?? null,
                        // "status" => $status ?? null,
                        // "recipient_id" => $recipient_id ?? null,
                        // "conversation_id" => $conversation_id ?? null,
                        // "messages_id" => $messages_id ?? null,
                        "field" => $field ?? null,
                        "event" => $event ?? null,
                        "message_template_id" => $message_template_id ?? null,
                        "message_template_name" => $message_template_name ?? null,
                        "message_template_language" => $message_template_language ?? null,
                        "reason" => $reason ?? null,
                        "changes_metadata_value_display_phone_number" => $changes_metadata_value_display_phone_number ?? null,
                        "changes_metadata_value_phone_number_id" => $changes_metadata_value_phone_number_id ?? null,



                        // "messagesFrom" => $messagesFrom ?? null,
                        // "messagesTimestamp" => $messagesTimestamp ?? null,
                        // "messagesType" => $messagesType ?? null,
                    ];
                    $mergedData[] = array_merge($registro->toArray(), $newData);
                }
            }
        }

        // $model =   $mergedData;

        return view('Api.indexlista', compact('model'));
    }

    public function registro(string $id)
    {

        $field = null;
        $messagingProduct = null;
        $metadata = null;
        $displayPhoneNumber = null;
        $phoneNumberId = null;
        $profile = null;
        $contactName = null;
        $waId = null;
        $from = null;
        $messageId = null;
        $body = null;
        $messageType = null;
        $status = null;
        $filename = null;
        $animated  = null;
        $mime_type = null;
        $sha256 = null;
        $iddocument  = null;
        $caption = null;
        $event = null;
        $message_template_id = null;
        $message_template_name = null;
        $message_template_language = null;
        $reason = null;

        $model = webhook::find($id);
        $cadastro_registro = $model;
        // $created_at = $model->created_at;

        $jsonData =  $model->webhook;
        $data = json_decode($jsonData, true); // Converte o JSON em um array associativo

        // Agora você pode acessar as variáveis da seguinte forma:
        $object = $data['object'] ?? null;

        $entry = $data['entry'][0] ?? null;

        $id = $entry['id'] ?? null;
        $changes = $entry['changes'][0] ?? null;

        $value = $changes['value'] ?? null;
        $field = $changes['field'] ?? null;

        $messagingProduct = $value['messaging_product'] ?? null;
        $metadata = $value['metadata'] ?? null;


        $displayPhoneNumber = $metadata['display_phone_number'] ?? null;
        $phoneNumberId = $metadata['phone_number_id'] ?? null;
        //  dd($data, $statuses );

        if (isset($value) && is_array($value) && count($value) > 0) {
            $event = $value['event'] ?? null;
            $message_template_id = $value['message_template_id']  ?? null;
            $message_template_name = $value['message_template_name']  ?? null;
            $message_template_language = $value['message_template_language']  ?? null;
            $reason = $value['event'] ?? null;
        }

        if (isset($value['statuses']) && is_array($value['statuses']) && count($value['statuses']) > 0) {
            $statuses = $value['statuses'][0];
            $status = $statuses['status'];
        }


        if (isset($value['contacts']) && is_array($value['contacts']) && count($value['contacts']) > 0) {
            $contacts = $value['contacts'][0];

            // Agora você pode acessar $contacts e outros campos dentro dele.
        }


        if (isset($value['messages']) && is_array($value['messages']) && count($value['messages']) > 0) {
            $messages = $value['messages'][0];
            if (isset($messages['text']) && is_array($messages['text']) && count($messages['text']) > 0) {
                $text = $messages['text'];
                $body = $text['body'];
            }

            $messageType = $messages['type'];
            $from = $messages['from'];
            $messageId = $messages['id'];
            $timestamp = $messages['timestamp'];
        }

        if (isset($messages['document']) && is_array($messages['document']) && count($messages['document']) > 0) {
            $document = $messages['document'];
            $filename = $document['filename'];
            $mime_type = $document['mime_type'];
            $sha256 = $document['sha256'];
            $iddocument = $document['id'];
        }

        if (isset($messages['image']) && is_array($messages['image']) && count($messages['image']) > 0) {
            $document = $messages['image'];

            $caption = $document['caption'] ?? null;
            // dd($data, $document,  $caption );


            $mime_type = $document['mime_type'];
            $sha256 = $document['sha256'];
            $iddocument = $document['id'];
        }

        if (isset($messages['sticker']) && is_array($messages['sticker']) && count($messages['sticker']) > 0) {

            $document = $messages['sticker'];
            $mime_type = $document['mime_type'];
            $sha256 = $document['sha256'];
            $iddocument = $document['id'];
            $animated = $document['animated'];
        }



        if (isset($contacts['profile']) && is_array($contacts['profile']) && count($contacts['profile']) > 0) {
            $profile = $contacts['profile'];
            $contactName = $profile['name'];
            $waId = $contacts['wa_id'];
            // Agora você pode acessar $contacts e outros campos dentro dele.
        }


        if (isset($contacts['profile']) && is_array($contacts['profile']) && count($contacts['profile']) > 0) {
            $field = $field ?? null;
            $event = $event ?? null;
            $message_template_id = $message_template_id ?? null;
            $message_template_name = $message_template_name ?? null;
            $message_template_language = $message_template_language ?? null;
            $reason = $reason ?? null;
        }



        $model = $data;

        // Chaves fornecidas
        $newKeys = [
            "webhook",
            "created_at",
            "field",
            "messagingProduct",
            "metadata",
            "displayPhoneNumber",
            "phoneNumberId",
            "profile",
            "contactName",
            "waId",
            "from",
            "messageId",
            "body",
            "messageType",
            "status",
            "filename",
            "animated",
            "mime_type",
            "sha256",
            "iddocument",
            "caption",
            "event",
            "message_template_id",
            "message_template_name",
            "message_template_language",
            "reason",
        ];

        // Crie um array associativo com chaves fornecidas e valores padrão (vazios)
        $newData = [];
        $newData["webhook"] =  $jsonData;
        // $newData["created_at"] =  $created_at;
        $newData["field"] = $field;
        $newData["messagingProduct"] = $messagingProduct;
        $newData["metadata"] = $metadata;
        $newData["displayPhoneNumber"] = $displayPhoneNumber;
        $newData["phoneNumberId"] = $phoneNumberId;
        $newData["profile"] = $profile;
        $newData["contactName"] = $contactName;
        $newData["waId"] = $waId;
        $newData["from"] = $from;
        $newData["messageId"] = $messageId;
        $newData["body"] = $body;
        $newData["messageType"] = $messageType;
        $newData["status"] = $status;
        $newData["filename"] = $filename;
        $newData["animated"] = $animated;
        $newData["mime_type"] = $mime_type;
        $newData["sha256"] = $sha256;
        $newData["iddocument"] = $iddocument;
        $newData["caption"] = $caption;
        $newData["event"] = $event;
        $newData["message_template_id"] = $message_template_id;
        $newData["message_template_name"] = $message_template_name;
        $newData["message_template_language"] = $message_template_language;
        $newData["reason"] = $reason;


        // $newData[""] = $;

        // Mesclar o novo array com o modelo existente
        // $mergedData = array_merge($data, $newData);

        // $modela = $mergedData;
        $model = $cadastro_registro;

        return view('Api.registro', compact(
            'model',
        ));
    }

    public function atualizaregistro(string $id)
    {

        $model = webhook::find($id);

        // dd(auth::user());
        $jsonData =  $model->webhook;
        $data = json_decode($jsonData, true);

        //////////////////////////////////////////////////////////////////////////
        $mergedData = array();
        $entry_id = null;
        $entry_time = null;
        $text = null;
        $caption = null;
        $object = null;
        $profile = null;
        $contactName = null;
        $waId = null;
        $body = null;
        $document = null;
        $filename = null;
        $mime_type = null;
        $image_id = null;
        $image_sha256 = null;
        $image_mime_type = null;
        $statuses = null;
        $status =  'received';
        $recipient_id =  null;
        $conversation_id = null;
        $messages_id = null;
        $event =   null;
        $message_template_id =   null;
        $message_template_name =  null;
        $message_template_language =   null;
        $reason =   null;
        $messagesType = null;
        $messagesTimestamp = null;
        $messagesFrom = null;
        $context_From = null;
        $context_Id = null;
        $messages_ButtonPayload =  null;
        $messages_ButtonText = null;
        $changes_field = null;
        $value_messaging_product = null;
        $changes_value_metadata_display_phone_number = null;
        $changes_value_metadata_phone_number_id = null;
        $changes_value_ban_info_waba_ban_state = null;
        $changes_value_ban_info_waba_ban_date = null;


        $object = $data['object'] ?? null;
        $entry = $data['entry'][0] ?? null;


        if ($entry) {
            $entry_id = $entry['id'] ?? null;
            $entry_time = $entry['time'] ?? null;

            $changes = $entry['changes'][0] ?? null;

            $image =  $data['entry'][0]['changes'][0]['value']['messages'][0]['image'] ?? null;


            if($image)
            {
                $image_sha256 = $image['sha256'];
                $image_id = $image['id'];

            }


            if ($changes) {
                $value = $changes['value'] ?? null;
                $changes_field = $changes['field'] ?? null;
                $contactName = $contactName ?? null;
                $waId = $waId ?? null;
                $body = $body ?? null;
                $text = $text ?? null;
                $mime_type = $mime_type ?? null;
                $filename = $filename ?? null;
                $image_mime_type = $image_mime_type ?? null;
                $caption = $caption ?? null;
                $status = $status ?? null;
                $recipient_id = $recipient_id ?? null;
                $conversation_id = $conversation_id ?? null;
                $messages_id = $messages_id ?? null;

                $contacts = $value['contacts'][0] ?? null;
                $messages = $value['messages'][0] ?? null;
                $statuses = $value['statuses'][0] ?? null;
                $ban_info = $value['ban_info'] ?? null;

                if ($messages) {
                    $messages_id = $messages['id'] ?? null;
                    $text = $messages['text'] ?? null;
                    $body = $text['body'] ?? null;
                    $document = $messages['document'] ?? null;

                    $filename = $document['filename'] ?? null;
                    $mime_type = $document['mime_type'] ?? null;
                    $image = $messages['image']  ?? null;

                    $image_mime_type = $image['mime_type'] ?? null;

                    $caption = $image['caption'] ?? null;

                    $messagesFrom = $messages['from'] ?? null;
                    $messagesTimestamp = $messages['timestamp'] ?? null;
                    $messagesType = $messages['type'] ?? null;

                    $context = $messages['context'] ?? null;
                    if ($context !== null) {
                        $context_From = $context['from'] ?? null; // Acessa o campo 'payload' no botão
                        $context_Id = $context['id'] ?? null; // Acessa o campo 'text' no botão
                    }

                    $button = $messages['button'] ?? null; // Acessa o campo 'button' no array
                    if ($button !== null) {
                        $messages_ButtonPayload = $button['payload'] ?? null; // Acessa o campo 'payload' no botão
                        $messages_ButtonText = $button['text'] ?? null; // Acessa o campo 'text' no botão
                    }
                }

                if ($contacts) {
                    $profile = $contacts['profile'] ?? null;
                    $contactName = $profile['name'] ?? null;
                    $waId = $contacts['wa_id'] ?? null;
                }

                if ($statuses) {
                    $status = $statuses['status'] ?? null;
                    $recipient_id = $statuses['recipient_id'] ?? null;
                    $conversation_id = $statuses['id'] ?? null;
                }

                if ($value) {
                    $value_messaging_product = $value['messaging_product'] ?? null;
                    $event = $value['event'] ?? null;
                    $message_template_id = $value['message_template_id'] ?? null;
                    $message_template_name = $value['message_template_name'] ?? null;
                    $message_template_language = $value['message_template_language'] ?? null;
                    $reason = $value['reason'] ?? null;
                    $metadata = $value['metadata'] ?? null;
                    $changes_value_metadata_display_phone_number = $metadata['display_phone_number'] ?? null;
                    $changes_value_metadata_phone_number_id = $metadata['phone_number_id'] ?? null;
                    $ban_info = $value['ban_info'] ?? null;
                    $changes_value_ban_info_waba_ban_state = $ban_info['waba_ban_state'] ?? null;
                    $changes_value_ban_info_waba_ban_date =  $ban_info['waba_ban_date'] ?? null;
                }
            }


        }

        //////////////////////////////////////////////////////////////////////////
        if ($status == null) {
            $status = 'received';
        }

        $atualiza = [
            // 'webhook' => $dataString ?? null,
            'user_updated' =>  auth::user()->email,
            'entry_id' => $entry_id ?? null,
            'entry_time' => $entry_time ?? null,
            'object' => $object ?? null,
            'value_messaging_product' => $value_messaging_product ?? null,
            // 'type' => $type?? null,
            'contactName' => $contactName ?? null,
            'waId' => $waId ?? null,
            'body' => $body ?? null,
            'text' => $text ?? null,
            'mime_type' => $mime_type ?? null,
            'filename' => $filename ?? null,
            'image_id' => $image_id ?? null,
            'image_sha256' => $image_sha256 ?? null,
            'image_mime_type' => $image_mime_type ?? null,
            'caption' => $caption ?? null,
            'status' => $status ?? null,
            'recipient_id' => $recipient_id ?? null,
            'conversation_id' => $conversation_id ?? null,
            'messagesType' => $messagesType ?? null,
            'messages_id' => $messages_id ?? null,
            'messagesFrom' => $messagesFrom ?? null,
            'context_From' => $context_From ?? null,
            'context_Id' => $context_Id ?? null,
            'messages_ButtonPayload' => $messages_ButtonText ?? null,
            'messages_ButtonText' => $messages_ButtonText ?? null,
            'messagesTimestamp' => $messagesTimestamp ?? null,
            'changes_field' => $changes_field ?? null,
            'event' => $event ?? null,
            'message_template_id' => $message_template_id ?? null,
            'message_template_name' => $message_template_name ?? null,
            'message_template_language' => $message_template_language ?? null,
            'reason' => $reason ?? null,
            'changes_value_metadata_display_phone_number' => $changes_value_metadata_display_phone_number ?? null,
            'changes_value_metadata_phone_number_id' => $changes_value_metadata_phone_number_id ?? null,
            'changes_value_ban_info_waba_ban_state' => $changes_value_ban_info_waba_ban_state ?? null,
            'changes_value_ban_info_waba_ban_date' => $changes_value_ban_info_waba_ban_date ?? null,
        ];

        $model->update($atualiza);


        $storagePath = storage_path();
        $arquivo = "/app/PostWebhook.log";
        $logData =   "=================================================\n"
            . "Mensagem de log: " . date('Y-m-d H:i:s') . "\n" . "_________________________________________________\n"
            // . "webhook: " . $data . "\n"
            . "object: " . $object . "\n"
            . "messaging_product: " . $value_messaging_product . "\n"
            . "entry_id: " . $entry_id . "\n"
            . "entry_time: " . $entry_time . "\n"
            // . "type: " . $request_type . "\n"
            . "contactName: " . $contactName . "\n"
            . "waId: " . $waId . "\n"
            . "body: " . $body . "\n"
            . "text: " . $body . "\n"
            . "mime_type: " . $mime_type . "\n"
            . "filename: " . $filename . "\n"
            . "image_mime_type: " . $image_mime_type . "\n"
            . "image_id: " . $image_id . "\n"
            . "image_sha256: " . $image_sha256 . "\n"
            . "image_mime_type: " . $image_mime_type . "\n"
            . "caption: " . $caption . "\n"
            . "status: " . $status . "\n"
            . "recipient_id: " . $recipient_id . "\n"
            . "conversation_id: " . $conversation_id . "\n"
            . "MessagesId: " . $messages_id . "\n"
            . "MessagesType: " . $messagesType . "\n"
            . "MessagesFrom: " . $messagesFrom . "\n"
            . "MessagesTimestamp: " . $messagesTimestamp . "\n"
            . "changes_field: " . $changes_field . "\n"
            . "event: " . $event . "\n"
            . "message_template_id: " . $message_template_id . "\n"
            . "message_template_name: " . $message_template_name . "\n"
            . "message_template_language: " . $message_template_language . "\n"
            . "reason: " . $reason . "\n"
            . "context_From: " . $context_From . "\n"
            . "context_Id: " . $context_Id . "\n"
            . "messages_ButtonPayload: " . $messages_ButtonPayload . "\n"
            . "messages_ButtonText: " . $messages_ButtonText . "\n"
            . "changes_value_metadata_display_phone_number: " . $changes_value_metadata_display_phone_number . "\n"
            . "changes_value_metadata_phone_number_id: " . $changes_value_metadata_phone_number_id . "\n"
            . "changes_value_ban_info_waba_ban_state: " . $changes_value_ban_info_waba_ban_state . "\n"
            . "changes_value_ban_info_waba_ban_date:  " . $changes_value_ban_info_waba_ban_date . "\n"
            . "=================================================\n";

        // Caminho para o arquivo de log
        $logFilePath = $storagePath . $arquivo;

        // Tente gravar os dados no arquivo de log
        if (file_put_contents($logFilePath, $logData, FILE_APPEND | LOCK_EX)) {
            // echo "Dados gravados com sucesso no arquivo de log.";
        } else {
            // echo "Erro ao gravar no arquivo de log.";
        }


        // dd("ATUALIZA OS REGISTROS");

        return redirect(route('whatsapp.indexlista'));
    }

    public function PreencherMensagemResposta(string $id)
    {

        $model = webhook::find($id);

        return view('api.registroresposta', compact('model'))->with(['id' => $id]);
    }

    public function enviarMensagemResposta(Request $request, $id)
    {


        $request->validate([
            'token_type' => 'required|in:token24horas,tokenpermanenteusuario',
        ]);
        $token = $request->token_type;


        $model = webhook::find($id);

        $message = $request->input('mensagem');

            if (empty($request->input('mensagem'))) {
                // O campo de mensagem está vazio, defina a mensagem de erro na sessão.
                session()->flash('MensagemNaoPreenchida', 'A mensagem está vazia... necessita de preenchimento!');
                return redirect()->back();
            }



            $WebhookConfig =  WebhookConfig::OrderBy('usuario')->get()->first();
            $accessToken = null;
            if ($token == 'token24horas') {
                $accessToken = $WebhookConfig->token24horas;
            } elseif ($token == 'tokenpermanenteusuario') {
                $accessToken = $WebhookConfig->tokenpermanenteusuario;
            }
            if($accessToken == null){
                session()
                ->flash('MensagemNaoPreenchida', 'Token não definido por algum erro. Verifique. Linha 1142!');
                return redirect()->back();
            }

        $client = new Client();
        $phone = $model->messagesFrom; // Número de telefone de destino
        $client = new Client();
        $requestData = [];
        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone, // Número de telefone de destino
            'type' => 'text',
            'text' => [
                // 'body' => 'Resposdendo o texto: '. $model->body . 'Resposta: ' .$message,
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
        // Verifique a resposta
        if ($response->getStatusCode() == 200) {
            $responseData = json_decode($response->getBody());
            // Faça algo com a resposta, se necessário
            // dd("Mensagem nova enviada", $responseData);

            ///////////////////Gravar
            /////////////// gravar mensagem aprovada
            $newWebhook = webhook::create([
                'webhook' => json_encode($requestData) ?? null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'contactName' => $model->contactName ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'body' => $requestData['text']['body'] ?? null,
                'status' => 'sent' ?? null,
            ]);

            $recipient_id = $requestData['to'];
            $contactName = $model->contactName;
            $newWebhookContact = WebhookServico::AtualizaOuCriaWebhookContact($recipient_id, $contactName);
            session()->flash('success', 'Mensagem enviada com sucesso para ' . $model->contactName .  '.');
            return redirect(route('whatsapp.indexlista'));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }

    public function SelecionarMensagemAprovada()
    {
        $contatos = webhookContact::where('recipient_id', '!=', null)
        ->orderBy('contactName')
        ->get();

        $template = WebhookTemplate::orderBy('Name')->get();


        return view('api.SelecionarMensagemAprovada', compact('contatos','template'));
    }


    public function enviarMensagemAprovada(Request $request)
    {

        $request->validate([
            'idcontato' => 'required',
            'idtemplate' => 'required',
            'token_type' => 'required|in:token24horas,tokenpermanenteusuario',
        ]);

        $token = $request->token_type;

        $efetuar = $request->all();

        // dd( $efetuar, $efetuar['idcontato'], $efetuar['idtemplate'] );

        $contatos = webhookContact::find($efetuar['idcontato']);
        $recipient_id = $contatos->recipient_id;
        $contactName = $contatos->contactName;

        $template = WebhookTemplate::find($efetuar['idtemplate']);

        $name = $template->name  ;
        $language = $template->language;

        $WebhookConfig =  WebhookConfig::OrderBy('usuario')->get()->first();

        if ($token == 'token24horas') {
            $accessToken = $WebhookConfig->token24horas;
        } elseif ($token == 'tokenpermanenteusuario') {
            // dd($WebhookConfig);
            $accessToken = $WebhookConfig->tokenpermanenteusuario;
        }

        $client = new Client();

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
            'https://graph.facebook.com/v17.0/147126925154132/messages',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
            ]
        );

        // Verifique a resposta

        if ($response->getStatusCode() == 200) {
            $responseData = json_decode($response->getBody());
            // Faça algo com a resposta, se necessário
            // dd("Mensagem aprovada enviada", $responseData);

            /////////////// gravar mensagem aprovada
            $newWebhook = webhook::create([
                'webhook' => json_encode($requestData) ?? null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'message_template_name' => $requestData['template']['name'] ?? null,
                'message_template_language' => $requestData['template']['language']['code'] ?? null,
                'status' => 'sent' ?? null,
            ]);

            /////////////////////////////// termina a gravação


            return redirect(route('whatsapp.indexlista'));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }

    public function Pegar_URL_Arquivo(string $id)
    {
        $accessToken = WebhookServico::token24horas();
        $client = new Client();
        $response = $client->get("https://graph.facebook.com/v18.0/".trim($id),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
             ]
        );

        if ($response->getStatusCode() == 200) {
            $responseData = json_decode($response->getBody());

            $response = $client->get(trim($responseData->url), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);


            $registrobd = webhook::where('image_id',$id)->first();

            $idtabela = $registrobd->id;
            $messages_id = $registrobd->messages_id;
            $value_messaging_product = $registrobd->value_messaging_product;

            $sufixo = null;
            if($registrobd->image_mime_type == 'image/jpeg'){
                $sufixo = '.jpg';
            }

            $file =
                 'registro_'.$idtabela
                .'_image_id_'.trim($id)
                .'_message_id_'.$messages_id
                .'_value_messaging_product_'.$value_messaging_product
                .$sufixo;

            // Definindo o caminho onde a imagem será salva
            $pastafisica = 'whatsapp/';

            if (!file_exists($pastafisica)) {
                // Verifique se a pasta não existe e, se não existir, crie-a
                if (mkdir($pastafisica, 0777, true)) {
                    echo 'A pasta foi criada com sucesso.';
                } else {
                    echo 'Não foi possível criar a pasta.';
                }
            } else {
                echo 'A pasta já existe.';
            }


            $filePath = $pastafisica. $file;  // Substitua pelo caminho desejado

            // Salva o conteúdo da resposta no arquivo
            file_put_contents($filePath, $response->getBody());



            return redirect('/file.jpg');

            //return redirect(route('whatsapp.Baixar_Arquivo',"$responseData->url"));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }



}
