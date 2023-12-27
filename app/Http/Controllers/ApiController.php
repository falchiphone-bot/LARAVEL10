<?php

namespace App\Http\Controllers;

use App\Models\FormandoBaseWhatsapp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use App\Models\webhook;
use App\Models\webhookAtendimentoEncerrado;
use Illuminate\Http\Request;
use App\Models\WebhookConfig;
use App\Models\webhookContact;
use App\Models\WebhookTemplate;
use App\Services\WebhookServico;
use App\Services\WebhookContactsServico;
use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Calculation\Web;
use Illuminate\Support\Facades\Gate;
use App\Services\WebhookContactsEnviarFlow;


class ApiController extends Controller
{

    public function __construct()
    {

        // $this->middleware(['permission:CATEGORIAS - INCLUIR'])->only(['create', 'store']);
        // $this->middleware(['permission:CATEGORIAS - EDITAR'])->only(['edit', 'update']);
        // $this->middleware(['permission:CATEGORIAS - VER'])->only(['edit', 'update']);
        // $this->middleware(['permission:CATEGORIAS - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:WHATSAPP - LISTAR'])->only('indexlista');

    }

    public function enviarFlowAlterarNascimento($recipient_id, $entry_id)
    {
        WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarNascimento($recipient_id, $entry_id);
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $recipient_id, 'entry_id' => $entry_id]));
    }

    public function enviarFlowMenuCadastroBasico($recipient_id, $entry_id)
    {
        WebhookContactsEnviarFlow::EnviaMensagemFlowMenuCadastroBasico($recipient_id, $entry_id);
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $recipient_id, 'entry_id' => $entry_id]));
    }
    public function enviarFlowCadastro($recipient_id, $entry_id)
    {
        WebhookContactsEnviarFlow::EnviaMensagemFlowCadastro($recipient_id, $entry_id);
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $recipient_id, 'entry_id' => $entry_id]));
    }

    public function enviarFlowAlterarCPF($recipient_id, $entry_id)
    {
        WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarCpf($recipient_id, $entry_id);
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $recipient_id, 'entry_id' => $entry_id]));
    }

    public function enviarFlowAlterarRG($recipient_id, $entry_id)
    {
        WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarRg($recipient_id, $entry_id);
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $recipient_id, 'entry_id' => $entry_id]));
    }
    public function enviarFlowAlterarCidadeUf($recipient_id, $entry_id)
    {
        WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarCidadeUf($recipient_id, $entry_id);
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $recipient_id, 'entry_id' => $entry_id]));
    }
    public function enviarFlowAlterarNomeCompleto($recipient_id, $entry_id)
    {
        WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarNome($recipient_id, $entry_id);
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $recipient_id, 'entry_id' => $entry_id]));
    }

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
        $image_caption = null;
        $waId = null;
        $body = null;
        $mime_caption = null;
        $mime_type = null;
        $image_id = null;
        $image_sha256 = null;
        $image_mime_type = null;
        $document_filename = null;
        $document_caption = null;
        $document_mime_type = null;
        $document_sha256 = null;
        $document_id = null;

        $video_filename = null;
        $video_mime_type = null;
        $video_sha256 = null;
        $video_id = null;

        $audio_mime_type = null;
        $audio_sha256 = null;
        $audio_id = null;
        $audio_voice = null;

        $statuses = null;
        $status =  'received';

        $sticker_mime_type = null;
        $sticker_sha256 = null;
        $sticker_id = null;
        $sticker_animated = null;


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



            $statuses =  $data['entry'][0]['changes'][0]['value']['statuses'][0] ?? null;
            if ($statuses) {
                $status = $statuses['status'] ?? null;
                $recipient_id = $statuses['recipient_id'] ?? null;
                $conversation_id = $statuses['id'] ?? null;
            }





            $audio =  $data['entry'][0]['changes'][0]['value']['messages'][0]['audio'] ?? null;

            if($audio)
            {
                $audio_mime_type = $audio['mime_type'] ?? null;
                $audio_sha256 = $audio['sha256'] ?? null;
                $audio_id = $audio['id']?? null;
                $audio_voice = $audio['voice']?? null;
            }


            $video =  $data['entry'][0]['changes'][0]['value']['messages'][0]['video'] ?? null;
            if($video)
            {
                $video_filename = $video['filename'] ?? null;
                $video_mime_type = $video['mime_type'] ?? null;
                $video_sha256 = $video['sha256'] ?? null;
                $video_id = $video['id'] ?? null;
            }


            $document =  $data['entry'][0]['changes'][0]['value']['messages'][0]['document'] ?? null;
            if($document)
            {
                $document_filename = $document['filename'] ?? null;
                $document_caption = $document['caption'] ?? null;
                $document_mime_type = $document['mime_type'] ?? null;
                $document_sha256 = $document['sha256'] ?? null;
                $document_id = $document['id'] ?? null;
            }


            $image =  $data['entry'][0]['changes'][0]['value']['messages'][0]['image'] ?? null;
            if($image)
            {
                $image_caption = $image['caption'] ?? null;
                $image_sha256 = $image['sha256'] ?? null;
                $image_id = $image['id'] ?? null;
            }


            $sticker =  $data['entry'][0]['changes'][0]['value']['messages'][0]['sticker'] ?? null;
            if($sticker)
            {
                $sticker_mime_type = $sticker['mime_type'] ?? null;
                $sticker_sha256 = $sticker['sha256'] ?? null;
                $sticker_id = $sticker['id']?? null ;
                $sticker_animated = $sticker['animated'] ?? null;
                // DD($sticker_mime_type, $sticker_sha256, $sticker_id, $sticker_animated);
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
                $image_caption = $image_caption ?? null;
                // $status = $status ?? null;
                // $recipient_id = $recipient_id ?? null;
                $conversation_id = $conversation_id ?? null;
                $messages_id = $messages_id ?? null;

                $contacts = $value['contacts'][0] ?? null;
                $messages = $value['messages'][0] ?? null;
                // $statuses = $value['statuses'][0] ?? null;
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

                    // $image_caption = $image['image_caption'] ?? null;

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
            . "document_filename" . $document_filename . "\n"
            . "document_caption" . $document_caption . "\n"
            . "document_mime_type" . $document_mime_type . "\n"
            . "document_sha256" . $document_sha256  . "\n"
            . 'document_id' . $document_id . "\n"

            . "video_filename" . $video_filename . "\n"
            . "video_mime_type" . $video_mime_type . "\n"
            . "video_sha256" . $video_sha256  . "\n"
            . 'video_id' . $video_id . "\n"

            . "audio_mime_type" . $audio_mime_type . "\n"
            . "audio_sha256" . $audio_sha256  . "\n"
            . "audio_id" . $audio_id . "\n"
            . "audio_voice" . $audio_voice. "\n"


            . "sticker_mime_type" . $sticker_mime_type . "\n"
            . "sticker_sha256" . $sticker_sha256  . "\n"
            . 'sticker_id' . $sticker_id . "\n"
            . 'sticker_animated' . $sticker_animated . "\n"



            . "image_mime_type: " . $image_mime_type . "\n"
            . "image_id: " . $image_id . "\n"
            . "image_sha256: " . $image_sha256 . "\n"
            . "image_mime_type: " . $image_mime_type . "\n"
            . "image_caption: " . $image_caption . "\n"
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
                                    file_put_contents($logFilePath, $logData, FILE_APPEND | LOCK_EX);


        ///////////////////////////////////////////// GRAVAR EM BANCO DE DADOS
        //////////// Se acrescentar campos para gravar em BD,
        ///// lembrar de também inserir no model webhook
        ////// e colocar somente  ' = apóstrofo no webhook.
        ////// CUIDADO... NÃO COLOCAR ASPAS = "
        ////// E AQUI ABAIXO TAMBÉM


        if($recipient_id == null)
        {
            $recipient_id = $messagesFrom;
        }



        $newWebhookContact = WebhookServico::AtualizaOuCriaWebhookContact($recipient_id, $contactName, $messagesTimestamp, $entry_id, $contactName);

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
         };


         $registro = webhookContact::
         where('recipient_id', $recipient_id)
         ->where('entry_id', $entry_id)
         ->orderBy('id', 'desc')
        ->first();
                ////////////////////////////////////  se bloquear entrada de mensagem
                        $bloquear_entrada_mensagem = $registro->bloquear_entrada_mensagem;
                        $nome_contato = $registro->contactName;
                        WebhookServico::VerificaBloqueadoEntradaMensagem($entry_id,
                        $bloquear_entrada_mensagem,
                        $status, $messagesFrom, $nome_contato);
                //////////////////////////////////////////////


         if ($registro) {
             $updateData = [
             ];

             if ($status === 'delivered') {
                 $updateData['ultima_entrega'] = now();
                 $updateData['status_mensagem_entregue'] = true;
                 $updateData['user_updated'] = 'webhook@falchi.com.br';

             } elseif ($status === 'read') {
                 $updateData['status_mensagem_enviada'] = true;
                 $updateData['status_mensagem_entregue'] = false;
                 $updateData['ultima_leitura'] = now();
                 $updateData['user_updated'] = 'webhook@falchi.com.br';

             } elseif ($status === 'sent') {
                 $updateData['status_mensagem_entregue'] = true;
                 $updateData['user_updated'] = 'webhook@falchi.com.br';

            } elseif ($status === 'received') {
                $MensagemRecebida = 'Mensagem recebida de: '
                . $messagesFrom
                . ' - ' . $contactName
                . ' - Com a mensagem: '
                . $body
                . ' - TimeStamp:'
                . $messagesTimestamp;


                WebhookServico::avisomensagemrecebidasupervisor($MensagemRecebida, $recipient_id, $entry_id, $messagesTimestamp, $contactName);

                $somarecebida = $registro->quantidade_nao_lida + 1;
                $updateData['quantidade_nao_lida'] = $somarecebida;
                $updateData['user_updated'] = 'webhook@falchi.com.br';
            }

// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             $registro->update($updateData);

        };


         if($recipient_id == $messagesFrom)
         {
             $recipient_id = null;
         }

         if($messagesType == 'interactive')
         {
             $messagesTimestamp= $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;
             $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;

             $data = json_decode($interactive_nfm_reply_response_json, true);
             $body =  WebhookContactsEnviarFlow::montabodyflow($data, $messagesTimestamp);


            //  $body = 'Nome: ' . $nome . " | " .
            //  'Data de Nascimento: ' . $dataNascimento . " | " .
            //  'Nome do Pai: ' . $nomePai . " | " .
            //  'Nome da Mãe: ' . $nomeMae . " | " .
            //  'Descrição: ' . $flow_description . " | " .
            //  'Flow Token: ' . $flow_token . " | " .
            //  'Código registro: ' . $messagesTimestamp . " | " ?? null;
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
            'mime_type' => $mime_type ?? null,
            'filename' => $filename ?? null,
            'document_filename' => $document_filename ?? null,
            'document_caption' => $document_caption ?? null,
            'document_mime_type' => $document_mime_type ?? null,
            'document_sha256' => $document_sha256 ?? null,
            'document_id' => $document_id ?? null,

            'video_filename' => $video_filename ?? null,
            'video_mime_type' => $video_mime_type ?? null,
            'video_sha256' => $video_sha256 ?? null,
            'video_id' => $video_id ?? null,


            'sticker_mime_type' => $sticker_mime_type ?? null,
            'sticker_sha256' => $sticker_sha256 ?? null,
            'sticker_id' => $sticker_id ?? null,
            'sticker_animated' => $sticker_animated ?? null,


            'audio_mime_type' => $audio_mime_type ?? null,
            'audio_sha256' => $audio_sha256 ?? null,
            'audio_id' => $audio_id ?? null,
            'audio_voice' => $audio_voice ?? null,


            'image_id' => $image_id,
            'image_sha256'  => $image_sha256,
            'image_mime_type' => $image_mime_type ?? null,
            'image_caption' => $image_caption ?? null,
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


        WebhookServico::interactive($entry);


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
            'https://graph.facebook.com/v18.0/147126925154132/messages',

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
                'user_atendimento' => Auth::user()->name ?? null,            ]);

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



        $response = $client->post('https://graph.facebook.com/v18.0/157689817424024/messages', [
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



        $response = $client->post('https://graph.facebook.com/v18.0/125892007279954/messages', [
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
        $model = webhook::limit(1000)->orderBy("id", "desc")->get();
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

                        $image_caption = $image['image_caption'] ?? null;

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
                        "image_caption" => $image_caption ?? null,
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

    public function atendimento(string $id)
    {
        $model = webhook::find($id);



        if($model->url_arquivo){
                $url = $model->url_arquivo;
                if (!file_exists($url )) {
                    $model['url_arquivo'] = '../storage/whatsapp/nao_existe.jpg';
                }
         }

        return view('Api.atendimento', compact(
            'model',
        ));
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
        $image_caption = null;
        $event = null;
        $message_template_id = null;
        $message_template_name = null;
        $message_template_language = null;
        $reason = null;

        $model = webhook::find($id);
        $cadastro_registro = $model;
        // $created_at = $model->created_at;

        if($cadastro_registro->url_arquivo){
                $url = $cadastro_registro->url_arquivo;
                if (!file_exists($url )) {
                    $cadastro_registro['url_arquivo'] = '../storage/whatsapp/nao_existe.jpg';
                }
         }
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

            $image_caption = $document['image_caption'] ?? null;
            // dd($data, $document,  $image_caption );


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
            "image_caption",
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
        $newData["image_caption"] = $image_caption;
        $newData["event"] = $event;
        $newData["message_template_id"] = $message_template_id;
        $newData["message_template_name"] = $message_template_name;
        $newData["message_template_language"] = $message_template_language;
        $newData["reason"] = $reason;
        $newData["user_atendimento"] = Auth::user()->name;


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
        $object = null;
        $profile = null;
        $contactName = null;
        $waId = null;
        $body = null;
        $document = null;
        $filename = null;
        $mime_type = null;
        $image_id = null;
        $image_caption = null;
        $image_sha256 = null;
        $image_mime_type = null;
        $document_filename = null;
        $document_caption = null;
        $document_mime_type = null;
        $document_sha256 = null;
        $document_id = null;

        $video_caption = null;
        $video_filename = null;
        $video_mime_type = null;
        $video_sha256 = null;
        $video_id = null;

        $audio_mime_type = null;
        $audio_sha256 = null;
        $audio_id = null;
        $audio_voice = null;

        $sticker_mime_type = null;
        $sticker_sha256 =  null;
        $sticker_id = null ;
        $sticker_animated =  null;

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
        $flow_token = null;
        $flow_description = null;


        $object = $data['object'] ?? null;
        $entry = $data['entry'][0] ?? null;




        if ($entry) {
            $entry_id = $entry['id'] ?? null;
            $entry_time = $entry['time'] ?? null;

            $changes = $entry['changes'][0] ?? null;


            $document =  $data['entry'][0]['changes'][0]['value']['messages'][0]['document'] ?? null;

            $sticker =  $data['entry'][0]['changes'][0]['value']['messages'][0]['sticker'] ?? null;

//////// flow interativos




            WebhookServico::interactive($entry);

            $statuses =  $data['entry'][0]['changes'][0]['value']['statuses'][0] ?? null;
            if ($statuses) {
                $status = $statuses['status'] ?? null;
                $recipient_id = $statuses['recipient_id'] ?? null;
                $conversation_id = $statuses['id'] ?? null;
            }

            // dd( $statuses, $status, $recipient_id, $conversation_id);


            if($document)
            {
                $document_filename = $document['filename'] ?? null;
                $document_caption = $document['caption'] ?? null;
                $document_mime_type = $document['mime_type'] ?? null;
                $document_sha256 = $document['sha256'] ?? null;
                $document_id = $document['id'] ?? null;
                // DD($document, $document_filename, $document_mime_type, $document_sha256, $document_id);
            }

            $audio =  $data['entry'][0]['changes'][0]['value']['messages'][0]['audio'] ?? null;

           if($audio)
           {
               $audio_mime_type = $audio['mime_type'] ?? null;
               $audio_sha256 = $audio['sha256'] ?? null;
               $audio_id = $audio['id']?? null;
               $audio_voice = $audio['voice']?? null;
           }

//   dd($data,  $audio,  $audio_mime_type, $audio_sha256, $audio_id, $audio_voice  );

             $image =  $data['entry'][0]['changes'][0]['value']['messages'][0]['image'] ?? null;
            if($image)
            {
                $image_caption = $image['caption'] ?? null;
                $image_mime_type = $image['mime_type'] ?? null;
                $image_sha256 = $image['sha256'] ?? null;
                $image_id = $image['id']?? null;
            }

            $video =  $data['entry'][0]['changes'][0]['value']['messages'][0]['video'] ?? null;
            if($video)
            {
                $video_filename = $video['filename'] ?? null;
                $video_caption = $video['caption'] ?? null;
                $video_mime_type = $video['mime_type'] ?? null;
                $video_sha256 = $video['sha256'] ?? null;
                $video_id = $video['id']?? null ;
                // DD($video, $video_caption,$video_filename, $video_mime_type, $video_sha256, $video_id);
            }


            if($sticker)
            {
                $sticker_mime_type = $sticker['mime_type'] ?? null;
                $sticker_sha256 = $sticker['sha256'] ?? null;
                $sticker_id = $sticker['id']?? null ;
                $sticker_animated = $sticker['animated'] ?? null;
                // DD($sticker_mime_type, $sticker_sha256, $sticker_id, $sticker_animated);
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

                // $status = $status ?? null;
                // $recipient_id = $recipient_id ?? null;
                $conversation_id = $conversation_id ?? null;
                $messages_id = $messages_id ?? null;

                $contacts = $value['contacts'][0] ?? null;
                $messages = $value['messages'][0] ?? null;
                // $statuses = $value['statuses'][0] ?? null;
                $ban_info = $value['ban_info'] ?? null;

                if ($messages) {
                    $messages_id = $messages['id'] ?? null;
                    $text = $messages['text'] ?? null;
                    $body = $text['body'] ?? null;
                    $document = $messages['document'] ?? null;

                    $filename = $document['filename'] ?? null;
                    $mime_type = $document['mime_type'] ?? null;
                    $image = $messages['image']  ?? null;



                    // $image_caption = $image['image_caption'] ?? null;

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


//////// flow interativos


        if($messagesType == 'interactive')
        {
            $messagesTimestamp= $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;
            $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;

            $data = json_decode($interactive_nfm_reply_response_json, true);

            // $nome = $data['nome'] ?? null;
            // $dataNascimento = $data['dataNascimento'] ?? null;
            // $dataNascimentoObj = DateTime::createFromFormat('d/m/Y', $dataNascimento);
            // $nomePai = $data['nomePai'] ?? null;
            // $nomeMae = $data['nomeMae'] ?? null;
            // $Cpf = $data['Cpf'] ?? null;

            // $flow_description = $data['description'] ?? null;
            // $flow_token = $data['flow_token'] ?? null;


            $body = WebhookContactsEnviarFlow::montabodyflow($data);
            // $body = '';

            // if ($nome) {
            //     $body .= 'Nome: ' . $nome . " | ";
            // }
            // if ($dataNascimento) {
            //     $body .= 'Data de Nascimento: ' . $dataNascimento . " | ";
            // }
            // if ($nomePai) {
            //     $body .= 'Nome do Pai: ' . $nomePai . " | ";
            // }
            // if ($nomeMae) {
            //     $body .= 'Nome da Mãe: ' . $nomeMae . " | ";
            // }
            // if ($flow_description) {
            //     $body .= 'Descrição: ' . $flow_description . " | ";
            // }
            // if ($flow_token) {
            //     $body .= 'Flow Token: ' . $flow_token . " | ";
            // }
            // if ($Cpf) {
            //     $body .= 'CPF: ' . $Cpf . " | ";
            // }
            // if ($messagesTimestamp) {
            //     $body .= 'Código registro: ' . $messagesTimestamp . " | ";
            // }

            // // Remover o último " | " se necessário
            // $body = rtrim($body, " | ");

        }




        $atualiza = [
            // 'webhook' => $dataString ?? null,
            'user_updated' =>  Auth::user()->email,
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
            'document_filename' => $document_filename ?? null,
            'document_caption' => $document_caption ?? null,
            'document_mime_type' => $document_mime_type ?? null,
            'document_sha256' => $document_sha256 ?? null,
            'document_id' => $document_id ?? null,

            'video_filename' => $video_filename ?? null,
            'video_caption' => $video_caption ?? null,
            'video_mime_type' => $video_mime_type ?? null,
            'video_sha256' => $video_sha256 ?? null,
            'video_id' => $video_id ?? null,

            'sticker_mime_type' => $sticker_mime_type ?? null,
            'sticker_sha256' => $sticker_sha256 ?? null,
            'sticker_id' => $sticker_id ?? null,
            'sticker_animated' => $sticker_animated ?? null,

            'audio_mime_type' => $audio_mime_type ?? null,
            'audio_sha256' => $audio_sha256 ?? null,
            'audio_id' => $audio_id ?? null,
            'audio_voice' => $audio_voice ?? null,


            'image_id' => $image_id ?? null,
            'image_caption' => $image_caption ?? null,
            'image_sha256' => $image_sha256 ?? null,
            'image_mime_type' => $image_mime_type ?? null,
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
            'flow_token' => $flow_token ?? null,
            'flow_description' => $flow_description ?? null,
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
            . "document_filename" . $document_filename . "\n"
            . "document_caption" . $document_caption . "\n"
            . "document_mime_type" . $document_mime_type . "\n"
            . "document_sha256" . $document_sha256  . "\n"
            . "document_id" . $document_id . "\n"

            . "video_filename" . $video_filename . "\n"
            . "video_caption" . $video_caption . "\n"
            . "video_mime_type" . $video_mime_type . "\n"
            . "video_sha256" . $video_sha256  . "\n"
            . "video_id" . $video_id . "\n"


            . "audio_mime_type" . $audio_mime_type . "\n"
            . "audio_sha256" . $audio_sha256  . "\n"
            . "audio_id" . $audio_id . "\n"
            . "audio_voice" . $audio_voice. "\n"

            . "sticker_mime_type" . $sticker_mime_type . "\n"
            . "sticker_sha256" . $sticker_sha256  . "\n"
            . "sticker_id" . $sticker_id . "\n"
            . "sticker_animated" . $sticker_animated . "\n"

            . "image_mime_type: " . $image_mime_type . "\n"
            . "image_id: " . $image_id . "\n"
            . "image_sha256: " . $image_sha256 . "\n"
            . "image_mime_type: " . $image_mime_type . "\n"
            . "image_caption: " . $image_caption . "\n"
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
            . "flow_token:  " . $flow_token . "\n"
            . "flow_description:  " . $flow_description . "\n"
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

        public function atendimentoWhatsapp()
        {
            $Contatos = NULL;
            $RegistrosContatos = NULL;

                 $Contatos = webhook::select(DB::raw('CONCAT(recipient_id, messagesFrom) AS recipient_messages'))
                    ->groupBy(DB::raw('CONCAT(recipient_id, messagesFrom)'))
                    ->where(DB::raw('CONCAT(recipient_id, messagesFrom)'), '<>', '')
                    ->get();


                    $resultado = WebhookContactsServico::FiltraCanaisUsuariosAtivos();
                    $RegistrosContatos = $resultado['RegistrosContatos'];
                    $QuantidadeCanalAtendimento = $resultado['QuantidadeCanalAtendimento'];

            $selecao = null;
            if( $RegistrosContatos == null)
            {
                session(['error' => 'Nada pesquisado! Usuário sem permissão de acesso a nenhum canal de atendimento!']);
                return redirect(route('dashboard'));
            }

            // dd($Contatos, $RegistrosContatos);
            $textopesquisar = null;
            return view('api.atendimentoWhatsapp', compact('Contatos', 'selecao', 'RegistrosContatos', 'textopesquisar'));
        }


    public function atendimentoWhatsappFiltroTelefone(string $recipient_id, string $entry_id, request $request)
    {
        $textopesquisar = null;

        $Contatos = webhook::select(DB::raw('CONCAT(recipient_id, messagesFrom) AS recipient_messages'))
        ->groupBy(DB::raw('CONCAT(recipient_id, messagesFrom)'))
        ->where(DB::raw('CONCAT(recipient_id, messagesFrom)'), '<>', '')
        ->get();


        $RegistrosContatos = null;
        $selecao = null;
        $QuantidadeCanalAtendimento = 0;

        $resultado = WebhookContactsServico::FiltraCanaisUsuariosAtivos();
        $RegistrosContatos = $resultado['RegistrosContatos'];
        $QuantidadeCanalAtendimento = $resultado['QuantidadeCanalAtendimento'];


        $NomeAtendido =  webhookContact::
        where('recipient_id', $recipient_id)
        ->Where('entry_id', $entry_id)
        ->OrderBy('updated_at', 'desc')
        ->get()->first();



        $Usuarios = User::where('email', '!=', Auth::user()->email)
        ->where('atendente_whatsapp', 1)
        ->orderBy('name')->get();

        if( $NomeAtendido)
        {
            $Ultimo_atendente = webhookAtendimentoEncerrado::OrderBy('created_at', 'desc')
            ->where('user_atendimento', Auth::user()->email)
            ->where('id_contact', $NomeAtendido->id)
            ->where('fim_atendimento',1)
            ->get()->first();



// WebhookServico::temposessao($NomeAtendido);
$tempo_em_segundos  = null;
    $tempo_em_horas = null;
    $tempo_em_minutos = null;

    if($NomeAtendido->timestamp)
            {
                $tempo_em_segundos = strtotime(now()) - $NomeAtendido->timestamp;
                            $tempo_em_horas = $tempo_em_segundos / 3600;
                            $tempo_em_minutos = $tempo_em_segundos / 60;
            }


    $numero = $tempo_em_horas;

    $partes = explode('.', $numero);


    $parte_inteira = (int)$partes[0];
    $parte_decimal = isset($partes[1]) ? (float)('0.' . $partes[1]) : 0;

    $parte_decimal_minutos = round($parte_decimal * 60);




        // dd($NomeAtendido->user_atendimento, $Ultimo_atendente->user_atendimento);

        if (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127')
        && Gate::allows('WHATSAPP_ENTRY_ID_189514994242034')
        && Gate::allows('WHATSAPP_ENTRY_ID_179613235241221')) {

                $selecao = webhook::limit(1000)
                ->where(function($query) use ($entry_id) {
                    $query->where('entry_id', $entry_id);
                 })
                ->where(function($query) use ($recipient_id, $entry_id) {
                    $query->where('recipient_id', $recipient_id)
                        ->orwhere('messagesFrom', $recipient_id)
                        ->where('entry_id', $entry_id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
                $QuantidadeCanalAtendimento = 2;

        }
        else
                if (Gate::allows('WHATSAPP_ENTRY_ID_179613235241221')) {
                    $selecao = webhook::limit(1000)
                    ->where('entry_id','179613235241221')
                    ->where(function($query) use ($recipient_id, $entry_id) {
                        $query->where('recipient_id', $recipient_id)
                            ->orwhere('messagesFrom', $recipient_id)
                            ->where('entry_id', $entry_id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
                    $QuantidadeCanalAtendimento = 1;

                }

       else
       if (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127')) {
                    $selecao = webhook::limit(1000)
                    ->where('entry_id','167722543083127')
                    ->where(function($query) use ($recipient_id, $entry_id) {
                        $query->where('recipient_id', $recipient_id)
                            ->orwhere('messagesFrom', $recipient_id)
                            ->where('entry_id', $entry_id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
                    $QuantidadeCanalAtendimento = 1;

                }


        else
                if (Gate::allows('WHATSAPP_ENTRY_ID_189514994242034')) {
                $selecao = webhook::limit(1000)
                    ->where('entry_id','189514994242034')
                    ->where(function($query) use ($recipient_id, $entry_id) {
                        $query->where('recipient_id', $recipient_id)
                            ->orwhere('messagesFrom', $recipient_id)
                            ->where('entry_id', $entry_id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
                    $QuantidadeCanalAtendimento = 1;
                    // dd('0',$recipient_id, $entry_id, $selecao);
                }
        }


        // dd($selecao);
        if($selecao == null)
        {
            session(['error' => 'Nada pesquisado! Usuário sem permissão de acesso!']);
            return redirect(route('whatsapp.atendimentoWhatsapp'));
        }



        // dd('1',$recipient_id, $entry_id, $selecao);
        $id = $recipient_id;
        return view('api.atendimentoWhatsappFiltro',
         compact('id', 'entry_id','Contatos','selecao','NomeAtendido',
         'Usuarios','Ultimo_atendente', 'tempo_em_horas',
         'tempo_em_segundos','tempo_em_minutos','parte_inteira','parte_decimal_minutos','RegistrosContatos', 'QuantidadeCanalAtendimento', 'textopesquisar'));
    }


    public function PreencherMensagemResposta(string $id)
    {
        $messagesTimestamp = null;
        $model = webhook::find($id);

        return view('api.registroresposta', compact('model'))->with(['id' => $id]);
    }

    public function enviarMensagemResposta(Request $request, $id)
    {
        $messagesTimestamp = null;
        $request->validate([
            'token_type' => 'required|in:token24horas,tokenpermanenteusuario',
        ]);
        $token = $request->token_type;

        $id_arquivo = null;
        $arquivo = $request->file('arquivo') ?? null;

      if( $arquivo)
      {
        $path = $arquivo->getRealPath() ;

        $name = $arquivo->getClientOriginalName()  ;
        $extension = $arquivo->getClientOriginalExtension()  ;

        $mime_type = $arquivo->getMimeType()  ;

        $id_arquivo = ApiController::Enviar_Arquivo($arquivo, $path, $name, $extension, $mime_type);
      }


        $model = webhook::find($id);

        $message = $request->input('mensagem');

            if (empty($request->input('mensagem'))) {
                // O campo de mensagem está vazio, defina a mensagem de erro na sessão.
                session()->flash('MensagemNaoPreenchida', 'A mensagem está vazia... necessita de preenchimento!');
                return redirect()->back();
            }



            $WebhookConfig =  WebhookConfig::OrderBy('usuario')->get()->first();
            $phone_number_id = WebhookServico::phone_number_id();
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


// ================arquivo em anexo como $responseData
if($id_arquivo){

   include('api/tipoarquivo.php');

   dd($mime_type);

 $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => $tipoarquivo,
            'image' => [
                'id' => $id_arquivo['id'],
                'caption' => $message,
            ],
        ];
        $tipomensagem = '/messages';

}
else
{
     // ===================================== somente texto como resposta
     $requestData = [
        'messaging_product' => 'whatsapp',
        'to' => $phone,
        'type' => 'text',
        'text' => [
            'body' => $message,
        ],
    ];
    $tipomensagem = '/messages';

}

// =================================================================


        $response = $client->post(
            'https://graph.facebook.com/v18.0/' . $phone_number_id . $tipomensagem ,
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
                'messagesType' => $requestData['type'] ?? null,
                'image_caption' => $requestData['image']['caption'] ?? null,
                'image_id' => $requestData['image']['id'] ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);

            $recipient_id = $requestData['to'];
            $contactName = $model->contactName;
            $newWebhookContact = WebhookServico::AtualizaOuCriaWebhookContact($recipient_id, $contactName, $messagesTimestamp ?? null);
            session()->flash('success', 'Mensagem enviada com sucesso para ' . $model->contactName .  '.');
            return redirect(route('whatsapp.indexlista'));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }
    public function enviarMensagemRespostaAtendimento(Request $request, $id)
    {

        $usuario = trim(Auth::user()->email);
        $id_arquivo = null;
        $arquivo = $request->file('arquivo') ?? null;



        // $model = webhook::where('messagesFrom',$id)->first();
        // $entry_id = $model->entry_id;

        // $WebhookConfig =  WebhookConfig::where('ativado','1')->first();

        $entry_id = $request->entry_id;

        $WebhookConfig =  WebhookConfig::where('identificacaocontawhatsappbusiness',$entry_id)->first();

        $phone_number_id = WebhookServico::phone_number_id($entry_id);
        $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
        $Token = $WebhookConfig->token24horas;


        if($Token == null){
            session()
            ->flash('MensagemNaoPreenchida', 'Token não definido por algum erro. Verifique. Linha 1142!');
            return redirect()->back();
        }


        $client = new Client();
        $phone = $request->recipient_id; // Número de telefone de destino
        $client = new Client();
        $requestData = [];

      if($arquivo)
      {
        $path = $arquivo->getRealPath() ;

        $name = $arquivo->getClientOriginalName()  ;
        $extension = $arquivo->getClientOriginalExtension()  ;

        $mime_type = $arquivo->getMimeType()  ;



        $id_arquivo = ApiController::Enviar_Arquivo($arquivo, $path, $name, $extension, $mime_type, $entry_id);
      }

        // $model = webhook::find($id);

        $message = $request->input('mensagem');

        $registro = webhookContact::where('recipient_id', $phone)->get()->first();



       if($registro->user_atendimento !== Auth::user()->email)
       {

        $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ")";

       }


            if($arquivo == null)
            {
                if (empty($request->input('mensagem'))) {
                    // O campo de mensagem está vazio, defina a mensagem de erro na sessão.
                    session()->flash('MensagemNaoPreenchida', 'A mensagem está vazia... necessita de preenchimento!');
                    return redirect()->back();
                }
            }






// ================arquivo em anexo como $responseData

if($id_arquivo){

            $tipoarquivo = ApiController::TipoArquivo($mime_type);

            if($tipoarquivo == 'image'){
                        $requestData = [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $phone,
                        'type' => $tipoarquivo,
                        'image' => [
                            'id' => $id_arquivo['id'],
                            'caption' => $message,
                        ],
                    ];
            }
            elseif($tipoarquivo == 'document')
            {
                    $requestData = [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $phone,
                        'type' => $tipoarquivo,
                         'document' => [
                            'id' => $id_arquivo['id'],
                            'filename' => $name,
                            'caption' => $message,
                        ],
                    ];


            }
            elseif($tipoarquivo == 'video')
            {
                $requestData = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $phone,
                    'type' => $tipoarquivo,
                    'video' => [
                        'id' => $id_arquivo['id'],
                        'caption' => $message,
                    ],
                ];
            }
}
else
   {
    // dd($tipoarquivo,'sem tipo de arquivo')   ;
    // ===================================== somente texto como resposta
        $requestData = [
           'messaging_product' => 'whatsapp',
           'recipient_type' => 'individual',
           'to' => $phone,
           'type' => 'text',
           'text' => [
               'body' => $message,
           ],
       ];
   }

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
            /////////////// gravar mensagem aprovada

            // $registro = webhookContact::where('recipient_id', $phone)->get()->first();
            $registro->update([
             'status_mensagem_enviada' => 0,
             'user_updated' => $usuario,
           ]);

           $registro->save();

            $newWebhook = webhook::create([
                'webhook' => json_encode($requestData) ?? null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'contactName' => $request->contactName ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'messagesType' => $requestData['type'] ?? null,
                'body' => $requestData['text']['body'] ?? null,
                'status' => 'sent' ?? null,
                'image_caption' => $requestData['image']['caption'] ?? null,
                'image_id' => $requestData['image']['id'] ?? null,
                'document_caption' => $requestData['document']['caption'] ?? null,
                'document_id' => $requestData['document']['id'] ?? null,
                'document_filename' => $name ?? null,
                // 'document_caption' => $document_caption ?? null,
                'video_caption' => $requestData['video']['caption'] ?? null,
                'video_id' => $requestData['video']['id'] ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);



            $recipient_id = $requestData['to'];
            $contactName = $request->contactName;
            $messagesTimestamp = null;
            $newWebhookContact = WebhookServico::AtualizaOuCriaWebhookContact($recipient_id, $contactName, $messagesTimestamp,$entry_id);
            session()->flash('success', 'Mensagem enviada com sucesso para ' . $request->contactName .  '.');


            // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone, $identificacaocontawhatsappbusiness));
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $phone, 'entry_id' => $identificacaocontawhatsappbusiness]));

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
    public function ConvidarMensagemAprovada(string $id)
    {
        $contatos = webhookContact::where('recipient_id', $id)
        ->get();

        // *É com grande satisfação que temos você como nosso cliente.*
        // Agradecemos por isso. Fique à vontade para entrar em contato conosco por esse canal. Te esperamos!😀📲💻🖥

        $WebhookConfig =  WebhookConfig::get();

        $template = WebhookTemplate::where('id', '3')
        ->orderBy('Name')->get();

        return view('api.ConvidarMensagemAprovada', compact('contatos','template', 'id','WebhookConfig'));
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
        $body = $template->texto ;
        $template_id = $template->id;

        $idWebhookConfig = $request->idWebhookConfig;

        // $WebhookConfig =  WebhookConfig::where('ativado','1')
        // ->first();

        $WebhookConfig =  WebhookConfig::find($idWebhookConfig);

        $phone_number_id  = $WebhookConfig->identificacaonumerotelefone;
        $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;

        // dd($WebhookConfig->identificacaocontawhatsappbusiness, $phone_number_id);
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
            'https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages',
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
            $user_updated = trim(Auth::user()->email);

            $newWebhook = webhook::create([
                'webhook' => json_encode($requestData) ?? null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'message_template_name' => $requestData['template']['name'] ?? null,
                'message_template_language' => $requestData['template']['language']['code'] ?? null,
                'status' => 'sent' ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'user_updated' =>  $user_updated ?? null,
                'ContactName'=>   $contactName ?? null,
                'body'=> $body ?? null,
                'template_id'=> $template_id ?? null,
                'messagesType' => 'text',
                'user_atendimento' => Auth::user()->email,
            ]);

            /////////////////////////////// termina a gravação


            // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', $recipient_id,$WebhookConfig->identificacaocontawhatsappbusiness));
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $recipient_id, 'entry_id' => $identificacaocontawhatsappbusiness]));
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
                ///// talvez tenha em = image
                if($registrobd->sticker_mime_type == 'image/webp'){
                    $sufixo = '.webp';
                }
            }

            if($registrobd->messagesType == 'sticker'){

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


            return redirect($filePath);

        } else {
            // Manipule erros, se houver
            echo 'Arquivo não existe mais no servidor.... Ele permanece por 30 dias da data de envio: ' . $response->getBody();
        }
    }


    public function Enviar_Arquivo($arquivo, $path, $name, $extension, $mime_type, $entry_id)
    {
        $accessToken = WebhookServico::token24horas();
        $phone_number_id = WebhookServico::phone_number_id($entry_id);
        $id_arquivo = null;

        $client = new Client();

        // dd(file_get_contents($arquivo), $mime_type);

        if($mime_type !== 'text/plain')
        {
                $response = Http::attach(
                'file', // Nome do campo esperado pela API do Facebook
                file_get_contents($arquivo), // O conteúdo do arquivo
                $name // Nome do arquivo que será enviado
            )->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/media', [
                'access_token' => $accessToken,
                'messaging_product' => 'whatsapp',
            ]);
        }
        elseif($mime_type === 'text/plain')
        {
            $response = Http::attach(
                        'file',
                        file_get_contents($arquivo),
                        $name,
                        ['Content-Type' => 'text/plain'] // Defina explicitamente o tipo MIME como text/plain
                    )->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/media', [
                        'access_token' => $accessToken,
                        'messaging_product' => 'whatsapp',
                    ]);
        }




        if ($response->successful()) {
            // Fazer algo com a resposta
            $id_arquivo = $response->json();
        } else {
            // Lidar com o erro
            $error = $response->body();
            // Log ou retorne o erro conforme necessário
        }

        // dd("Sucesso de envio do arquivo. Id: ",$id_arquivo);
    // dd($id_arquivo, $arquivo);
        return $id_arquivo;
    }



    public function TipoArquivo($mime_type)
    {

        if($mime_type == 'image/jpeg' || $mime_type == 'image/png' || $mime_type == 'image/jpg')
        {
            $tipoarquivo = 'image';

        }
        elseif($mime_type == 'video/mp4' || $mime_type == 'video/3gpp' || $mime_type == 'video/quicktime')
        {
            $tipoarquivo = 'video';

        }
        elseif($mime_type == 'application/pdf'
                || $mime_type == 'text/plain'
                || $mime_type == 'text/csv'
                || $mime_type == 'application/vnd.ms-excel'
                || $mime_type == 'application/msword'
                || $mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
        {
            $tipoarquivo = 'document';

        }
        // else
        // {
        //     $tipoarquivo = 'text';
        // }
// dd( $tipoarquivo);
        return $tipoarquivo;
    }


    public function ConfirmaRecebimentoMensagem($id, $entry_id)
    {
        $registro = webhook::find($id);
        // $entry_id = $registro->entry_id;



        $NomeAtendido =  webhookContact::where('recipient_id', $registro->messagesFrom)
        ->where('entry_id', $entry_id)
        ->get()->first();
       $calculo =  $NomeAtendido->quantidade_nao_lida - 1;
        $NomeAtendido->update([
            'quantidade_nao_lida' => $calculo
            ]);

// dd($registro,$NomeAtendido);

        $accessToken = WebhookServico::token24horas($entry_id);
        $phone_number_id = WebhookServico::phone_number_id($entry_id);

        $client = new Client();
        $requestData = [
            'messaging_product' => 'whatsapp',
            'status' => "read",
            'message_id' => $registro->messages_id,
        ];
        $response = $client->post(
            'https://graph.facebook.com/v18.0/' . $phone_number_id  .'/messages',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
            ]
        );

        if ($response->getStatusCode() == 200) {
               $registro->update([
                'statusconfirmado' => true,
               ]);
            //    dd($registro->messagesFrom,$entry_id );
            // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', $registro->messagesFrom, $entry_id));
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $registro->messagesFrom, 'entry_id' => $entry_id]));

        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
            // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', $registro->recipient_id,  $entry_id));
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $registro->messagesFrom, 'entry_id' => $entry_id]));
    }

    public function StatusMensagemEnviada(string $id)
    {
        $registro = webhookContact::where('recipient_id', $id)->get()->first();
               $registro->update([
                'status_mensagem_enviada' => true,
                'user_updated' => Auth::user()->email,

               ]);
            // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', $registro->recipient_id, $registro->entry_id));
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $registro->recipient_id, 'entry_id' => $registro->entry_id]));
    }

    public function enviarMensagemEncerramentoAtendimento(Request $request, $id, $entry_id)
    {
        $usuario = trim(Auth::user()->email);
        $id_arquivo = null;
        $arquivo = $request->file('arquivo') ?? null;

    //   if($arquivo)
    //   {
    //     $path = $arquivo->getRealPath() ;

    //     $name = $arquivo->getClientOriginalName()  ;
    //     $extension = $arquivo->getClientOriginalExtension()  ;

    //     $mime_type = $arquivo->getMimeType()  ;

    //     $id_arquivo = ApiController::Enviar_Arquivo($arquivo, $path, $name, $extension, $mime_type);
    //   }


        $model = webhook::where('messagesFrom',$id)->first();
        // $entry_id = $model->entry_id;
        // $message = $request->input('mensagem');
        $WebhookConfig =  WebhookConfig::OrderBy('usuario')
        ->where('identificacaocontawhatsappbusiness',$entry_id)
        ->get()->first();
        $phone_number_id = WebhookServico::phone_number_id($entry_id);
        $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
        $Token = $WebhookConfig->token24horas;
        // dd($id, $identificacaocontawhatsappbusiness);

        if($Token == null){
                session()
                ->flash('MensagemNaoPreenchida', 'Token não definido por algum erro. Verifique. Linha 1142!');
                return redirect()->back();
        }
        $client = new Client();
        $phone = $request->recipient_id; // Número de telefone de destino
        $requestData = [];
// ================arquivo em anexo como $responseData

if($id_arquivo){

            $tipoarquivo = ApiController::TipoArquivo($mime_type);

            if($tipoarquivo == 'image'){
                        $requestData = [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $phone,
                        'type' => $tipoarquivo,
                        'image' => [
                            'id' => $id_arquivo['id'],
                            'caption' => $message,
                        ],
                    ];
            }
            elseif($tipoarquivo == 'document')
            {
                    $requestData = [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $phone,
                        'type' => $tipoarquivo,
                         'document' => [
                            'id' => $id_arquivo['id'],
                            'filename' => $name,
                            'caption' => $message,
                        ],
                    ];


            }
            elseif($tipoarquivo == 'video')
            {
                $requestData = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $phone,
                    'type' => $tipoarquivo,
                    'video' => [
                        'id' => $id_arquivo['id'],
                        'caption' => $message,
                    ],
                ];
            }
}
else
   {

    $message = "A nossa conversa foi encerrada por " . Auth::user()->name . ". Caso queira prosseguir é só enviar alguma nova mensagem. Obrigado!";

    $registro = webhookContact::where('recipient_id', $phone)
    ->where('entry_id',$identificacaocontawhatsappbusiness)
    ->get()->first();

    if($registro->user_atendimento !== Auth::user()->email)
    {
     $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ")";
    }

    // dd($tipoarquivo,'sem tipo de arquivo')   ;
    // ===================================== somente texto como resposta
        $requestData = [
           'messaging_product' => 'whatsapp',
           'recipient_type' => 'individual',
           'to' => $phone,
           'type' => 'text',
           'text' => [
               'body' => $message,
           ],
       ];
   }

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
            /////////////// gravar mensagem aprovada

                   $id_contact = $registro->id;
            $registro->update([
             'status_mensagem_enviada' => 0,
             'user_updated' => $usuario,
           ]);

           $registro->save();

            $newWebhook = webhook::create([
                'webhook' => json_encode($requestData) ?? null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'contactName' => $request->contactName ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'messagesType' => $requestData['type'] ?? null,
                'body' => $requestData['text']['body'] ?? null,
                'status' => 'sent' ?? null,
                'image_caption' => $requestData['image']['caption'] ?? null,
                'image_id' => $requestData['image']['id'] ?? null,
                'document_caption' => $requestData['document']['caption'] ?? null,
                'document_id' => $requestData['document']['id'] ?? null,
                'document_filename' => $name ?? null,
                'document_caption' => $document_caption ?? null,
                'video_caption' => $requestData['video']['caption'] ?? null,
                'video_id' => $requestData['video']['id'] ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);

            $recipient_id = $requestData['to'];
            $contactName = $request->contactName;


            session()->flash('success', 'Encerramento do atendimento. Mensagem enviada com sucesso para ' . $request->contactName .  '.');

            $Usuario_atendimento = WebhookServico::grava_user_encerramento_atendimento($id, $identificacaocontawhatsappbusiness);

            // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone, $identificacaocontawhatsappbusiness));
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $phone, 'entry_id' => $identificacaocontawhatsappbusiness]));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }

    public function enviarMensagemInicioAtendimento(Request $request, $id)
    {

        $usuario = trim(Auth::user()->email);
        $nomeatendente = trim(Auth::user()->name);

        $id_arquivo = null;
        $arquivo = $request->file('arquivo') ?? null;

      if($arquivo)
      {
        $path = $arquivo->getRealPath();

        $name = $arquivo->getClientOriginalName()  ;
        $extension = $arquivo->getClientOriginalExtension()  ;

        $mime_type = $arquivo->getMimeType()  ;

        $id_arquivo = ApiController::Enviar_Arquivo($arquivo, $path, $name, $extension, $mime_type);
      }

        $model = webhook::find($id);

        $message = $request->input('mensagem');

            // if (empty($request->input('mensagem'))) {
            //     // O campo de mensagem está vazio, defina a mensagem de erro na sessão.
            //     session()->flash('MensagemNaoPreenchida', 'A mensagem está vazia... necessita de preenchimento!');
            //     return redirect()->back();
            // }



            $WebhookConfig =  WebhookConfig::OrderBy('usuario')->get()->first();
            $phone_number_id = WebhookServico::phone_number_id();
            $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
            $Token = $WebhookConfig->token24horas;


            if($Token == null){
                session()
                ->flash('MensagemNaoPreenchida', 'Token não definido por algum erro. Verifique. Linha 1142!');
                return redirect()->back();
            }


        $client = new Client();
        $phone = $request->recipient_id; // Número de telefone de destino
        $client = new Client();
        $requestData = [];

// ================arquivo em anexo como $responseData

if($id_arquivo){

            $tipoarquivo = ApiController::TipoArquivo($mime_type);

            if($tipoarquivo == 'image'){
                        $requestData = [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $phone,
                        'type' => $tipoarquivo,
                        'image' => [
                            'id' => $id_arquivo['id'],
                            'caption' => $message,
                        ],
                    ];
            }
            elseif($tipoarquivo == 'document')
            {
                    $requestData = [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $phone,
                        'type' => $tipoarquivo,
                         'document' => [
                            'id' => $id_arquivo['id'],
                            'filename' => $name,
                            'caption' => $message,
                        ],
                    ];


            }
            elseif($tipoarquivo == 'video')
            {
                $requestData = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $phone,
                    'type' => $tipoarquivo,
                    'video' => [
                        'id' => $id_arquivo['id'],
                        'caption' => $message,
                    ],
                ];
            }
}
else
   {

    $message = "Olá! Sou " . $nomeatendente . ", estou a vossa disposição. Obrigado por entrar em contato conosco! Me esforçarei para responder o mais breve possível.";
    // dd($tipoarquivo,'sem tipo de arquivo')   ;
    // ===================================== somente texto como resposta
        $requestData = [
           'messaging_product' => 'whatsapp',
           'recipient_type' => 'individual',
           'to' => $phone,
           'type' => 'text',
           'text' => [
               'body' => $message,
           ],
       ];
   }

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
            /////////////// gravar mensagem aprovada

            $registro = webhookContact::where('recipient_id', $phone)
            ->where('entry_id', $identificacaocontawhatsappbusiness)
            ->get()->first();
            $id_contact = $registro->id;
            $registro->update([
             'status_mensagem_enviada' => 0,
             'user_updated' => $usuario,
           ]);

           $registro->save();

            $newWebhook = webhook::create([
                'webhook' => json_encode($requestData) ?? null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'contactName' => $request->contactName ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'messagesType' => $requestData['type'] ?? null,
                'body' => $requestData['text']['body'] ?? null,
                'status' => 'sent' ?? null,
                'image_caption' => $requestData['image']['caption'] ?? null,
                'image_id' => $requestData['image']['id'] ?? null,
                'document_caption' => $requestData['document']['caption'] ?? null,
                'document_id' => $requestData['document']['id'] ?? null,
                'document_filename' => $name ?? null,
                'document_caption' => $document_caption ?? null,
                'video_caption' => $requestData['video']['caption'] ?? null,
                'video_id' => $requestData['video']['id'] ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);

            $recipient_id = $requestData['to'];
            $contactName = $request->contactName;


            session()->flash('success', 'Iniciado o atendimento. Mensagem enviada com sucesso para ' . $request->contactName .  '.');

             $Usuario_atendimento = WebhookServico::grava_user_inicio_atendimento($id, $identificacaocontawhatsappbusiness);

            // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone, $identificacaocontawhatsappbusiness));
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $phone, 'entry_id' => $identificacaocontawhatsappbusiness]));

        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }

    public function TransferirAtendimento(request $request, string $id)
    {
        $contato = webhookContact::find($id);
        $idcontato = $contato->id;

        $UsuarioID = $request->UsuarioID;

        $AvisoTransferencia = User::where('email', $UsuarioID)->get()->first()->whatsapp;

        $TransfereAvisa = WebhookServico::VerificaSessao($AvisoTransferencia,$idcontato);


        $contato->update([
            'transferido_para' => $UsuarioID ,
            'quantidade_nao_lida' => $contato->quantidade_nao_lida+1,
        ]);
        $contato->save();

        $Transfere = WebhookServico::transferiratendimento($id, $UsuarioID);



        return redirect()->back();
    }

    public function CancelarTransferirAtendimento(request $request, string $id)
    {
        $contato = webhookContact::find($id);


        $UsuarioID = $contato->transferido_para;
        // $contato->update([
        //     'transferido_para' => $UsuarioID ,
        //     'quantidade_nao_lida' => $contato->quantidade_nao_lida+1,
        // ]);
        // $contato->save();

        $Transfere = WebhookServico::cancelartransferiratendimento($id, $UsuarioID);

        $TransfereAvisa = WebhookServico::avisocancelamentotransferiratendimento($id, $UsuarioID);

        return redirect()->back();
    }


    public function ReabrirAposEncerramentoAtendimento(Request $request, $id)
    {
        $usuario = trim(Auth::user()->email);
        $id_arquivo = null;
        $arquivo = $request->file('arquivo') ?? null;


      if($arquivo)
      {
        $path = $arquivo->getRealPath() ;
        $name = $arquivo->getClientOriginalName()  ;
        $extension = $arquivo->getClientOriginalExtension()  ;
        $mime_type = $arquivo->getMimeType()  ;
        $id_arquivo = ApiController::Enviar_Arquivo($arquivo, $path, $name, $extension, $mime_type);
      }
        // $idWebhook = $request->recipient_id;
        // $model = webhook::where('messagesFrom',$idWebhook)->first();
        $message = $request->input('mensagem');
        // $entry_id = $model->entry_id;

        $entry_id = $request->entry_id;


        $WebhookConfig =  WebhookConfig::where('identificacaocontawhatsappbusiness',$entry_id)
        ->get()->first();
        // $phone_number_id = WebhookServico::phone_number_id($entry_id);
        $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
        $Token = $WebhookConfig->token24horas;
            // dd($entry_id, $WebhookConfig, $model, $idWebhook, $phone_number_id  );

        if($Token == null){
                session()
                ->flash('MensagemNaoPreenchida', 'Token não definido por algum erro. Verifique. Linha 1142!');
                return redirect()->back();
        }





        $phone = $request->recipient_id; // Número de telefone de destino
// dd($entry_id,$phone_number_id, $phone);


        $client = new Client();
        $requestData = [];
// ================arquivo em anexo como $responseData
if($id_arquivo){

            $tipoarquivo = ApiController::TipoArquivo($mime_type);

            if($tipoarquivo == 'image'){
                        $requestData = [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $phone,
                        'type' => $tipoarquivo,
                        'image' => [
                            'id' => $id_arquivo['id'],
                            'caption' => $message,
                        ],
                    ];
            }
            elseif($tipoarquivo == 'document')
            {
                    $requestData = [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $phone,
                        'type' => $tipoarquivo,
                         'document' => [
                            'id' => $id_arquivo['id'],
                            'filename' => $name,
                            'caption' => $message,
                        ],
                    ];


            }
            elseif($tipoarquivo == 'video')
            {
                $requestData = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $phone,
                    'type' => $tipoarquivo,
                    'video' => [
                        'id' => $id_arquivo['id'],
                        'caption' => $message,
                    ],
                ];
            }
}
else
   {
    $message = "A nossa conversa foi aberta/reaberta por " . Auth::user()->name . ". Caso queira prosseguir é só enviar alguma nova mensagem. Obrigado!";
    // dd($tipoarquivo,'sem tipo de arquivo')   ;
    // ===================================== somente texto como resposta
        $requestData = [
           'messaging_product' => 'whatsapp',
           'recipient_type' => 'individual',
           'to' => $phone,
           'type' => 'text',
           'text' => [
               'body' => $message,
           ],
       ];
   }

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
            /////////////// gravar mensagem aprovada

            $registro = webhookContact::where('recipient_id', $phone)
            ->where('entry_id', $identificacaocontawhatsappbusiness)
            ->get()->first();

            $id_contact = $registro->id;
            $registro->update([
             'status_mensagem_enviada' => 1,
             'user_updated' => $usuario,
             'quantidade_nao_lida' => $registro->quantidade_nao_lida+1,
             'user_atendimento' => Auth::user()->email,
           ]);

           $registro->save();

            $newWebhook = webhook::create([
                'webhook' => json_encode($requestData) ?? null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'contactName' => $request->contactName ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'messagesType' => $requestData['type'] ?? null,
                'body' => $requestData['text']['body'] ?? null,
                'status' => 'sent' ?? null,
                'image_caption' => $requestData['image']['caption'] ?? null,
                'image_id' => $requestData['image']['id'] ?? null,
                'document_caption' => $requestData['document']['caption'] ?? null,
                'document_id' => $requestData['document']['id'] ?? null,
                'document_filename' => $name ?? null,
                'document_caption' => $document_caption ?? null,
                'video_caption' => $requestData['video']['caption'] ?? null,
                'video_id' => $requestData['video']['id'] ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);

            $recipient_id = $requestData['to'];
            $contactName = $request->contactName;

            session()->flash('success', 'Encerramento do atendimento. Mensagem enviada com sucesso para ' . $request->contactName .  '.');

            $Usuario_atendimento = WebhookServico::grava_user_inicio_atendimento($id,$identificacaocontawhatsappbusiness);

            // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone, $identificacaocontawhatsappbusiness));
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $phone, 'entry_id' => $identificacaocontawhatsappbusiness]));
        } else {
            // Manipule erros, se houver
            echo 'Erro ao enviar a mensagem: ' . $response->getBody();
        }
    }

///////////////////////////////////////////
public function enviarMensagemEncerramentoAtendimentoSemAviso(Request $request, $id)
{

    $usuario = trim(Auth::user()->email);
    $id_arquivo = null;
    $arquivo = $request->file('arquivo') ?? null;
    $phone = $request->recipient_id;

    $model = webhook::find($id);


    $registro = webhookContact::where('recipient_id', $phone)->get()->first();
    $id_contact = $registro->id;


    $registro->update([
     'status_mensagem_enviada' => 1,
     'user_updated' => $usuario,
     'quantidade_nao_lida' => 0,
     'user_atendimento' => NULL,

   ]);

   $registro->save();

    session()->flash('success', 'Encerramento do atendimento. Mensagem enviada com sucesso para ' . $request->contactName .  '.');

    // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone, $registro->entry_id));
    return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $phone, 'entry_id' => $registro->entry_id]));

}





}
