<?php

namespace App\Http\Controllers;

use App\Models\webhook;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    //
    public function index(Request $r)
    {

        $data = $r->all();
        $request_type = $r->method();
        $dataString = json_encode($data);

        webhook::create(['webhook' => $dataString, 'type' => $request_type]);

        // return $data['hub_challenge'];

        // return ['sucess' => true];
    }

    public function enviarMensagemNova()
    {
        $accessToken = 'EAALZBJb4ieTcBO8Yemzg41ZASqQgq3KsH3ve15cW8DzWBtPnobeDW6uaJeOO5hfQ8yMZBJlsBuHDecUGeYrlAAhZAorUnOOJHfRJ5wqvUdAEOCJsLfvZC9EZBFZCQAOTtr0hheg3SAZA88Q0aK9EX6NMqygeRy9WDps094Rxhzx6mGmEsBr7EzZCeEls6uvrp9WlfmzMZCvvDZCMduMZAXLjio4ZBkzAIktiCzzvMysWpQDqZC1L9Ia94s9ZBhY'; // Substitua pelo seu token de acesso
        $client = new Client();
        $phone = '5517997662949'; // Número de telefone de destino
        $message = 'Esta é uma mensagem livre de teste'; // Sua mensagem de texto
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
            dd("Mensagem nova enviada", $responseData);
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
                'name' => 'hello_world',
                'language' => [
                    'code' => 'en_US',
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
            dd("Mensagem aprovada enviada", $responseData);
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

        // $model = webhook::where("id",52)->orderBy("id", "desc")->get();

        $mergedData = array(); // Inicialize o array $mergedData fora do loop foreach


        foreach ($model as $registro) {
            $profile = null;
            $contactName = null;
            $waId = null;
            $from = null;
            $body = null;
            $document = null;
            $filename = null;
            $mime_type = null;
            $image_mime_type = null;

            $jsonData = $registro->webhook;
            $data = json_decode($jsonData, true);

            $entry = $data['entry'][0] ?? null;

            if ($entry) {
                $id = $entry['id'] ?? null;
                $changes = $entry['changes'][0] ?? null;

                if ($changes) {
                    $value = $changes['value'] ?? null;
                    $contacts = $value['contacts'][0] ?? null;
                    $messages = $value['messages'][0] ?? null;


                    if ($messages) {

                        $text = $messages['text'] ?? null;
                        $body = $text['body'] ?? null;
                        $document = $messages['document'] ?? null;

                        $filename = $document['filename'] ?? null;
                        $mime_type = $document['mime_type'] ?? null;
                        $image = $messages['image']  ?? null;

                        $image_mime_type = $image['mime_type'] ?? null;

                        $caption = $image['caption'] ?? null;
                    }



                    if ($contacts) {
                        $profile = $contacts['profile'] ?? null;
                        $contactName = $profile['name'] ?? null;
                        $waId = $contacts['wa_id'] ?? null;

                        // Crie um array associativo com chaves fornecidas e valores correspondentes
                        $newData = [
                            "contactName" => $contactName,
                            "waId" => $waId,
                            "body" => $body,
                            "text" => $text,
                            "mime_type" => $mime_type,
                            "filename" => $filename,
                            "image_mime_type" => $image_mime_type,
                            "caption" => $caption

                        ];



                        $mergedData[] = array_merge($registro->toArray(), $newData);
                    }
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

        $messagingProduct = $value['messaging_product'];
        $metadata = $value['metadata'];


        $displayPhoneNumber = $metadata['display_phone_number'];
        $phoneNumberId = $metadata['phone_number_id'];
        //  dd($data, $statuses );

        if (isset($value['statuses']) && is_array($value['statuses']) && count($value['statuses']) > 0) {
            $statuses = $value['statuses'][0];
            $status = $statuses['status'];
        } else {
            // Lida com o caso em que 'contacts' não está definido ou é um array vazio.
            $status = null;
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
            "caption"
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

        // $newData[""] = $;

        // Mesclar o novo array com o modelo existente
        $mergedData = array_merge($data, $newData);

        $model = $mergedData;


        return view('Api.registro', compact(
            'model',
        ));
    }
}
