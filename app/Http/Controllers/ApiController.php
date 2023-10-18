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




//   $jsonData = '{
//     "object": "whatsapp_business_account",
//     "entry": [
//       {
//         "id": "126533890548013",
//         "changes": [
//           {
//             "value": {
//               "messaging_product": "whatsapp",
//               "metadata": {
//                 "display_phone_number": "15550875457",
//                 "phone_number_id": "125892007279954"
//               },
//               "statuses": [
//                 {
//                   "id": "wamid.HBgNNTUxNzk5NjE2NTg1MRUCABEYEkE5RDYyNTQxMDU2NEUzQzA0QwA=",
//                   "status": "read",
//                   "timestamp": "1697584941",
//                   "recipient_id": "5517996165851"
//                 }
//               ]
//             },
//             "field": "messages"
//           }
//         ]
//       }
//     ]
//   }';

//   $data = json_decode($jsonData, true); // Decodifica o JSON em um array associativo

//   if (isset($data['entry']) && is_array($data['entry'])) {
//       $conversations = [];
//       foreach ($data['entry'] as $entry) {
//           $conversation = [
//               'id' => $entry['id'],
//               'messaging_product' => $entry['changes'][0]['value']['messaging_product'],
//               'phone_number' => $entry['changes'][0]['value']['metadata']['display_phone_number'],
//               'phone_number_id' => $entry['changes'][0]['value']['metadata']['phone_number_id'],
//               'statuses' => [],
//           ];

//           foreach ($entry['changes'][0]['value']['statuses'] as $status) {
//               $conversation['statuses'][] = [
//                   'id' => $status['id'],
//                   'status' => $status['status'],
//                   'timestamp' => $status['timestamp'],
//                   'recipient_id' => $status['recipient_id'],
//               ];
//           }

//           $conversations[] = $conversation;
//       }

//       // Agora, $conversations contém os dados da conversa em um formato de array em PHP
//       print_r($conversations);
//   }







        return view('Api.registro', compact('model', 'data', "field",
        'messagingProduct', "metadata", "displayPhoneNumber","phoneNumberId", "profile", "contactName",
        "waId","from", "messageId","body","messageType", "status"
    ));

    }


}
