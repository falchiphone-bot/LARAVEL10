
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


    $registrobd = webhook::where('image_id', $id)
    ->orWhere('document_id', $id)
    ->orWhere('video_id', $id)
    ->orWhere('sticker_id', $id)
    ->orWhere('audio_id', $id)
    ->first();




    $idtabela = $registrobd->id;
    $messages_id = $registrobd->messages_id;
    $value_messaging_product = $registrobd->value_messaging_product;

    $sufixo = null;
    if($registrobd->messagesType == 'image'){
        if($registrobd->image_mime_type == 'image/jpeg'){
            $sufixo = '.jpg';
        }
        if($registrobd->sticker_mime_type == 'image/webp'){
            $sufixo = '.webp';
        }
    }
    if($registrobd->messagesType == 'video'){
        if($registrobd->video_mime_type == 'video/mp4'){
            $sufixo = '.mp4';
        }
    }
    if($registrobd->messagesType == 'audio'){
        if($registrobd->video_mime_type == 'audio/ogg'){
            $sufixo = '.ogg';
        }
    }


    if($registrobd->messagesType == 'document'){
        if($registrobd->document_mime_type == 'application/pdf'){
            $sufixo = '.pdf';
        }

        if($registrobd->document_mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'){
            $sufixo = '.docx';
        }
        elseif($registrobd->document_mime_type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
            $sufixo = '.xlsx';
        }
        elseif($registrobd->document_mime_type == 'text/rtf'){
            $sufixo = '.rtf';
        }
        elseif($registrobd->document_mime_type == 'text/csv'){
            $sufixo = '.csv';
        }
        elseif($registrobd->document_mime_type == 'text/plain'){
            $sufixo = '.txt';
        }
    }


    $file =
         'registro_'.$idtabela
        .'_image_id_'.trim($id)
        .'_message_id_'.$messages_id
        .'_value_messaging_product_'.$value_messaging_product
        .$sufixo;

    // Definindo o caminho onde a imagem será salva
    $pastafisica = '../storage/whatsapp/';

    if (!file_exists($pastafisica)) {
        // Verifique se a pasta não existe e, se não existir, crie-a
        if (mkdir($pastafisica, 0777, true)) {
            // echo 'A pasta foi criada com sucesso.';
        } else {
            // echo 'Não foi possível criar a pasta.';
        }
    } else {
        // echo 'A pasta já existe.';
    }


    $filePath = $pastafisica. $file;

    // Salva o conteúdo da resposta no arquivo
    file_put_contents($filePath, $response->getBody());


    $registrobd->update([
        'url_arquivo' => $filePath,
    ]);
