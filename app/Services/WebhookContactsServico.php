<?php
namespace App\Services;
use App\Models\webhookAtendimentoEncerrado;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\webhook;
use App\Models\WebhookContact;
use App\Models\WebhookConfig;
use App\Models\WebhookTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class WebhookContactsServico
{
    public static function FiltraCanaisUsuariosAtivos()
{
    $result = [];

//     $RegistrosContatos = webhookContact::get();
//   dd($RegistrosContatos);

    if (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127')
    && Gate::allows('WHATSAPP_ENTRY_ID_189514994242034')
    && Gate::allows('WHATSAPP_ENTRY_ID_179613235241221')) {
        $RegistrosContatos = webhookContact::
            where(function ($query) {
                $query->whereNull('ocultar_lista_atendimento')
                    ->orWhere('ocultar_lista_atendimento', 0);
            })
            ->whereIn('entry_id', ['189514994242034', '167722543083127', '179613235241221'])
            ->orderby('updated_at', 'desc')
            ->get();

        $QuantidadeCanalAtendimento = 2;


    } elseif (Gate::allows('WHATSAPP_ENTRY_ID_189514994242034')) {
        $RegistrosContatos = webhookContact::
            where(function ($query) {
                $query->whereNull('ocultar_lista_atendimento')
                    ->orWhere('ocultar_lista_atendimento', 0);
            })
            ->whereIn('entry_id', ['189514994242034'])
            ->orderby('updated_at', 'desc')
            ->get();
        $QuantidadeCanalAtendimento = 1;
    } elseif (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127')) {
        $RegistrosContatos = webhookContact::
            where(function ($query) {
                $query->whereNull('ocultar_lista_atendimento')
                    ->orWhere('ocultar_lista_atendimento', 0);
            })
            ->whereIn('entry_id', ['167722543083127'])
            ->orderby('updated_at', 'desc')
            ->get();
        $QuantidadeCanalAtendimento = 1;
    } elseif (Gate::allows('WHATSAPP_ENTRY_ID_179613235241221')) {
        $RegistrosContatos = webhookContact::
            where(function ($query) {
                $query->whereNull('ocultar_lista_atendimento')
                    ->orWhere('ocultar_lista_atendimento', 0);
            })
            ->whereIn('entry_id', ['179613235241221'])
            ->orderby('updated_at', 'desc')
            ->get();
        $QuantidadeCanalAtendimento = 1;
    }


    $result['RegistrosContatos'] = $RegistrosContatos;
    $result['QuantidadeCanalAtendimento'] = $QuantidadeCanalAtendimento;


    return $result;
}

    public static function temposessao($contatos)
    {
        $tempo_em_segundos = null;
        $tempo_em_horas = null;
        $tempo_em_minutos = null;

        if ($contatos->timestamp) {
            $tempo_em_segundos = strtotime(now()) - $contatos->timestamp;
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

    public static function canal_empresa($entry_id)
    {
        if ($entry_id = '189514994242034') {
            $empresaID = 1029;
        }
        else
        if ($entry_id = '179613235241221') {
            $empresaID = 1025;
        }

        return $empresaID;
    }



}

