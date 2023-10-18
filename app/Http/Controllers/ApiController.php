<?php

namespace App\Http\Controllers;

use App\Models\webhook;
use Illuminate\Http\Request;

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

    public function indexlista()
    {
        $model = webhook::orderBy("id", "desc")->get();

        return view('Api.indexlista', compact('model'));
    }

    public function registro(string $id)
    {

        $field = null ;
        $messagingProduct = null ;
        $metadata = null ;
        $displayPhoneNumber = null ;
        $phoneNumberId = null ;
        $profile = null ;
        $contactName = null ;
        $waId = null ;
        $from = null ;
        $messageId = null ;
        $body = null ;
        $messageType = null ;
        $status = null ;
        $filename = null ;
        $animated  = null ;
        $mime_type = null ;
        $sha256 = null ;
        $iddocument  = null ;
        $caption = null ;



        $model = webhook::find($id);

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

                 $caption = $document['caption'];
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
