<?php

namespace App\Http\Controllers;

use App\Models\webhook;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    //
    public function index(Request $r){

        $data = $r->all();
        $request_type = $r->method();
        $dataString = json_encode($data);

        webhook::create(['webhook' => $dataString, 'type'=>$request_type]);

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
            $messages = $value['messages'][0];

            $from = $messages['from'];
            $messageId = $messages['id'];
            $timestamp = $messages['timestamp'];
            $text = $messages['text'];
            $body = $text['body'];
            $messageType = $messages['type'];
            // Agora você pode acessar $contacts e outros campos dentro dele.
        } else {
            // Lida com o caso em que 'contacts' não está definido ou é um array vazio.
            $contacts = null;
            $messages = null;
            $from = null;
            $messageId = null;
            $timestamp = null;
            $text = null;
            $body = null;
            $messageType = null;


        }



          if (isset($contacts['profile']) && is_array($contacts['profile']) && count($contacts['profile']) > 0) {
            $profile = $contacts['profile'];
            $contactName = $profile['name'];
            $waId = $contacts['wa_id'];
            // Agora você pode acessar $contacts e outros campos dentro dele.
        } else {
            // Lida com o caso em que 'contacts' não está definido ou é um array vazio.
            $profile = null;
            $contactName = null ;
            $waId = null;
        }






          $model = $data;






        return view('Api.registro', compact('model', 'data', "field",
        'messagingProduct', "metadata", "displayPhoneNumber","phoneNumberId", "profile", "contactName",
        "waId","from", "messageId","body","messageType", "status"
    ));

    }


}
