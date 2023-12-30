<?php
namespace App\Services;

use App\Models\FormandoBaseWhatsapp;
use App\Models\webhookAtendimentoEncerrado;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\webhook;
use App\Models\WebhookContact;
use App\Models\WebhookConfig;
use App\Models\WebhookTemplate;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class WebhookServico
{
    public static function AtualizaOuCriaWebhookContact($recipient_id, $contactName, $messagesTimestamp, $entry_id)
    {
        $newWebhookContact = WebhookContact::where('recipient_id', $recipient_id)
            ->where('entry_id', $entry_id)
            ->first();

        if ($newWebhookContact) {
            $newWebhookContact->update([
                // 'contactName' => $contactName ?? null,
                'user_updated' => Auth::user()->email ?? null,
            ]);
        } else {
            if ($recipient_id) {
                $newWebhookContact = WebhookContact::create([
                    'contactName' => $contactName ?? null,
                    'recipient_id' => $recipient_id ?? null,
                    'user_updated' => Auth::user()->email ?? null,
                    'timestamp' => $messagesTimestamp,
                    'entry_id' => $entry_id,
                ]);

                Log::info('Criado novo contato' . $recipient_id . ' - ' . $contactName);
            }
        }

        Log::info($messagesTimestamp);
        if ($messagesTimestamp != null) {
            $newWebhookContact->update([
                'timestamp' => $messagesTimestamp,
            ]);
        }

        return $newWebhookContact;
    }

    public static function Agradecimento_por_ter_lido_mensagem_recebida($recipient_id, $accessToken)
    {
        $client = new Client();
        // $phone = $recipient_id;
        $phone = '5517997662949';
        $client = new Client();
        $requestData = [];
        $message = 'Agradecemos por ter visto nossa mensagem!';
        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];
        $response = $client->post('https://graph.facebook.com/v17.0/147126925154132/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);

        if ($response->getStatusCode() == 200) {
        }
    }

    public static function Token24horas($entry_id)
    {
        $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
            ->OrderBy('usuario')
            ->get()
            ->first();
        $accessToken = $WebhookConfig->token24horas;
        return $accessToken;
    }

    public static function accessToken($entry_id)
    {
        $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', trim($entry_id))->first();
        $accessToken = $WebhookConfig->token24horas;
        return $accessToken;
    }

    public static function phone_number_id($entry_id)
    {
        $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', trim($entry_id))->first();
        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
        return $phone_number_id;
    }

    public static function grava_user_atendimento($id)
    {
        $Usuario_sistema = Auth::user()->email;

        $User_Atendente = WebhookContact::where('recipient_id', $id)->first();
        if (!empty($User_Atendente->user_atendimento)) {
            if ($User_Atendente->user_atendimento !== $Usuario_sistema) {
                if (session('usuarioatendente') == null) {
                    session(['usuarioatendente' => 'Cliente sendo atendido por: ' . $User_Atendente->user_atendimento]);
                }

                return;
            }
        } else {
            if ($User_Atendente) {
                $User_Atendente->update([
                    'user_atendimento' => $Usuario_sistema,
                    'transferido_para' => null,
                ]);
            }
        }

        return;
    }

    public static function grava_user_encerramento_atendimento($id, $identificacaocontawhatsappbusiness)
    {
        $User_Atendente = WebhookContact::where('recipient_id', $id)
            ->where('entry_id', $identificacaocontawhatsappbusiness)
            ->first();

        if ($User_Atendente) {
            $User_Atendente->update([
                'user_atendimento' => null,
                'quantidade_nao_lida' => 0,
                'transferido_para' => null,
            ]);
        }

        $webhookAtendimentoEncerrado = webhookAtendimentoEncerrado::create([
            'id_contact' => $User_Atendente->id ?? null,
            'user_atendimento' => Auth::user()->email ?? null,
            'inicio_atendimento' => false,
            'fim_atendimento' => true,
        ]);
        return;
    }

    public static function grava_user_inicio_atendimento($id, $identificacaocontawhatsappbusiness)
    {
        $Usuario_sistema = Auth::user()->email;

        $User_Atendente = WebhookContact::where('recipient_id', $id)
            ->where('entry_id', $identificacaocontawhatsappbusiness)
            ->first();

        if ($User_Atendente) {
            $User_Atendente->update([
                'user_atendimento' => $Usuario_sistema,
                'quantidade_nao_lida' => $User_Atendente->quantidade_nao_lida - 1,
                'transferido_para' => null,
            ]);
        }

        $webhookAtendimentoEncerrado = webhookAtendimentoEncerrado::create([
            'id_contact' => $User_Atendente->id ?? null,
            'user_atendimento' => Auth::user()->email ?? null,
            'inicio_atendimento' => true,
            'fim_atendimento' => false,
        ]);

        return;
    }

    public function refreshpagina($id)
    {
        $User_Atendente = WebhookContact::where('recipient_id', $id)->first();

        if ($User_Atendente) {
            if ($User_Atendente->pagina_refresh == null || $User_Atendente->pagina_refresh == false) {
                $atualiza_pagina = true;
            } else {
                $atualiza_pagina = false;
            }

            $User_Atendente->update([
                'pagina_refresh' => $atualiza_pagina,
            ]);
        }

        return redirect()->back();
    }

    public function PesquisaMensagens($id, Request $request)
    {
        $recipient_id = $id;
        $textopesquisar = request()->input('pesquisar_mensagem');
        $entry_id = request()->input('entry_id');
        $quantidademensagem = request()->input('quantidademensagem');

        if (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127') && Gate::allows('WHATSAPP_ENTRY_ID_189514994242034')) {
            $selecao = Webhook::limit($quantidademensagem)
                ->whereIn('entry_id', ['167722543083127', '189514994242034'])
                ->where(function ($query) use ($recipient_id) {
                    $query->where('recipient_id', $recipient_id)->orWhere('messagesFrom', $recipient_id);
                })
                ->where('body', 'like', '%' . $textopesquisar . '%')
                ->orderBy('created_at', 'desc')
                ->get();

            $QuantidadeCanalAtendimento = 2;

            $RegistrosContatos = webhookContact::where('ocultar_lista_atendimento', null)
                ->whereIn('entry_id', ['167722543083127', '189514994242034'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } elseif (Gate::allows('WHATSAPP_ENTRY_ID_189514994242034')) {
            $selecao = Webhook::limit($quantidademensagem);
            $selecao = $selecao->whereIn('entry_id', ['189514994242034'])->where(function ($query) use ($recipient_id) {
                $query->where('recipient_id', $recipient_id)->orWhere('messagesFrom', $recipient_id);
            });

            if ($textopesquisar) {
                $selecao = $selecao->where('body', 'like', '%' . $textopesquisar . '%');
            }

            $selecao = $selecao->orderBy('created_at', 'desc')->get();

            $QuantidadeCanalAtendimento = 2;

            $RegistrosContatos = webhookContact::where('ocultar_lista_atendimento', null)
                ->whereIn('entry_id', ['189514994242034'])
                ->orderBy('updated_at', 'desc')
                ->get();
            $QuantidadeCanalAtendimento = 2;

            $RegistrosContatos = webhookContact::where('ocultar_lista_atendimento', null)
                ->whereIn('entry_id', ['189514994242034'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } elseif (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127')) {
            $selecao = Webhook::limit($quantidademensagem);
            $selecao = $selecao->whereIn('entry_id', ['167722543083127'])->where(function ($query) use ($recipient_id) {
                $query->where('recipient_id', $recipient_id)->orWhere('messagesFrom', $recipient_id);
            });

            if ($textopesquisar) {
                $selecao = $selecao->where('body', 'like', '%' . $textopesquisar . '%');
            }

            $selecao = $selecao->orderBy('created_at', 'desc')->get();

            $QuantidadeCanalAtendimento = 2;

            $RegistrosContatos = webhookContact::where('ocultar_lista_atendimento', null)
                ->whereIn('entry_id', ['167722543083127'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        // dd($quantidademensagem, $selecao);

        $Usuarios = User::where('email', '!=', Auth::user()->email)
            ->where('atendente_whatsapp', 1)
            ->orderBy('name')
            ->get();

        $NomeAtendido = WebhookContact::where('recipient_id', $id)->first();

        $resultado = WebhookServico::temposessao($NomeAtendido);
        $tempo_em_segundos = $resultado['tempo_em_segundos'];
        $tempo_em_horas = $resultado['tempo_em_horas'];
        $tempo_em_minutos = $resultado['tempo_em_minutos'];
        $parte_inteira = $resultado['parte_inteira'];
        $parte_decimal = $resultado['parte_decimal'];
        $parte_decimal_minutos = $resultado['parte_decimal_minutos'];

        return view('Api.atendimentoWhatsappFiltro', compact('id', 'NomeAtendido', 'RegistrosContatos', 'QuantidadeCanalAtendimento', 'tempo_em_segundos', 'tempo_em_horas', 'tempo_em_minutos', 'parte_inteira', 'parte_decimal', 'parte_decimal_minutos', 'Usuarios', 'selecao', 'textopesquisar'));
    }

    public static function temposessao($NomeAtendido)
    {
        $tempo_em_segundos = null;
        $tempo_em_horas = null;
        $tempo_em_minutos = null;

        if ($NomeAtendido->timestamp) {
            $tempo_em_segundos = strtotime(now()) - $NomeAtendido->timestamp;
            $tempo_em_horas = $tempo_em_segundos / 3600;
            $tempo_em_minutos = $tempo_em_segundos / 60;
        }

        $numero = $tempo_em_horas;
        $partes = explode('.', $numero);
        $parte_inteira = (int) $partes[0];
        $parte_decimal = isset($partes[1]) ? (float) ('0.' . $partes[1]) : 0;
        $parte_decimal_minutos = round($parte_decimal * 60);

        return [
            'tempo_em_segundos' => $tempo_em_segundos,
            'tempo_em_horas' => $tempo_em_horas,
            'tempo_em_minutos' => $tempo_em_minutos,
            'parte_inteira' => $parte_inteira,
            'parte_decimal' => $parte_decimal,
            'parte_decimal_minutos' => $parte_decimal_minutos,
        ];
    }

    public static function transferiratendimento($id, $UsuarioID)
    {
        $usuario = trim(Auth::user()->email);
        $User = user::where('email', $UsuarioID)->first();
        $NomeAtendente = $User->name;

        $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
            ->OrderBy('usuario')
            ->get()
            ->first();

        $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
        $Token = $WebhookConfig->token24horas;

        $webhootContact = webhookcontact::find($id);
        $client = new Client();
        $phone = $webhootContact->recipient_id; // Número de telefone de destino
        $client = new Client();
        $requestData = [];

        $message = 'A nossa conversa foi transferida para outro usuário: ' . $NomeAtendente . '. Aguarde que já o mesmo atenderá! Caso queira prosseguir é só enviar alguma nova mensagem. Obrigado!';

        if ($webhootContact->user_atendimento !== Auth::user()->email) {
            $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ')';
        }
        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];
        $response = $client->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $Token,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);
        // Verifique a resposta
        if ($response->getStatusCode() == 200) {
            $responseData = json_decode($response->getBody());
            // Faça algo com a resposta, se necessário
            // dd("Mensagem nova enviada", $responseData);
            ///////////////////Gravar
            //  $registro = webhookContact::where('recipient_id', $phone)->get()->first();
            $registro = $webhootContact;

            $registro->update([
                'status_mensagem_enviada' => 0,
                'user_updated' => $usuario,
                'quantidade_nao_lida' => $registro->quantidade_nao_lida + 1,
            ]);

            $registro->save();

            $newWebhook = webhook::create([
                'webhook' => null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'contactName' => $registro->contactName ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'messagesType' => $requestData['type'] ?? null,
                'body' => $requestData['text']['body'] ?? null,
                'status' => 'sent' ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);
        }
        //  return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone));
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $phone, 'entry_id' => $identificacaocontawhatsappbusiness]));
    }

    public static function cancelartransferiratendimento($id, $UsuarioID)
    {
        $usuario = trim(Auth::user()->email);
        $User = null;
        if ($UsuarioID) {
            $User = user::where('email', $UsuarioID)->first();
            $NomeAtendente = $User->name;
        }
        $webhootContact = webhookcontact::find($id);

        $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
            ->OrderBy('usuario')
            ->get()
            ->first();

        $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
        $Token = $WebhookConfig->token24horas;

        $webhootContact = webhookcontact::find($id);

        $client = new Client();
        $phone = $webhootContact->recipient_id; // Número de telefone de destino
        $client = new Client();
        $requestData = [];

        $message = 'A transferência para o usuário ' . $webhootContact->transferido_para . ' foi cancelada. Continue com o atendente ' . $usuario . '(comigo). Caso queira é só enviar alguma nova mensagem. Obrigado!';

        if ($webhootContact->user_atendimento !== Auth::user()->email) {
            $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ')';
        }

        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];
        $response = $client->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $Token,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);
        // Verifique a resposta
        if ($response->getStatusCode() == 200) {
            $responseData = json_decode($response->getBody());
            // Faça algo com a resposta, se necessário
            // dd("Mensagem nova enviada", $responseData);
            ///////////////////Gravar
            //  $registro = webhookContact::where('recipient_id', $phone)->get()->first();
            $registro = $webhootContact;

            $registro->update([
                'status_mensagem_enviada' => 0,
                'user_updated' => $usuario,
                'quantidade_nao_lida' => $registro->quantidade_nao_lida + 1,
                'transferido_para' => null,
            ]);

            $registro->save();

            $newWebhook = webhook::create([
                'webhook' => null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'contactName' => $registro->contactName ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'messagesType' => $requestData['type'] ?? null,
                'body' => $requestData['text']['body'] ?? null,
                'status' => 'sent' ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);
        }
        // return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone));
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $phone, 'entry_id' => $identificacaocontawhatsappbusiness]));
    }

    public static function avisotransferiratendimento($id, $UsuarioID, $NomeAtendido, $idatendido)
    {
        $usuario = trim(Auth::user()->email);
        $User = user::where('whatsapp', $UsuarioID)->first();
        $NomeAtendente = $User->name;

        $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
            ->OrderBy('usuario')
            ->get()
            ->first();

        $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
        $Token = $WebhookConfig->token24horas;
        $webhootContact = webhookcontact::find($idatendido);

        $client = new Client();
        $phone = $User->whatsapp;
        $client = new Client();
        $requestData = [];

        $message = 'Foi transferido para você, ' . $NomeAtendente . ' um atendimento  ' . '. Contato de nome ' . $NomeAtendido . ', aguardando. Obrigado!';

        if ($webhootContact->user_atendimento !== Auth::user()->email) {
            $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ')';
        }

        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
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
            $responseData = json_decode($response->getBody());
            // Faça algo com a resposta, se necessário
            // dd("Mensagem nova enviada", $responseData);
            ///////////////////Gravar
            //  $registro = webhookContact::where('recipient_id', $phone)->get()->first();
            $registro = $webhootContact;

            $registro->update([
                'status_mensagem_enviada' => 0,
                'user_updated' => $usuario,
                'quantidade_nao_lida' => $registro->quantidade_nao_lida + 1,
            ]);
            $registro->save();

            $newWebhook = webhook::create([
                'webhook' => null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'contactName' => $registro->contactName ?? null,
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'messagesType' => $requestData['type'] ?? null,
                'body' => $requestData['text']['body'] ?? null,
                'status' => 'sent' ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);
        }
        //  return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone));
        return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $phone, 'entry_id' => $identificacaocontawhatsappbusiness]));
    }

    public static function avisocancelamentotransferiratendimento($id, $UsuarioID)
    {
        $usuario = trim(Auth::user()->email);
        $user = null;
        $NomeAtendente = null;
        if ($UsuarioID) {
            $User = user::where('email', $UsuarioID)->first();
            $NomeAtendente = $User->name;

            $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
                ->OrderBy('usuario')
                ->get()
                ->first();

            $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
            $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
            $Token = $WebhookConfig->token24horas;

            $webhootContact = webhookcontact::find($id);

            $client = new Client();
            $phone = $User->whatsapp;
            $client = new Client();
            $requestData = [];

            $message = 'Foi cancelado um atendimento transferido para você, ' . $NomeAtendente . '. Obrigado!';

            if ($webhootContact->user_atendimento !== Auth::user()->email) {
                $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ')';
            }

            // ===================================== somente texto como resposta
            $requestData = [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ];

            // =================================================================

            $response = $client->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $Token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
            ]);
            // Verifique a resposta
            if ($response->getStatusCode() == 200) {
                $responseData = json_decode($response->getBody());
                // Faça algo com a resposta, se necessário
                // dd("Mensagem nova enviada", $responseData);

                ///////////////////Gravar
                //  $registro = webhookContact::where('recipient_id', $phone)->get()->first();
                $registro = $webhootContact;

                $registro->update([
                    'status_mensagem_enviada' => 0,
                    'user_updated' => $usuario,
                    'quantidade_nao_lida' => $registro->quantidade_nao_lida + 1,
                ]);

                $registro->save();

                $newWebhook = webhook::create([
                    'webhook' => null,
                    'value_messaging_product' => $requestData['messaging_product'] ?? null,
                    'object' => $requestData['messaging_product'] ?? null,
                    'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                    'contactName' => $registro->contactName ?? null,
                    'recipient_id' => $requestData['to'] ?? null,
                    'type' => $requestData['type'] ?? null,
                    'messagesType' => $requestData['type'] ?? null,
                    'body' => $requestData['text']['body'] ?? null,
                    'status' => 'sent' ?? null,
                    'user_atendimento' => Auth::user()->email,
                ]);
            }
            return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $phone, 'entry_id' => $identificacaocontawhatsappbusiness]));
        }
        //  return redirect(route('whatsapp.atendimentoWhatsappFiltroTelefone',$phone));
    }

    public static function VerificaSessao(string $AvisoTransferencia, $idcontato)
    {
        $id = $AvisoTransferencia;

        $UsuarioID = $id;
        $Atendido = webhookContact::where('id', $idcontato)
            ->OrderBy('updated_at', 'desc')
            ->get()
            ->first();

        $idatendido = $Atendido->id;
        $NomeAtendido = $Atendido->contactName;
        $NomeTransferido = webhookContact::where('recipient_id', $id)
            ->OrderBy('updated_at', 'desc')
            ->get()
            ->first();

        $tempo_em_segundos = null;
        $tempo_em_horas = null;
        $tempo_em_minutos = null;
        if ($NomeTransferido->timestamp) {
            $tempo_em_segundos = strtotime(now()) - $NomeTransferido->timestamp;
            $tempo_em_horas = $tempo_em_segundos / 3600;
            $tempo_em_minutos = $tempo_em_segundos / 60;
        }
        $numero = $tempo_em_horas;
        // Separar valores antes e depois do ponto
        $partes = explode('.', $numero);

        // Atribuir partes
        $parte_inteira = (int) $partes[0];
        $parte_decimal = isset($partes[1]) ? (float) ('0.' . $partes[1]) : 0;

        // dd('parte inteira: ' . $parte_inteira, 'parte decimal: ' . $parte_decimal);

        if ($parte_inteira > 23) {
            $Avisa = WebhookServico::Avisaparaatender($id, $NomeAtendido);
        } else {
            $TransfereAvisa = WebhookServico::avisotransferiratendimento($id, $UsuarioID, $NomeAtendido, $idatendido);
        }
        return $parte_inteira;
    }

    public static function Avisaparaatender($recipient_id, $NomeAtendido)
    {
        $accessToken = WebhookServico::Token24horas();
        $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
            ->OrderBy('usuario')
            ->get()
            ->first();
        $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
        // $Token = $WebhookConfig->token24horas;
        $template = WebhookTemplate::where('id', '9')
            ->get()
            ->first();
        $name = $template->name;
        $language = $template->language;
        $message = $template->texto;

        $client = new Client();
        $phone = $recipient_id;
        $client = new Client();
        $requestData = [];

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

        $response = $client->post('https://graph.facebook.com/v17.0/' . $phone_number_id . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);
        if ($response->getStatusCode() == 200) {
            $newWebhook = webhook::create([
                'webhook' => null,
                'value_messaging_product' => $requestData['messaging_product'] ?? null,
                'object' => $requestData['messaging_product'] ?? null,
                'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                'contactName' => '',
                'recipient_id' => $requestData['to'] ?? null,
                'type' => $requestData['type'] ?? null,
                'messagesType' => 'template',
                'body' => $message . ' Atender: ' . trim($NomeAtendido),
                'status' => 'sent' ?? null,
                'user_atendimento' => Auth::user()->email,
            ]);
        }
    }

    public static function avisomensagemrecebidasupervisor($MensagemRecebida, $recipient_id, $entry_id, $messagesTimestamp, $contactName)
    {
        Log::info(' Texto: ' . $MensagemRecebida . ' -  VARIAVEIS: Telefone:' . $recipient_id . ' - Nome: ' . $contactName . ' - Canal: ' . $entry_id . ' - TimeStamp: ' . $messagesTimestamp);

        $client = new Client();
        $requestData = [];

        //   $message = $message . "\n" . ' (Enviada por supervisor(a) ' . Auth::user()->name . ")"

        $alerta = webhookContact::where('alerta_mensagem_recebida', 1)
            ->where('entry_id', $entry_id)
            ->orderby('recipient_id')
            ->get();
        foreach ($alerta as $contatos) {
            // Log::info(' Contato ' . $contactName);
            // continue;

            $TempoSessao = WebhookContactsServico::temposessao($contatos);

            $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
                ->OrderBy('usuario')
                ->get()
                ->first();

            // $identificacaocontawhatsappbusiness = $WebhookConfig->identificacaocontawhatsappbusiness;
            $phone_number_id = $WebhookConfig->identificacaonumerotelefone;
            $Token = $WebhookConfig->token24horas;

            $message = 'Tem mensagem recebida na plataforma de canal: ' . $WebhookConfig->telefone . ' para ser atendido.  ' . ' Contato de nome ' . $contactName . ', aguardando. Verifique!';

            Log::info($TempoSessao['parte_inteira']);
            if ($TempoSessao['parte_inteira'] < 24) {
                Log::info($message);
                $requestData = [
                    'messaging_product' => 'whatsapp',
                    'to' => $contatos->recipient_id,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ];
            } else {
                Log::info(' Template: aviso_mensagem_recebida_no_canal_whatsapp');
                $requestData = [
                    'messaging_product' => 'whatsapp',
                    'to' => $contatos->recipient_id,
                    'type' => 'template',
                    'template' => [
                        'name' => 'aviso_mensagem_recebida_no_canal_whatsapp',
                        'language' => [
                            'code' => 'pt_BR',
                        ],
                    ],
                ];
            }
            $response = $client->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $Token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
            ]);

            if ($response->getStatusCode() == 200) {
                Log::info(' Avisado: ' . $contatos->recipient_id);

                //             //              $responseData = json_decode($response->getBody());
                //             //              // Faça algo com a resposta, se necessário
                //             //              // dd("Mensagem nova enviada", $responseData);
                //             //   ///////////////////Gravar
                //             //             //  $registro = webhookContact::where('recipient_id', $phone)->get()->first();
                //             //             $registro =  $webhootContact;

                //             //              $registro->update([
                //             //               'status_mensagem_enviada' => 0,
                //             //               'user_updated' => $usuario,
                //             //               'quantidade_nao_lida' => $registro->quantidade_nao_lida+1,
                //             //             ]);
                //             //             $registro->save();

                //             //              $newWebhook = webhook::create([
                //             //                  'webhook' =>  null,
                //             //                  'value_messaging_product' => $requestData['messaging_product'] ?? null,
                //             //                  'object' => $requestData['messaging_product'] ?? null,
                //             //                  'entry_id' => $identificacaocontawhatsappbusiness ?? null,
                //             //                  'contactName' => $registro->contactName ?? null,
                //             //                  'recipient_id' => $requestData['to'] ?? null,
                //             //                  'type' => $requestData['type'] ?? null,
                //             //                  'messagesType' => $requestData['type'] ?? null,
                //             //                  'body' => $requestData['text']['body'] ?? null,
                //             //                  'status' => 'sent' ?? null,
                //             //                  'user_atendimento' => Auth::user()->email,
                //             //              ]);
            }
        }
    }

    public static function interactive($entry)
    {
        $data['entry'] = $entry;
        // DD($data['entry']);
        /////////   usando flow - recebendo informacoes do formulario
        $interactive = $entry['changes'][0]['value']['messages'][0]['interactive'] ?? null;

        // DD($interactive, $entry);
        $interactive_type = $entry['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
        $interactive_nfm_reply = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
        $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
        $interactive_nfm_reply_body = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
        $interactive_nfm_reply_name = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $data = json_decode($interactive_nfm_reply_response_json, true);
        $flow_token = $data['flow_token'] ?? null;
        $topicRadio = $data['topicRadio'] ?? null;
        $recipient_id = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $entry_id = $entry['id'] ?? null;
        $codigo_registro = $entry['changes'][0]['value']['messages']['timestamp'] ?? null;

        if ($flow_token == '2120367534804891') {
            WebhookServico::CadastrarFlow_token($entry);
        } elseif ($flow_token == '372275572014981') {
            WebhookServico::AlterarCPF_Flow_token($entry);
        } elseif ($flow_token == '348317521263758') {
            WebhookServico::AlterarRG_Flow_token($entry);
        } elseif ($flow_token == '381547034317574') {
            WebhookServico::AlterarCidadeUf_Flow_token($entry);
        } elseif ($flow_token == '1434146677313794') {
            WebhookServico::AlterarNome_Flow_token($entry);
        } elseif ($flow_token == '3496535283943398') {
            WebhookServico::AlterarNascimento_Flow_token($entry);
        }elseif ($flow_token == '338160952497179') {
            WebhookServico::AlterarNomeMae_Flow_token($entry);
        }
        elseif ($flow_token == '1082401946512589') {
            WebhookServico::AlterarNomePai_Flow_token($entry);
        }

        ///////////////// menu de opcoes
        if ($flow_token == '383392457504361') {
            if ($topicRadio == 'ALTERAR_CPF') {
                WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarCpf($recipient_id, $entry_id);
            } elseif ($topicRadio == 'ALTERAR_RG') {
                WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarRg($recipient_id, $entry_id);
            } elseif ($topicRadio == 'ALTERAR_NOMECOMPLETO') {
                WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarNome($recipient_id, $entry_id);
            } elseif ($topicRadio == 'ALTERAR_CIDADEUF') {
                WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarCidadeUf($recipient_id, $entry_id);
            } elseif ($topicRadio == 'CADASTROBASICO') {
                WebhookContactsEnviarFlow::EnviaMensagemFlowCadastro($recipient_id, $entry_id);
            } elseif ($topicRadio == 'ALTERAR_DATANASCIMENTO') {
                WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarNascimento($recipient_id, $entry_id);
            }
            elseif ($topicRadio == 'ALTERAR_MAE') {
                WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarNomeMae($recipient_id, $entry_id);
            }
            elseif ($topicRadio == 'ALTERAR_PAI') {
                WebhookContactsEnviarFlow::EnviaMensagemFlowAlterarNomePai($recipient_id, $entry_id);
            }
            elseif ($topicRadio == 'MEUS_CADASTROS') {
                WebhookContactsEnviarFlow::EnviaMensagemMeusDadosCadastroBasico($recipient_id, $entry_id);
                // return redirect(route('whatsapp.EnviaMensagemDadosCadastroBasico', ['recipient_id' => $recipient_id,
                //         'entry_id' => $entry_id]));
            }
            elseif ($topicRadio == 'OUTRAS_OPCOES') {
                WebhookContactsEnviarFlow::EnviaMensagemEstamosTrabalhando($recipient_id, $entry_id);
            }
        }

        // if ($interactive) {
        //         // Decodificando o JSON para um array associativo
        //         $data = json_decode($interactive_nfm_reply_response_json, true);
        //         // Atribuindo cada valor a uma variável
        //         $nome = $data['nome'];
        //         $dataNascimento = $data['dataNascimento'];
        //         $dataNascimentoObj = DateTime::createFromFormat('d/m/Y', $dataNascimento);

        //         if ($dataNascimentoObj != false) {
        //             $dataNascimentoInt = $dataNascimentoObj->format('Y-m-d');
        //         }
        //         $flow_token = $data['flow_token'];
        //         $nomePai = $data['nomePai'];
        //         $nomeMae = $data['nomeMae'];
        //         $flow_description = $data['description'];
        //          WebhookContactsServico::canal_empresa($entry_id);

        //         $formandobasewhatsappContagem = formandobasewhatsapp::where('EmpresaID', $empresaID)
        //         ->where('flow_token',$flow_token)
        //         ->where('telefone', $messagesFrom)
        //         ->get()->count();

        //         ////// incluir registros
        //         if ($formandobasewhatsappContagem < 6) {

        //             $formandobasewhatsapp = formandobasewhatsapp::where('EmpresaID', $empresaID)
        //                 ->where('nome', $nome)
        //                 ->where('flow_token',$flow_token)
        //                 ->where('telefone', $messagesFrom)
        //                 ->first();

        //             if ($formandobasewhatsapp) {
        //                 $formandobasewhatsapp->update([
        //                     'EmpresaID' => $empresaID,
        //                     'nome' => $nome ?? null,
        //                     'nascimento' => $dataNascimentoInt ?? null, // Remova um $ extra de $$dataNascimentoInt
        //                     'flow_token' => $flow_token ?? null,
        //                     'nomePai' => $nomePai ?? null,
        //                     'nomeMae' => $nomeMae ?? null,
        //                     'flow_description' => $flow_description ?? null,
        //                     'user_atendimento' => Auth::user()->name ?? null,
        //                     'telefone' => $messagesFrom ?? null,
        //                 ]);
        //             } else {
        //                 $newformandobasewhatsapp = FormandoBaseWhatsapp::create([
        //                     'EmpresaID' => $empresaID,
        //                     'nome' => $nome ?? null,
        //                     'nascimento' => $dataNascimentoInt ?? null,
        //                     'flow_token' => $flow_token ?? null,
        //                     'nomePai' => $nomePai ?? null,
        //                     'nomeMae' => $nomeMae ?? null,
        //                     'flow_description' => $flow_description ?? null,
        //                     'user_atendimento' => Auth::user()->name ?? null,
        //                     'telefone' => $messagesFrom ?? null,
        //                 ]);
        //                 // dd($interactive, $interactive_type, $interactive_nfm_reply, $interactive_nfm_reply_response_json,  $interactive_nfm_reply_body,  $interactive_nfm_reply_name,
        //                 // $nome, $dataNascimento, $flow_token, $nomePai, $nomeMae, $flow_description, $empresaID, $formandobasewhatsapp, $newformandobasewhatsapp );
        //             }

        //         }
        // }
    }

    public static function CadastrarFlow_token($entry)
    {
        // DD($data['entry']);
        /////////   usando flow - recebendo informacoes do formulario
        $interactive = $entry['changes'][0]['value']['messages'][0]['interactive'] ?? null;
        $entry_id = $entry['id'] ?? null;
        $messagesTimestamp = $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;

        // DD($interactive, $entry);
        $interactive_type = $entry['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
        $interactive_nfm_reply = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
        $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
        $interactive_nfm_reply_body = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
        $interactive_nfm_reply_name = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $phone_number_id = $entry['changes'][0]['value']['metadata']['phone_number_id'] ?? null;
        $nome_contato = $entry['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;

        if ($interactive) {
            // Decodificando o JSON para um array associativo
            $data = json_decode($interactive_nfm_reply_response_json, true);
            // Atribuindo cada valor a uma variável
            $nome = $data['nome'] ?? null;
            $dataNascimento = $data['dataNascimento'] ?? null;
            $dataNascimentoObj = DateTime::createFromFormat('d/m/Y', $dataNascimento);

            if ($dataNascimentoObj != false) {
                $dataNascimentoInt = $dataNascimentoObj->format('Y-m-d' ?? null);
            }
            $flow_token = $data['flow_token'] ?? null;
            $nomePai = $data['nomePai'] ?? null;
            $nomeMae = $data['nomeMae'] ?? null;
            $flow_description = $data['description'] ?? null;

            WebhookContactsServico::canal_empresa($entry_id);

            $formandobasewhatsappContagem = formandobasewhatsapp::where('EmpresaID', $empresaID)
                ->where('flow_token', $flow_token)
                ->where('telefone', $messagesFrom)
                ->get()
                ->count();

            $QuantidadeCadastro = 100;
            if ($messagesFrom != '5517997662949') {
                $QuantidadeCadastro = 6;
            }
            ////// incluir registros
            if ($formandobasewhatsappContagem > $QuantidadeCadastro) {
                WebhookServico::avisoInteractiveJaAtingiuLimite($entry, $messagesFrom, $phone_number_id, $nome_contato, $nome);
            } else {
                $formandobasewhatsapp = FormandoBaseWhatsapp::where('EmpresaID', $empresaID)
                    ->where('nome', $nome)
                    ->where('flow_token', $flow_token)
                    ->where('telefone', $messagesFrom)
                    ->first();

                $messagesTimestampCadastro = $formandobasewhatsapp->codigo_registro ?? null;
                Log::info($messagesTimestampCadastro);

                if (!$formandobasewhatsapp) {
                    $usuario = Auth::user(); // Garantir que o usuário está autenticado
                    $userName = $usuario ? $usuario->name : null;

                    $newformandobasewhatsapp = FormandoBaseWhatsapp::create([
                        'EmpresaID' => $empresaID,
                        'nome' => $nome ?? null,
                        'nascimento' => $dataNascimentoInt ?? null,
                        'flow_token' => $flow_token ?? null,
                        'nomePai' => $nomePai ?? null,
                        'nomeMae' => $nomeMae ?? null,
                        'flow_description' => $flow_description ?? null,
                        'user_created' => $flow_token ?? null,
                        'user_atendimento' => $userName,
                        'telefone' => $messagesFrom ?? null,
                        'codigo_registro' => $messagesTimestamp ?? null,
                    ]);
                    Log::info('ANTES ENVIAR MENSAGEM DE CADASTRO');
                    WebhookServico::avisoInteractiveCadastrado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome);
                } else {
                    WebhookServico::avisoInteractiveJaCadastrado($entry, $messagesFrom, $phone_number_id, $nome_contato, $nome, $messagesTimestampCadastro);
                }
            }
        }
    }

    public static function AlterarCPF_Flow_token($entry)
    {
        /////////   usando flow - recebendo informacoes do formulario
        $interactive = $entry['changes'][0]['value']['messages'][0]['interactive'] ?? null;
        $entry_id = $entry['id'] ?? null;
        $messagesTimestamp = $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;

        // DD($interactive, $entry);
        $interactive_type = $entry['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
        $interactive_nfm_reply = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
        $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
        $interactive_nfm_reply_body = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
        $interactive_nfm_reply_name = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $phone_number_id = $entry['changes'][0]['value']['metadata']['phone_number_id'] ?? null;
        $nome_contato = $entry['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;
        $nome = null;

        if ($interactive) {
            // Decodificando o JSON para um array associativo
            $data = json_decode($interactive_nfm_reply_response_json, true);
            // Atribuindo cada valor a uma variável

            $Cpf = $data['Cpf'];

            if (!validarCPF($Cpf)) {
                $message = $nome_contato . ', o CPF: ' . $Cpf . ' é inválido, errado! Verifique o que digitou!';
                WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
                exit();
            }

            $codigoRegistro = $data['codigoRegistro'];

            $flow_token = $data['flow_token'];



            WebhookContactsServico::canal_empresa($entry_id);

            $formandoBaseWhatsapp = FormandoBaseWhatsapp::where('codigo_registro', $codigoRegistro)->first();

            // DD($entry, $Cpf, $Rg, $codigoRegistro,$flow_token, $flow_description, $formandobasewhatsapp);
            $messagesTimestampCadastro = $formandoBaseWhatsapp->codigo_registro ?? null;
            Log::info($messagesTimestampCadastro);

            if ($formandoBaseWhatsapp) {
                $nome = $formandoBaseWhatsapp->nome;
                // $usuario = Auth::user(); // Garantir que o usuário está autenticado
                // $userName = $usuario ? $usuario->name : null;

                $atualiza = [
                    'cpf' => $Cpf,
                    'user_updated' => $codigoRegistro,
                ];
                $formandoBaseWhatsapp->update($atualiza);

                Log::info('ANTES ENVIAR MENSAGEM DE CADASTRO');

                WebhookServico::avisoInteractiveCpfAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Cpf);
            } else {
                WebhookServico::avisoInteractiveCpfAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Cpf, $codigoRegistro);
            }
        }
    }
    public static function AlterarRG_Flow_token($entry)
    {
        /////////   usando flow - recebendo informacoes do formulario
        $interactive = $entry['changes'][0]['value']['messages'][0]['interactive'] ?? null;
       $entry_id = $entry['id'] ?? null;
        $messagesTimestamp = $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;

        // DD($interactive, $entry);
        $interactive_type = $entry['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
        $interactive_nfm_reply = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
        $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
        $interactive_nfm_reply_body = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
        $interactive_nfm_reply_name = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $phone_number_id = $entry['changes'][0]['value']['metadata']['phone_number_id'] ?? null;
        $nome_contato = $entry['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;
        $nome = null;

        if ($interactive) {
            // Decodificando o JSON para um array associativo
            $data = json_decode($interactive_nfm_reply_response_json, true);
            // Atribuindo cada valor a uma variável

            $Rg = $data['Rg'];

            $codigoRegistro = $data['codigoRegistro'];

            $flow_token = $data['flow_token'];


            WebhookContactsServico::canal_empresa($entry_id);

            $formandoBaseWhatsapp = FormandoBaseWhatsapp::where('codigo_registro', $codigoRegistro)->first();
            $messagesTimestampCadastro = $formandoBaseWhatsapp->codigo_registro ?? null;
            Log::info($messagesTimestampCadastro);

            if ($formandoBaseWhatsapp) {
                $nome = $formandoBaseWhatsapp->nome;
                // $usuario = Auth::user(); // Garantir que o usuário está autenticado
                // $userName = $usuario ? $usuario->name : null;

                $atualiza = [
                    'rg' => $Rg,
                    'user_updated' => $codigoRegistro,
                ];
                $formandoBaseWhatsapp->update($atualiza);

                Log::info('ANTES ENVIAR MENSAGEM DE CADASTRO para alterar RG');

                WebhookServico::avisoInteractiveRgAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Rg);
            } else {
                WebhookServico::avisoInteractiveRgAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Rg, $codigoRegistro);
            }
        }
    }
    public static function AlterarCidadeUf_Flow_token($entry)
    {
        /////////   usando flow - recebendo informacoes do formulario
        $interactive = $entry['changes'][0]['value']['messages'][0]['interactive'] ?? null;
        $entry_id = $entry['id'] ?? null;
        $messagesTimestamp = $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;

        // DD($interactive, $entry);
        $interactive_type = $entry['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
        $interactive_nfm_reply = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
        $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
        $interactive_nfm_reply_body = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
        $interactive_nfm_reply_name = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $phone_number_id = $entry['changes'][0]['value']['metadata']['phone_number_id'] ?? null;
        $nome_contato = $entry['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;
        $nome = null;

        if ($interactive) {
            // Decodificando o JSON para um array associativo
            $data = json_decode($interactive_nfm_reply_response_json, true);
            // Atribuindo cada valor a uma variável

            $Cidade = $data['Cidade'];
            $Uf = $data['Uf'];

            $codigoRegistro = $data['codigoRegistro'];

            $flow_token = $data['flow_token'];

            WebhookContactsServico::canal_empresa($entry_id);

            $formandoBaseWhatsapp = FormandoBaseWhatsapp::where('codigo_registro', $codigoRegistro)->first();
            $messagesTimestampCadastro = $formandoBaseWhatsapp->codigo_registro ?? null;
            Log::info($messagesTimestampCadastro);

            if ($formandoBaseWhatsapp) {
                $nome = $formandoBaseWhatsapp->nome;
                $atualiza = [
                    'cidade' => $Cidade,
                    'uf' => $Uf,
                    'user_updated' => $codigoRegistro,
                ];
                $formandoBaseWhatsapp->update($atualiza);

                Log::info('ANTES ENVIAR MENSAGEM DE CADASTRO');

                WebhookServico::avisoInteractiveCidadeUfAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Cidade, $Uf);
            } else {
                WebhookServico::avisoInteractiveCidadeUfAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Cidade, $Uf, $codigoRegistro);
            }
        }
    }
    public static function AlterarNome_Flow_token($entry)
    {
        /////////   usando flow - recebendo informacoes do formulario
        $interactive = $entry['changes'][0]['value']['messages'][0]['interactive'] ?? null;
        $entry_id = $entry['id'] ?? null;
        $messagesTimestamp = $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;

        // DD($interactive, $entry);
        $interactive_type = $entry['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
        $interactive_nfm_reply = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
        $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
        $interactive_nfm_reply_body = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
        $interactive_nfm_reply_name = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $phone_number_id = $entry['changes'][0]['value']['metadata']['phone_number_id'] ?? null;
        $nome_contato = $entry['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;
        $nome = null;

        if ($interactive) {
            // Decodificando o JSON para um array associativo
            $data = json_decode($interactive_nfm_reply_response_json, true);
            // Atribuindo cada valor a uma variável

            $NomeCompleto = $data['Nome'];

            $codigoRegistro = $data['codigoRegistro'];

            $flow_token = $data['flow_token'];

            WebhookContactsServico::canal_empresa($entry_id);

            $formandoBaseWhatsapp = FormandoBaseWhatsapp::where('codigo_registro', $codigoRegistro)->first();
            $messagesTimestampCadastro = $formandoBaseWhatsapp->codigo_registro ?? null;
            Log::info($messagesTimestampCadastro);

            if ($formandoBaseWhatsapp) {
                $nome = $formandoBaseWhatsapp->nome;
                $atualiza = [
                    'nome' => $NomeCompleto,
                    'user_updated' => $codigoRegistro,
                ];
                $formandoBaseWhatsapp->update($atualiza);

                Log::info('ANTES ENVIAR MENSAGEM DE CADASTRO PARA ALTERAR NOME');

                WebhookServico::avisoInteractiveNomeAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $NomeCompleto);
            } else {
                WebhookServico::avisoInteractiveNomeAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $NomeCompleto, $codigoRegistro);
            }
        }
    }

    public static function AlterarNomeMae_Flow_token($entry)
    {
        /////////   usando flow - recebendo informacoes do formulario
        $interactive = $entry['changes'][0]['value']['messages'][0]['interactive'] ?? null;
        $entry_id = $entry['id'] ?? null;
        $messagesTimestamp = $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;

        // DD($interactive, $entry);
        $interactive_type = $entry['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
        $interactive_nfm_reply = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
        $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
        $interactive_nfm_reply_body = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
        $interactive_nfm_reply_name = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $phone_number_id = $entry['changes'][0]['value']['metadata']['phone_number_id'] ?? null;
        $nome_contato = $entry['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;
        $nome = null;

        if ($interactive) {
            // Decodificando o JSON para um array associativo
            $data = json_decode($interactive_nfm_reply_response_json, true);
            // Atribuindo cada valor a uma variável

            $nomeMae = $data['nomeMae'];

            $codigoRegistro = $data['codigoRegistro'];

            $flow_token = $data['flow_token'];

            WebhookContactsServico::canal_empresa($entry_id);

            $formandoBaseWhatsapp = FormandoBaseWhatsapp::where('codigo_registro', $codigoRegistro)->first();
            $messagesTimestampCadastro = $formandoBaseWhatsapp->codigo_registro ?? null;
            Log::info($messagesTimestampCadastro);

            if ($formandoBaseWhatsapp) {
                $nome = $formandoBaseWhatsapp->nome;
                $atualiza = [
                    'nomeMae' => $nomeMae,
                    'user_updated' => $codigoRegistro,
                ];
                $formandoBaseWhatsapp->update($atualiza);

                Log::info('ANTES ENVIAR MENSAGEM DE CADASTRO PARA ALTERAR NOME DA MAE');

                WebhookServico::avisoInteractiveNomeMaeAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato,
                 $messagesTimestamp, $nome, $nomeMae);
            } else {
                WebhookServico::avisoInteractiveNomeMaeAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id,
                $nome_contato, $messagesTimestamp, $nome, $nomeMae, $codigoRegistro);
            }
        }
    }

    public static function AlterarNomePai_Flow_token($entry)
    {
        /////////   usando flow - recebendo informacoes do formulario
        $entry_id = $entry['id'];
        $interactive = $entry['changes'][0]['value']['messages'][0]['interactive'] ?? null;
        $messagesTimestamp = $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;

        // DD($interactive, $entry);
        $interactive_type = $entry['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
        $interactive_nfm_reply = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
        $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
        $interactive_nfm_reply_body = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
        $interactive_nfm_reply_name = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $phone_number_id = $entry['changes'][0]['value']['metadata']['phone_number_id'] ?? null;
        $nome_contato = $entry['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;
        $nome = null;

        if ($interactive) {
            // Decodificando o JSON para um array associativo
            $data = json_decode($interactive_nfm_reply_response_json, true);
            // Atribuindo cada valor a uma variável

            $nomePai = $data['nomePai'];


            $codigoRegistro = $data['codigoRegistro'];

            $flow_token = $data['flow_token'];



            WebhookContactsServico::canal_empresa($entry_id);


            $formandoBaseWhatsapp = FormandoBaseWhatsapp::where('codigo_registro', $codigoRegistro)->first();
            $messagesTimestampCadastro = $formandoBaseWhatsapp->codigo_registro ?? null;
            Log::info($messagesTimestampCadastro);

            if ($formandoBaseWhatsapp) {
                $nome = $formandoBaseWhatsapp->nome;
                $atualiza = [
                    'nomePai' => $nomePai,
                    'user_updated' => $codigoRegistro,
                ];
                $formandoBaseWhatsapp->update($atualiza);

                Log::info('ANTES ENVIAR MENSAGEM DE CADASTRO PARA ALTERAR NOME DO PAI');

                WebhookServico::avisoInteractiveNomePaiAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato,
                 $messagesTimestamp, $nome, $nomePai);
            } else {
                WebhookServico::avisoInteractiveNomePaiAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id,
                $nome_contato, $messagesTimestamp, $nome, $nomePai, $codigoRegistro);
            }
        }
    }

    public static function AlterarNascimento_Flow_token($entry)
    {
        /////////   usando flow - recebendo informacoes do formulario
        $interactive = $entry['changes'][0]['value']['messages'][0]['interactive'] ?? null;
        $entry_id = $entry['id'];
        $messagesTimestamp = $entry['changes'][0]['value']['messages'][0]['timestamp'] ?? null;

        // DD($interactive, $entry);
        $interactive_type = $entry['changes'][0]['value']['messages'][0]['interactive']['type'] ?? null;
        $interactive_nfm_reply = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply'] ?? null;
        $interactive_nfm_reply_response_json = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['response_json'] ?? null;
        $interactive_nfm_reply_body = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['body'] ?? null;
        $interactive_nfm_reply_name = $entry['changes'][0]['value']['messages'][0]['interactive']['nfm_reply']['name'] ?? null;
        $messagesFrom = $entry['changes'][0]['value']['messages'][0]['from'] ?? null;
        $phone_number_id = $entry['changes'][0]['value']['metadata']['phone_number_id'] ?? null;
        $nome_contato = $entry['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;
        $nome = null;

        if ($interactive) {
            // Decodificando o JSON para um array associativo
            $data = json_decode($interactive_nfm_reply_response_json, true);
            // Atribuindo cada valor a uma variável

            $dataNascimento = $data['dataNascimento'] ?? null;

            $dataNascimentoObj = DateTime::createFromFormat('d/m/Y', $dataNascimento);

            if ($dataNascimentoObj != false) {
                $dataNascimentoInt = $dataNascimentoObj->format('Y-m-d' ?? null);
            }

            $codigoRegistro = $data['codigoRegistro'];

            $flow_token = $data['flow_token'];

            WebhookContactsServico::canal_empresa($entry_id);

            $formandoBaseWhatsapp = FormandoBaseWhatsapp::where('codigo_registro', $codigoRegistro)->first();
            $messagesTimestampCadastro = $formandoBaseWhatsapp->codigo_registro ?? null;
            Log::info($messagesTimestampCadastro);

            if ($formandoBaseWhatsapp) {
                $nome = $formandoBaseWhatsapp->nome;
                $atualiza = [
                    'nascimento' => $dataNascimentoInt,
                    'user_updated' => $codigoRegistro,
                ];
                $formandoBaseWhatsapp->update($atualiza);

                Log::info('ANTES ENVIAR MENSAGEM DE CADASTRO PARA ALTERAR NASCIMENTO');

                WebhookServico::avisoInteractiveNascimentoAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato,
                 $messagesTimestamp, $nome, $dataNascimento);
            } else {
                WebhookServico::avisoInteractiveNascimentoAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato,
                 $messagesTimestamp, $nome, $dataNascimento, $codigoRegistro);
            }
        }
    }

    public static function avisoInteractiveNascimentoAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato,
     $messagesTimestamp, $nome, $dataNascimento, $codigoRegistro)
    {
        $message = $nome_contato . ', O NÚMERO DE CODIGO DE REGISTRO: ' . $codigoRegistro . ' para o NASCIMENTO: ' . $dataNascimento . ' NÃO FOI LOCALIZADO! VERIFIQUE O QUE DIGITOU!';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveCpfAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Cpf, $codigoRegistro)
    {
        $message = $nome_contato . ', O NÚMERO DE CODIGO DE REGISTRO: ' . $codigoRegistro . ' para o CPF: ' . $Cpf . ' NÃO FOI LOCALIZADO! VERIFIQUE O QUE DIGITOU!';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }
    public static function avisoInteractiveRgAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Rg, $codigoRegistro)
    {
        $message = $nome_contato . ', O NÚMERO DE CODIGO DE REGISTRO: ' . $codigoRegistro . ' para o RG: ' . $Rg . ' NÃO FOI LOCALIZADO! VERIFIQUE O QUE DIGITOU!';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }
    public static function avisoInteractiveCidadeUfAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Cidade, $Uf, $codigoRegistro)
    {
        $message = $nome_contato . ', O NÚMERO DE CODIGO DE REGISTRO: ' . $codigoRegistro . ' para a CIDADE e UF/ESTADO: ' . $Cidade . '-' . $Uf . ' NÃO FOI LOCALIZADO! VERIFIQUE O QUE DIGITOU!';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveCpfAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Cpf)
    {
        $message = $nome_contato . ', o registro com nome de ' . $nome . ' no CADASTROS DE ATLETAS foi alterado com sucesso o campo CPF para: ' . $Cpf . '.';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveNascimentoAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Nascimento)
    {
        $message = $nome_contato . ', o registro com nome de ' . $nome . ' no CADASTROS DE ATLETAS foi alterado com sucesso o campo NASCIMENTO para: ' . $Nascimento . '.';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveRgAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Rg)
    {
        $message = $nome_contato . ', o registro com nome de ' . $nome . ' no CADASTROS DE ATLETAS foi alterado com sucesso o campo RG para: ' . $Rg . '.';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }
    public static function avisoInteractiveNomeAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $NomeCompleto)
    {
        $message = $nome_contato . ', o registro com nome de ' . $nome . ' no CADASTROS DE ATLETAS foi alterado com sucesso o campo NOME para: ' . $NomeCompleto . '.';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }
    public static function avisoInteractiveNomeAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $NomeCompleto, $codigoRegistro)
    {
        $message = $nome_contato . ', O NÚMERO DE CODIGO DE REGISTRO: ' . $codigoRegistro . ' para o NOME COMPLETO: ' . $NomeCompleto . ' NÃO FOI LOCALIZADO! VERIFIQUE O QUE DIGITOU!';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveNomeMaeAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato,
    $messagesTimestamp, $nome, $nomeMae)
    {
        $message = $nome_contato . ', o registro com nome de ' . $nome .
        ' no CADASTROS DE ATLETAS foi alterado com sucesso o campo NOME DA MAE para: ' . $nomeMae . '.';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveNomeMaeAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id,
     $nome_contato, $messagesTimestamp, $nome, $nomeMae, $codigoRegistro)
    {
        $message = $nome_contato . ', O NÚMERO DE CODIGO DE REGISTRO: ' . $codigoRegistro .
         ' para o NOME DA MÃE: ' . $nomeMae . ' NÃO FOI LOCALIZADO! VERIFIQUE O QUE DIGITOU!';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveNomePaiAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato,
    $messagesTimestamp, $nome, $nomePai)
    {
        $message = $nome_contato . ', o registro com nome de ' . $nome .
        ' no CADASTROS DE ATLETAS foi alterado com sucesso o campo NOME PAI para: ' . $nomePai . '.';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveNomePaiAlteradoNaoAchado($entry, $messagesFrom, $phone_number_id,
     $nome_contato, $messagesTimestamp, $nome, $nomePai, $codigoRegistro)
    {
        $message = $nome_contato . ', O NÚMERO DE CODIGO DE REGISTRO: ' . $codigoRegistro .
         ' para o NOME DO PAI: ' . $nomePai . ' NÃO FOI LOCALIZADO! VERIFIQUE O QUE DIGITOU!';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }



    public static function avisoInteractiveCidadeUfAlterado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome, $Cidade, $Uf)
    {
        $message = $nome_contato . ', o registro com nome de ' . $nome . ' no CADASTROS DE ATLETAS foi alterado com sucesso o campo CIDADE e UF/ESTADO para: ' . $Cidade . '-' . $Uf . '.';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveCadastrado($entry, $messagesFrom, $phone_number_id, $nome_contato, $messagesTimestamp, $nome)
    {
        $message =
            $nome_contato .
            ', o registro com nome de ' .
            $nome .
            ' no CADASTROS DE ATLETAS foi cadastrado com sucesso! O mesmo está vinculado a este whatsapp.
        O Código do registro é: ' .
            $messagesTimestamp .
            '. ANOTE ESTE CÓDIGO PARA FUTURAS CONSULTAS.';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveJaCadastrado($entry, $messagesFrom, $phone_number_id, $nome_contato, $nome, $messagesTimestampCadastro)
    {
        $message =
            $nome_contato .
            ', o registro com nome de ' .
            $nome .
            ' no CADASTROS DE ATLETAS já existe! O mesmo está vinculado a este whatsapp.' .
            '
        O Código do registro é: ' .
            $messagesTimestampCadastro .
            '. ANOTE ESTE CÓDIGO PARA FUTURAS CONSULTAS.';
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoInteractiveJaAtingiuLimite($entry, $messagesFrom, $phone_number_id, $nome_contato, $nome)
    {
        $message = $nome_contato . ', você atingiu o limite de registros no CADASTROS DE ATLETAS pelo número desse whatsapp!';

        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function avisoMensagemBloqueadaEntrada($entry, $messagesFrom, $phone_number_id, $nome_contato)
    {
        $message = 'O teu número está bloqueado para receber mensagem. PROCURE ALGUÉM DA ADMINISTRAÇÃO!';

        Log::info('Mensagem:' . $message);
        WebhookServico::EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message);
    }

    public static function EnviaMensagem($entry, $messagesFrom, $phone_number_id, $nome_contato, $message)
    {
        Log::info('Identificação:' . $phone_number_id);

        $WebhookConfig = WebhookConfig::Where('identificacaonumerotelefone', $phone_number_id)
            ->OrderBy('usuario')
            ->get()
            ->first();

        Log::info('Identificação:' . $WebhookConfig->identificacaocontawhatsappbusiness);

        $phone_number_id = $WebhookConfig->identificacaonumerotelefone;

        Log::info('Validado:');

        $Token = $WebhookConfig->token24horas;

        $client = new Client();
        $requestData = [];

        $requestData = [
            'messaging_product' => 'whatsapp',
            'to' => $messagesFrom,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];
        $response = $client->post('https://graph.facebook.com/v18.0/' . $phone_number_id . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . $Token,
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ]);
        // Verifique a resposta
        if ($response->getStatusCode() == 200) {
            Log::info('Mensagem enviada com sucesso na linha 1332!');
        }
    }

    public static function VerificaBloqueadoEntradaMensagem($entry_id, $bloquear_entrada_mensagem, $status, $messagesFrom, $nome_contato)
    {
        if ($bloquear_entrada_mensagem) {
            if ($status == 'received') {
                $WebhookConfig = WebhookConfig::Where('identificacaocontawhatsappbusiness', $entry_id)
                    ->OrderBy('usuario')
                    ->get()
                    ->first();

                if ($WebhookConfig == null) {
                    Log::info('Canal nulo');
                    exit();
                }

                $phone_number_id = $WebhookConfig->identificacaonumerotelefone;

                Log::info('bloquear_entrada_mensagem como verdadeiro: ' . $bloquear_entrada_mensagem);
                Log::info('Telefone:' . $messagesFrom);
                Log::info('canal:' . $phone_number_id);
                Log::info('Contato:' . $nome_contato);

                WebhookServico::avisoMensagemBloqueadaEntrada($entry_id, $messagesFrom, $phone_number_id, $nome_contato);

                exit();
            }
        }
    }
}
