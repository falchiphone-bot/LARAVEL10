<?php

namespace App\Http\Controllers;

use App\Models\webhook;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    //
    public function salvararquivoPostWebhook()
    {
                // Dados que você deseja salvar no arquivo de log
        $logData = "Mensagem de log: " . date('Y-m-d H:i:s') . " - Informação importante.\n";

        // Caminho para o arquivo de log
        $logFilePath = "/storage/app/PostWebhook.log";

        // Tente gravar os dados no arquivo de log
        if (file_put_contents($logFilePath, $logData, FILE_APPEND | LOCK_EX)) {
            echo "Dados gravados com sucesso no arquivo de log.";
        } else {
            echo "Erro ao gravar no arquivo de log.";

        }
        dd("Verifique se salvou em /storage/app/contabilidade/PostWebhook.log ");
    }

    public function index(Request $r)
    {

        // date_default_timezone_set('UTC');
        $data = $r->all();

        $request_type = $r->method();
        $dataString = json_encode($data);
//////////////////////////////////////////////////////////////////////////
    $mergedData = array();
    $profile = null;
    $contactName = null;
    $waId = null;
    $from = null;
    $body = null;
    $document = null;
    $filename = null;
    $mime_type = null;
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
    $reason=   null;
    $messagesType = null;
    $messagesTimestamp = null;
    $messagesFrom = null;


    $jsonData = $dataString;
    $data = json_decode($jsonData, true);
    $entry = $data['entry'][0] ?? null;

    if ($entry) {

        $id = $entry['id'] ?? null;
        $id = $entry['time'] ?? null;

        $changes = $entry['changes'][0] ?? null;

        if ($changes) {
            $value = $changes['value'] ?? null;
            $field = $changes['field'] ?? null;
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

                // $messageButtonPayload = $messages['timestamp'] ?? null;
                // $messageButtonPayload = $messages['timestamp'] ?? null;

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
                $event = $value['event'] ?? null;
                $message_template_id = $value['message_template_id'] ?? null;
                $message_template_name = $value['message_template_name'] ?? null;
                $message_template_language = $value['message_template_language'] ?? null;
                $reason= $value['reason'] ?? null;
            }

        }
    }
//////////////////////////////////////////////////////////////////////////
        if($status == null)
        {
            $status = 'received';
        }



        webhook::create(['webhook' => $dataString,
         'type' => $request_type,
         'contactName' => $contactName,
         'waId' => $waId,
         'body' => $body,
         'text' => $text,
         'mime_type' => $mime_type,
         'filename' => $filename,
         'image_mime_type' => $image_mime_type,
         'caption' => $caption,
         'status' => $status,
         'recipient_id' => $recipient_id,
         'conversation_id' => $conversation_id,
         'messages_id' => $messages_id,
         'messagesType' => $messagesType,
         'messagesFrom' => $messagesFrom,
         'messagesTimestamp' => $messagesTimestamp,
         'field' => $field,
         "event" => $event ?? null,
         "message_template_id" => $message_template_id ?? null,
         "message_template_name" => $message_template_name ?? null,
         "message_template_language" => $message_template_language ?? null,
         "reason" => $reason ?? null,
        ]);



        // if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //     // Verifique se os campos do formulário foram enviados
        //     if (isset($_POST["messagesFrom"]) && isset($_POST["messagesType"])) {
        //         // Acesse os valores POST
        //         $messagesFrom = $_POST["messagesFrom"];
        //         $messagesType = $_POST["messagesType"];

        //         // Exiba os valores na tela para depuração
        //         echo "From: " . $messagesFrom . "<br>";
        //         echo "Type: " . $messagesType;
        //     } else {
        //         echo "Campos do formulário não foram enviados corretamente.";
        //     }
        // }

// // Receber o POST do webhook
// $data = file_get_contents('php://input');

// // Exiba os dados recebidos para depuração
// echo "Dados recebidos do webhook:<br>";
// var_dump($data);

// // Faça o parsing dos dados, se necessário
// $parsedData = json_decode($data, true);

// // Exiba os dados parseados
// echo "Dados parseados:<br>";
// var_dump($parsedData);

//         return ['From'=> $messagesFrom];

 // Dados que você deseja salvar no arquivo de log
$logData = "Mensagem de log: " . date('Y-m-d H:i:s') . " - Informação importante.\n";

// Caminho para o arquivo de log
$logFilePath = "/storage/app/contabilidade/PostWebhook.log";

