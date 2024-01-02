<?php
namespace App\Services;

use App\Models\FormandoBaseWhatsapp;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\webhook;
use App\Models\WebhookContact;
use App\Models\WebhookConfig;
use App\Models\WebhookTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DateTime;
use PhpParser\Node\Stmt\Foreach_;

class WebhookContactsEnviarFlow
{

    public static function EnviaMensagemFlowMenuCadastroBasico($recipient_id, $entry_id)
    {

        if($entry_id == '189514994242034')
        {

            // DADOS DO FLOW CRIADO A MENSAGEM = ID 1145104546467989 / 383392457504361
            // {
            //   "id": "744795220448470", // 25366076489658611
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '383392457504361';
            $flow_name = 'menu_cadastro_com_opcoes';
            $flow_description = 'Enviado o flow  menu_cadastro_com_opcoes , token '. $flow_token;
        }else
        if($entry_id == '179613235241221')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID 867844158418937
            // {
            //   "id": 676937214523206
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '867844158418937';
            $flow_name = 'menu_cadastro_com_opcoes';
            $flow_description = 'Enviado o flow  menu_cadastro_com_opcoes , token '. $flow_token;
        }
        WebhookContactsEnviarFlow::
        EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }

    public static function EnviaMensagemEstamosTrabalhando($recipient_id, $entry_id)
    {

      $flow_token = '';
        // $flow_name = 'menu_cadastro_basico_formandos_afins';

        $flow_name = 'trabalhando_para_mais_opcoes';
        $flow_description = 'Enviado trabalhando_para_mais_opcoes';
        WebhookContactsEnviarFlow::
        EnviaMensagemGravaTemplate($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }


  public static function EnviaMensagemFlowCadastro($recipient_id, $entry_id)
  {
        if($entry_id == '189514994242034')
        {

            $flow_token = '2120367534804891';
            $flow_name = 'cadastro_de_atletas';
            $flow_description = 'Enviado o flow  EnviaMensagemFlowCadastro , token '. $flow_token;
        }else
        if($entry_id == '179613235241221')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID 380310001057380
            // {
            //   "id": 1309532939706842
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '380310001057380';
            $flow_name = 'cadastro_de_atletas';
            $flow_description = 'Enviado o flow  EnviaMensagemFlowCadastro , token '. $flow_token;
        }
      WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
  }

    public static function EnviaMensagemFlowAlterarCpf($recipient_id, $entry_id)
    {
        if($entry_id == '189514994242034')
        {
            $flow_token = '372275572014981';
            $flow_name = 'cadastro_alterar_cpf';
            $flow_description = 'Enviado o flow  cadastro_alterar_cpf , token '. $flow_token;

        }else
        if($entry_id == '179613235241221')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID 1427482604781479
            // {
            //   "id": 392895259868351
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '1427482604781479';
            $flow_name = 'cadastro_alterar_cpf';
            $flow_description = 'Enviado o flow  cadastro_alterar_cpf , token '. $flow_token;
        }
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }


    public static function EnviaMensagemFlowAlterarRg($recipient_id, $entry_id)
    {

        if($entry_id == '189514994242034')
        {
                // DADOS DO FLOW CRIADO A MENSAGEM = ID 348317521263758
        // {
        //   "id": "1284925575611992",
        //   "status": "PENDING",
        //   "category": "MARKETING"
        // }
            $flow_token = '348317521263758';
            $flow_name = 'cadastro_alterar_rg';
            $flow_description = 'Enviado o flow  cadastro_alterar_rg , token '. $flow_token;
        }else
        if($entry_id == '179613235241221')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID 1438375836712494
            // {
            //   "id": 908952390359202
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '1438375836712494';
            $flow_name = 'cadastro_alterar_rg';
            $flow_description = 'Enviado o flow  cadastro_alterar_rg , token '. $flow_token;
        }
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }

    public static function EnviaMensagemFlowAlterarCidadeUf($recipient_id, $entry_id)
    {
        if($entry_id == '189514994242034')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID 381547034317574
            // {
            //   "id": "904034640924140",
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '381547034317574';
            $flow_name = 'cadastro_alterar_cidade_uf';
            $flow_description = 'Enviado o flow  cadastro_alterar_cidade_uf , token '. $flow_token;
        }else
        if($entry_id == '179613235241221')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID  358895973412960
            // {
            //   "id":   1025136535245872
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '358895973412960';
            $flow_name = 'cadastro_alterar_cidade_uf';
            $flow_description = 'Enviado o flow  cadastro_alterar_cidade_uf , token '. $flow_token;
        }
           WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }

    public static function EnviaMensagemFlowAlterarNascimento($recipient_id, $entry_id)
    {
        if($entry_id == '189514994242034')
        {
           // DADOS DO FLOW CRIADO A MENSAGEM = ID 3496535283943398
            // {
            //   "id": "317767004567130",
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '3496535283943398';
            $flow_name = 'cadastro_alterar_data_de_nascimento';
            $flow_description = 'Enviado o flow  cadastro_alterar_data_de_nascimento , token '. $flow_token;
        }else
        if($entry_id == '179613235241221')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID  1478186329427489
            // {
            //   "id":  897015888636132
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '1478186329427489';
            $flow_name = 'cadastro_alterar_data_de_nascimento';
            $flow_description = 'Enviado o flow  cadastro_alterar_data_de_nascimento , token '. $flow_token;
        }
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }



    public static function EnviaMensagemFlowAlterarNome($recipient_id, $entry_id)
    {
        if($entry_id == '189514994242034')
        {
          // DADOS DO FLOW CRIADO A MENSAGEM = ID 381547034317574
        // {
        //   "id": "896152218616381",
        //   "status": "PENDING",
        //   "category": "MARKETING"
        // }
        $flow_token = '1434146677313794';
        $flow_name = 'cadastro_alterar_nome_completo';
        $flow_description = 'Enviado o flow' .  $flow_name . ', token '. $flow_token;
        }else
        if($entry_id == '179613235241221')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID 680413520953704
            // {
            //   "id":  668410952126259
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '680413520953704';
            $flow_name = 'cadastro_alterar_nome_completo';
            $flow_description = 'Enviado o flow' .  $flow_name . ', token '. $flow_token;
        }
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }
    public static function EnviaMensagemFlowAlterarNomeMae($recipient_id, $entry_id)
    {
        if($entry_id == '189514994242034')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID 338160952497179
            // {
            //   "id": "728728702527379",
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '338160952497179';
            $flow_name = 'cadastro_alterar_nome_da_mae';
            $flow_description = 'Enviado o flow' .  $flow_name . ', token '. $flow_token;
        }else
        if($entry_id == '179613235241221')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID  213814095142059
            // {
            //   "id": 845106654030865
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '213814095142059';
            $flow_name = 'cadastro_alterar_nome_da_mae';
            $flow_description = 'Enviado o flow' .  $flow_name . ', token '. $flow_token;
        }
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );
    }
    public static function EnviaMensagemFlowAlterarNomePai($recipient_id, $entry_id)
    {
        if($entry_id == '189514994242034')
        {
                // DADOS DO FLOW CRIADO A MENSAGEM = ID 1082401946512589
            // {
            //   "id": "1743916812696917",
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '1082401946512589';
            $flow_name = 'cadastro_alterar_nome_de_pai';
            $flow_description = 'Enviado o flow' .  $flow_name . ', token '. $flow_token;
        }else
        if($entry_id == '179613235241221')
        {
            // DADOS DO FLOW CRIADO A MENSAGEM = ID 311444757904313
            // {
            //   "id": 777116537590184
            //   "status": "PENDING",
            //   "category": "MARKETING"
            // }
            $flow_token = '311444757904313';
            $flow_name = 'cadastro_alterar_nome_de_pai';
            $flow_description = 'Enviado o flow' .  $flow_name . ', token '. $flow_token;
        }
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
              'body' => $flow_description ?? null,
              'status' => 'sent' ?? null,
              'user_atendimento' => Auth::user()->email ?? null,
              'flow_token' => $flow_token ?? null,
              'flow_description'=> $flow_description ?? null,
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


    public static function  EnviaMensagemGravaDados($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id, $mensagem)
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
        'to' => $recipient_id,
        'type' => 'text',
        'text' => [
            'body' => $mensagem,
        ],
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
              'body' => $mensagem ?? null,
              'status' => 'sent' ?? null,
              'user_atendimento' => Auth::user()->email ?? null,
              'flow_token' => $flow_token ?? null,
              'flow_description'=> $flow_description ?? null,
          ]);
        }
    }



    public static function EnviaMensagemDadosCadastroBasico($recipient_id, $entry_id)
    {
        $CadastroBasico = FormandoBaseWhatsapp::where([
            'recipient_id' => $recipient_id,
            'entry_id' => $entry_id
        ])
        ->get();


dd  ($CadastroBasico);

        $mensagem = "📋 *Dados do Cadastro Básico*\n\n";

        // Adiciona cabeçalhos
        $mensagem .= "👤 *Código registro* |👤 *Nome* | 🎂 *Data Nasc.* | 👨‍👦 *Pai* | 👩‍👦 *Mãe* | 🆔 *CPF* | 🆔 *RG* | 🕒 *Cidade* | 📻 *UF*\n";

        foreach ($CadastroBasico as $Cadastro) {
            $mensagem .= sprintf(
                "%s |%s | %s | %s | %s | %s | %s | %s | %s\n",
                $Cadastro->codigo_registro,
                $Cadastro->nome,
                $Cadastro->dataNascimento,
                $Cadastro->nomePai,
                $Cadastro->nomeMae,
                $Cadastro->Cpf,
                $Cadastro->Rg,
                $Cadastro->cidade,
                $Cadastro->UF
            );
        }



        // Enviar a mensagem via WhatsApp aqui
        // Exemplo: WhatsappApi::enviarMensagem($recipient_id, $entry_id ,$mensagem);

        // WebhookServico::avisoInteractiveCadastrado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome);


        $flow_token = null;
        $flow_name = 'Dados do Cadastro Básico';
        $flow_description = 'Enviado os dados do cadastro básico.';
        WebhookContactsEnviarFlow::EnviaMensagemGrava($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id );

    }


    public static function EnviaMensagemMeusDadosCadastroBasico($recipient_id, $entry_id)
    {
        // $CadastroBasico = FormandoBaseWhatsapp::where('telefone', $recipient_id)
        // ->where('entry_id', $entry_id)
        // ->get();

        $CadastroBasico = FormandoBaseWhatsapp::where([
            'telefone' => $recipient_id,
            'entry_id' => $entry_id
        ])
        ->get();


        $mensagem = "📋 *Dados do Cadastro Básico*\n\n";

        // Adiciona cabeçalhos
        // $mensagem .= "👤 *Código registro* |👤 *Nome* | 🎂 *Data Nasc.* | 👨‍👦 *Pai* | 👩‍👦 *Mãe* | 🆔 *CPF* | 🆔 *RG* | 🕒 *Cidade* | 📻 *UF*\n";
        $mensagem .= "\n";

        foreach ($CadastroBasico as $Cadastro) {

            if($Cadastro->nascimento){
                $dtn = "Data de nascimento: ".trim($Cadastro->nascimento->format('d/m/Y'))."\n";
            }
            else
            {
                $dtn = "Data de nascimento: "."\n";
            }
            $mensagem .= sprintf(
                "%s | %s | %s | %s | %s | %s | %s | %s | %s\n",
                "Código de registro: ".trim($Cadastro->codigo_registro)."\n",
                "Nome: ". trim($Cadastro->nome)."\n",
                $dtn,
                "Nome do pai: ".trim($Cadastro->nomePai)."\n",
                "Nome da mãe: ".trim($Cadastro->nomeMae)."\n",
                "CPF: ". trim($Cadastro->cpf)."\n",
                "RG: ".trim($Cadastro->rg)."\n",
                "Cidade: ".trim($Cadastro->cidade)."\n",
                "UF/Estado: ".trim($Cadastro->uf)."\n",
            );
        }




        $flow_token = null;
        $flow_name = 'Dados do Cadastro Básico';
        $flow_description = 'Enviado os dados do cadastro básico.';
        WebhookContactsEnviarFlow::EnviaMensagemGravaDados($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id, $mensagem);

    }

    public static function  EnviaMensagemGravaTemplate($flow_token, $flow_name, $flow_description, $recipient_id, $entry_id)
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
        'to' => $recipient_id,
        'type' => 'template',
        'template' => [
            'name' => $flow_name,
            'language' => [
                'code' => 'pt_BR',
            ],
        ],
    ];
    Log::info('Template:'. $flow_name);

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
              'body' => $flow_description ?? null,
              'status' => 'sent' ?? null,
              'user_atendimento' => Auth::user()->email ?? null,
              'flow_token' => $flow_token ?? null,
              'flow_description'=> $flow_description ?? null,
          ]);
        }
    }


}

