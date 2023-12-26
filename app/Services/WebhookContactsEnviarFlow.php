<?php
namespace App\Services;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\webhook;
use App\Models\WebhookContact;
use App\Models\WebhookConfig;
use App\Models\WebhookTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DateTime;

class WebhookContactsEnviarFlow
{

    public static function EnviaMensagemFlowMenuCadastroBasico($recipient_id, $entry_id)
    {
      // DADOS DO FLOW CRIADO A MENSAGEM = ID 348317521263758
      // {
      //   "id": "744795220448470",
      //   "status": "PENDING",
      //   "category": "MARKETING"
      // }
      $flow_token = '1145104546467989';
        $flow_name = 'menu_cadastro_basico_formandos_afins';
        $flow_description = 'Enviado o flow  menu_cadastro_basico_formandos_afins , token 1145104546467989';
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }

  public static function EnviaMensagemFlowCadastro($recipient_id, $entry_id)
  {

      $flow_token = '2120367534804891';
      $flow_name = 'cadastro_de_atletas';
      $flow_description = 'Enviado o flow  cadastro_de_atleta, token 2120367534804891';

      WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );


  }

    public static function EnviaMensagemFlowAlterarCpf($recipient_id, $entry_id)
    {
        $flow_token = '372275572014981';
        $flow_name = 'cadastro_alterar_cpf';
        $flow_description = 'Enviado o flow  cadastro_alterar_cpf, token 372275572014981';
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }


    public static function EnviaMensagemFlowAlterarRg($recipient_id, $entry_id)
    {
      // DADOS DO FLOW CRIADO A MENSAGEM = ID 348317521263758
      // {
      //   "id": "1284925575611992",
      //   "status": "PENDING",
      //   "category": "MARKETING"
      // }
      $flow_token = '348317521263758';
        $flow_name = 'cadastro_alterar_rg';
        $flow_description = 'Enviado o flow  cadastro_alterar_rg, token 348317521263758';
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }

    public static function EnviaMensagemFlowAlterarCidadeUf($recipient_id, $entry_id)
    {
      // DADOS DO FLOW CRIADO A MENSAGEM = ID 381547034317574
      // {
      //   "id": "904034640924140",
      //   "status": "PENDING",
      //   "category": "MARKETING"
      // }
      $flow_token = '381547034317574';
        $flow_name = 'cadastro_alterar_cidade_uf';
        $flow_description = 'Enviado o flow  cadastro_alterar_cidade_uf, token 381547034317574';
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }

    public static function EnviaMensagemFlowAlterarNome($recipient_id, $entry_id)
    {
      // DADOS DO FLOW CRIADO A MENSAGEM = ID 381547034317574
      // {
      //   "id": "896152218616381",
      //   "status": "PENDING",
      //   "category": "MARKETING"
      // }
      $flow_token = '1434146677313794';
        $flow_name = 'cadastro_alterar_nome_completo';
        $flow_description = 'Enviado o flow  cadastro_alterar_nome_completo, token 1434146677313794';
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }


    public static function  EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id)
    {

      $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
      ->OrderBy('usuario')
      ->get()
      ->first();
      $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
      $Token = $WebhookConfig->token24horas;

      $client = new Client();
      $requestData = [];

      $requestData = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $recipient_id,
        'type' => 'template',
        'template' => [
            'name' => $flow_name,
            'language' => [
                'code' => 'pt_BR'
            ],
            'components' => [
                [
                    'type' => 'button',
                    'sub_type' => 'flow',
                    'index' => '0',
                    'parameters' => [
                        [
                            'type' => 'action',
                            'action' => [
                                'flow_token' => $flow_token,
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
        $response = $client->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $Token,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);

        if ($response->getStatusCode() == 200) {
            Log::info('Flow enviado com sucesso!');

            $contatos = WebhookContact::where('recipient_id', $recipient_id)
            ->where('entry_id', $entry_id)
            ->first();

            $newWebhook = webhook::create([
              'webhook' => json_encode($requestData) ?? null,
              'value_messaging_product' => $requestData['messaging_product'] ?? null,
              'object' => $requestData['messaging_product'] ?? null,
              'entry_id' => $entry_id?? null,
              'contactName' => $contatos->contactName ?? null,
              'recipient_id' => $requestData['to'] ?? null,
              'type' => $requestData['type'] ?? null,
              'messagesType' => $requestData['type'] ?? null,
              'body' => $flow_description,
              'status' => 'sent' ?? null,
              'user_atendimento' => Auth::user()->email,
              'flow_token' => $flow_token,
              'flow_description'=> $flow_description,
          ]);
        }
    }

    public static function montabodyflow($data, $messagesTimestamp)
    {

        $nome = $data['nome'] ?? null;
        $dataNascimento = $data['dataNascimento'] ?? null;
        $dataNascimentoObj = DateTime::createFromFormat('d/m/Y', $dataNascimento);
        $flow_token = $data['flow_token'] ?? null;
        $nomePai = $data['nomePai'] ?? null;
        $nomeMae = $data['nomeMae'] ?? null;
        $Cpf = $data['Cpf'] ?? null;
        $Rg = $data['Rg'] ?? null;
        $flow_description = $data['description'] ?? null;
        $flow_token = $data['flow_token'] ?? null;
        $messagesTimestamp = $data['messagesTimestamp'] ?? null;
        $topicRadio = $data['topicRadio'] ?? null;

      $body = '';

                if ($nome) {
                    $body .= 'Nome: ' . $nome . " | ";
                }
                if ($dataNascimento) {
                    $body .= 'Data de Nascimento: ' . $dataNascimento . " | ";
                }
                if ($nomePai) {
                    $body .= 'Nome do Pai: ' . $nomePai . " | ";
                }
                if ($nomeMae) {
                    $body .= 'Nome da Mãe: ' . $nomeMae . " | ";
                }
                if ($flow_description) {
                    $body .= 'Descrição: ' . $flow_description . " | ";
                }
                if ($flow_token) {
                    $body .= 'Flow Token: ' . $flow_token . " | ";
                }
                if ($Cpf) {
                    $body .= 'CPF: ' . $Cpf . " | ";
                }
                if ($Rg) {
                    $body .= 'RG: ' . $Rg . " | ";
                }
                if ($topicRadio) {
                    $body .= 'Opção cadastro: ' . $topicRadio . " | ";
                }
                if ($messagesTimestamp) {
                    $body .= 'Código registro: ' . $messagesTimestamp . " | ";
                }

                // Remover o último " | " se necessário
                $body = rtrim($body, " | ");

                return $body;
    }


}