// Tente gravar os dados no arquivo de log
if (file_put_contents($logFilePath, $logData, FILE_APPEND | LOCK_EX)) {
    echo "Dados gravados com sucesso no arquivo de log.";
} else {
    echo "Erro ao gravar no arquivo de log.";
}




    }

    public function enviarMensagemNova()
    {
        $accessToken = 'EAALZBJb4ieTcBO8Yemzg41ZASqQgq3KsH3ve15cW8DzWBtPnobeDW6uaJeOO5hfQ8yMZBJlsBuHDecUGeYrlAAhZAorUnOOJHfRJ5wqvUdAEOCJsLfvZC9EZBFZCQAOTtr0hheg3SAZA88Q0aK9EX6NMqygeRy9WDps094Rxhzx6mGmEsBr7EzZCeEls6uvrp9WlfmzMZCvvDZCMduMZAXLjio4ZBkzAIktiCzzvMysWpQDqZC1L9Ia94s9ZBhY'; // Substitua pelo seu token de acesso
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
            // dd("Mensagem nova enviada", $responseData);
            return redirect(route('whatsapp.indexlista'));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }


    public function enviarMensagemAprovada()
    {

        $accessToken = 'EAALZBJb4ieTcBO8Yemzg41ZASqQgq3KsH3ve15cW8DzWBtPnobeDW6uaJeOO5hfQ8yMZBJlsBuHDecUGeYrlAAhZAorUnOOJHfRJ5wqvUdAEOCJsLfvZC9EZBFZCQAOTtr0hheg3SAZA88Q0aK9EX6NMqygeRy9WDps094Rxhzx6mGmEsBr7EzZCeEls6uvrp9WlfmzMZCvvDZCMduMZAXLjio4ZBkzAIktiCzzvMysWpQDqZC1L9Ia94s9ZBhY'; // Substitua pelo seu token de acesso

        $client = new Client();

        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => '5517997662949', // Número de telefone de destino
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

    public function enviarMensagemResposta()
    {
        $model = webhook::where("id", 61)->orderBy("id", "desc")->get();
        dd("Resposta", $model);
    }


    public function indexlista()
    {
        date_default_timezone_set('UTC');
        $model = webhook::orderBy("id", "desc")->get();
        // $model = webhook::where("id",310)->orderBy("id", "desc")->get();


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
            // $status =  null;
            // $recipient_id =  null;
            // // $conversation_id = null;
            // $messages_id = null;

            $jsonData = $registro->webhook;
            $data = json_decode($jsonData, true);

            $entry = $data['entry'][0] ?? null;


            if ($entry) {
                $id = $entry['id'] ?? null;
                $id = $entry['time'] ?? null;

                $changes = $entry['changes'][0] ?? null;
// dd($entry);

                if ($changes) {
                    $value = $changes['value'] ?? null;
                    $field = $changes['field'] ?? null;
                    $contacts = $value['contacts'][0] ?? null;
                    $messages = $value['messages'][0] ?? null;


                    // $statuses = $value['statuses'][0] ?? null;


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
                        $event = $value['event'] ?? null;
                        $message_template_id = $value['message_template_id'] ?? null;
                        $message_template_name = $value['message_template_name'] ?? null;
                        $message_template_language = $value['message_template_language'] ?? null;
                        $reason= $value['reason'] ?? null;
                        // dd($entry, $event, $message_template_id,$message_template_name, $message_template_language, $reason, $field );
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
                            // "messagesFrom" => $messagesFrom ?? null,
                            // "messagesTimestamp" => $messagesTimestamp ?? null,
                            // "messagesType" => $messagesType ?? null,
                        ];
                       $mergedData[] = array_merge($registro->toArray(), $newData);
                }
            }
        }

        $model =   $mergedData;

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
        $event = null ;


        $model = webhook::find($id);

        $created_at = $model->created_at;

        $jsonData =  $model->webhook;
        $data = json_decode($jsonData, true); // Converte o JSON em um array associativo

        // Agora você pode acessar as variáveis da seguinte forma:
        $object = $data['object'];

        $entry = $data['entry'][0];

        $id = $entry['id'];
        $changes = $entry['changes'][0];

        $value = $changes['value'];
        $field = $changes['field'];

        $messagingProduct = $value['messaging_product'] ?? null;
        $metadata = $value['metadata'] ?? null;


        $displayPhoneNumber = $metadata['display_phone_number'] ?? null;
        $phoneNumberId = $metadata['phone_number_id'] ?? null;
        //  dd($data, $statuses );

        if (isset($value) && is_array($value ) && count($value ) > 0) {
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
        $newData["created_at"] =  $created_at;
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
        $mergedData = array_merge($data, $newData);

        $model = $mergedData;


        return view('Api.registro', compact(
            'model',
        ));
    }
}
