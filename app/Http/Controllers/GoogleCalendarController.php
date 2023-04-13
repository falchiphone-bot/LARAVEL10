<?php

namespace App\Http\Controllers;
use App\Models\User;
use Google\Service\AnalyticsData\OrderBy;
use Illuminate\Http\Request;
use Google_Service;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Client;
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;
use Google_Service_Calendar_EventDateTime;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

// addAttendee   - para incluir participante no evento

// Inclua o autoload do Composer
// require __DIR__ . '/../../../Users/pedrorobertofalchi/Projetos/IniciacaoLaravel-10/vendor/autoload.php';
require '../vendor/autoload.php';

// Users/pedrorobertofalchi/Projetos/IniciacaoLaravel-10/vendor/autoload.php;

class GoogleCalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:AGENDA - LISTAR'])->only('index');
        $this->middleware(['permission:AGENDA - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:AGENDA - EDITAR'])->only(['edit', 'update']);
        // $this->middleware(['permission:AGENDA - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:AGENDA - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        return view('Google.dashboard');
    }

    // public function index()
    // {
    //     $eventos = new Event();
    //     $eventos = $eventos->get();

    //     // dd($eventos->first());

    //     return view('/Google.index', compact('eventos'));
    // }
    public function startanterior()
    {
        $eventos = new Event();

        //Todas anteriores
        $eventos = $eventos->get();

        $eventos_filtrados = [];

        foreach ($eventos as $evento) {
            $startDateTime = $evento->start->dateTime ?? $evento->start->date;
            $data_inicio = new Carbon($startDateTime);
            if ($data_inicio->lt(Carbon::today())) {
                // eventos anteriores a hoje
                $eventos_filtrados[] = $evento;
            }
            $eventos = $eventos_filtrados;

            return view('/Google.index', compact('eventos'));
        }
    }

    public function startposterior()
    {
        //Todas posteriores
        $data_posterior = Carbon::now(); // data posterior é a data atual

        $eventos = new Event();
        $eventos = $eventos->get();

        $eventos_filtrados = [];

        foreach ($eventos as $evento) {
            $startDateTime = $evento->start->dateTime ?? $evento->start->date;
            $data_inicio = new Carbon($startDateTime);
            if ($data_inicio->gt($data_posterior)) {
                // eventos com data de início posterior
                $eventos_filtrados[] = $evento;
            }
        }
        $eventos = $eventos_filtrados;

        return view('/Google.index', compact('eventos'));
    }

    public function starthoje()
    {
        $data_atual = Carbon::today(); // data atual é a data de hoje

        $eventos = new Event();

        $eventos = $eventos->get();

        $eventos_filtrados = [];

        foreach ($eventos as $evento) {
            $startDateTime = $evento->start->dateTime ?? $evento->start->date;
            $data_inicio = new Carbon($startDateTime);
            if ($data_inicio->isSameDay($data_atual)) {
                // eventos com data de início igual a data de hoje
                $eventos_filtrados[] = $evento;
            }
        }
        $eventos = $eventos_filtrados;

        return view('/Google.index', compact('eventos'));
    }

    public function index()
    {
        $data_inicial = Carbon::now()->startOfMonth(); // data inicial é o primeiro dia do mês atual
        $data_final = Carbon::now()->endOfMonth(); // data final é o último dia do mês atual

        $eventos = new Event();
        $eventos = $eventos->get();

        $eventos_filtrados = [];

        foreach ($eventos as $evento) {
            $startDateTime = $evento->start->dateTime ?? $evento->start->date;
            $data_inicio = new Carbon($startDateTime);
            if ($data_inicio->between($data_inicial, $data_final)) {
                // eventos na faixa de datas
                $eventos_filtrados[] = $evento;
            }
        }
        $eventos = $eventos_filtrados;

        return view('/Google.index', compact('eventos'));
    }

    public function create()
    {
        return view('Google.create');
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $inicio = Carbon::createFromFormat('Y-m-d\TH:i', $request->inicio);
        $fim = Carbon::createFromFormat('Y-m-d\TH:i', $request->fim);
        Event::create([
            'name' => $request->name,
            'startDateTime' => $inicio,
            'endDateTime' => $fim,
            'description' => $request->descricao,
        ]);

        $usuario = User::get();

        // dd($usuario->hasPermissionTo('AGENDA - RECEBER EMAIL'));
        $Enviado = '';
        $NaoEnviado = '';
        // dd(env('MAIL_PASSWORD'));

        foreach ($usuario as $user) {
            if ($user->hasPermissionTo('AGENDA - RECEBER EMAIL DE INCLUSAO')) {
                // Criar uma nova instância do SwiftMailer
                $mailer = new Swift_Mailer(new Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'), env('MAIL_ENCRYPTION')));
                $mailer->getTransport()->setUsername(env('MAIL_USERNAME'));
                $mailer->getTransport()->setPassword(env('MAIL_PASSWORD'));

                $html =
                    '<!DOCTYPE html>
            <html>
            <head>
                <title>Inclusão na Agenda</title>
            </head>
            <body>
                <h1>Inclusão na Agenda</h1>



                <p>Olá, ' .
                    $user->name .
                    ',</p>
                <p>Este e-mail é para informá-lo(a) de que um evento na Agenda contabilidadeprf foi incluído. Por favor, verifique seus compromissos e certifique-se de estar ciente das mudanças.</p>
                <p>Obrigado!</p>
                <p>Equipe de Agenda contabilidadeprf</p>
            </body>
            </html>';

                // Criar a mensagem de e-mail
                $message = (new Swift_Message('Alterado evento na agenda via API. = ' . $request->name))
                    ->setFrom([env('MAIL_FROM_ADDRESS') => 'Agenda contabilidadeprf - EVENTO ALTERADO'])
                    ->setTo([$user->email => $user->name])
                    ->setBody('Titulo da agenda: ' . $request->name . ' <-///-> Link para a agenda: ' . $html, 'text/html');

                // Enviar a mensagem de e-mail
                $result = $mailer->send($message);

                // verifica se o e-mail foi enviado com sucesso
                if ($result) {
                    $Enviado .= $user->name . ', ';
                } else {
                    $NaoEnviado .= $user->name . ', ';
                }

            }

        }
            return redirect(route('Agenda.index'))
            ->with('success', 'Email enviados com sucesso para ' . $Enviado)
            ->with('error', 'Não foram enviados para ' . $NaoEnviado);

    }

    public function show(string $id)
    {
        $evento = new Event();
        $evento = $evento->find($id);

        $participantes = $evento->attendees;



        return view('Google.show', compact('evento', 'participantes'));
    }

    public function edit($id)
    {
        $evento = new Event();
        $evento = $evento->find($id);

        return view('Google.edit', compact('evento'));
    }

    public function update(Request $request, string $id)
    {
        $inicio = Carbon::createFromFormat('Y-m-d\TH:i', $request->inicio);
        $fim = Carbon::createFromFormat('Y-m-d\TH:i', $request->fim);

        $evento = new Event();
        $evento = $evento->find($id);
        if (empty($evento)) {
            return redirect(route('Agenda.index'))->with('error', 'EVENTOS NÃO ENCONTRADOS!');
        }
        $evento->name = $request->name;
        $evento->startDateTime = $inicio;
        $evento->endDateTime = $fim;
        $evento->description = $request->descricao;
        $evento->sendUpdates = 'all'; //// depende de configurações
        $evento->sendUpdates = 'externalOnly'; /// depende de configurações

        // dd($evento);
        $evento->save();

        $usuario = User::get();

        // dd($usuario->hasPermissionTo('AGENDA - RECEBER EMAIL'));
        $Enviado = '';
        $NaoEnviado = '';
        // dd(env('MAIL_PASSWORD'));

        foreach ($usuario as $user) {
            if ($user->hasPermissionTo('AGENDA - RECEBER EMAIL DE EDICAO')) {
                // Criar uma nova instância do SwiftMailer
                $mailer = new Swift_Mailer(new Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'), env('MAIL_ENCRYPTION')));
                $mailer->getTransport()->setUsername(env('MAIL_USERNAME'));
                $mailer->getTransport()->setPassword(env('MAIL_PASSWORD'));

                $html =
                    '<!DOCTYPE html>
            <html>
            <head>
                <title>Alteração na Agenda</title>
            </head>
            <body>
                <h1>Alteração na Agenda</h1>



                <p>Olá, ' .
                    $user->name .
                    ',</p>
                <p>Este e-mail é para informá-lo(a) de que um evento na Agenda contabilidadeprf foi alterado. Por favor, verifique seus compromissos e certifique-se de estar ciente das mudanças.</p>
                <p>Obrigado!</p>
                <p>Equipe de Agenda contabilidadeprf</p>
            </body>
            </html>';

                // Criar a mensagem de e-mail
                $message = (new Swift_Message('Alterado evento na agenda via API. = ' . $evento->name))
                    ->setFrom([env('MAIL_FROM_ADDRESS') => 'Agenda contabilidadeprf - EVENTO ALTERADO'])
                    ->setTo([$user->email => $user->name])
                    ->setBody('Titulo da agenda: ' . $evento->name . ' <-///-> Link para a agenda: ' . $evento->htmlLink . $html, 'text/html');

                // Enviar a mensagem de e-mail
                $result = $mailer->send($message);

                // verifica se o e-mail foi enviado com sucesso
                if ($result) {
                    $Enviado .= $user->name . ', ';
                } else {
                    $NaoEnviado .= $user->name . ', ';
                }
            }
        }

        return redirect(route('Agenda.index'))
            ->with('success', 'Email enviados com sucesso para ' . $Enviado)
            ->with('error', 'Não foram enviados para ' . $NaoEnviado);
    }

    public function destroy(string $id)
    {
        $evento = new Event();
        $evento = $evento->find($id);

        if (empty($evento)) {
            return redirect(route('Agenda.index'))->with('error', 'Evento não encontrado!');
        }

        $evento->delete();

        $usuario = User::get();

        // dd($usuario->hasPermissionTo('AGENDA - RECEBER EMAIL'));
        $Enviado = '';
        $NaoEnviado = '';
        // dd(env('MAIL_PASSWORD'));

        foreach ($usuario as $user) {
            if ($user->hasPermissionTo('AGENDA - RECEBER EMAIL DE EXCLUSAO')) {
                // Criar uma nova instância do SwiftMailer
                $mailer = new Swift_Mailer(new Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'), env('MAIL_ENCRYPTION')));
                $mailer->getTransport()->setUsername(env('MAIL_USERNAME'));
                $mailer->getTransport()->setPassword(env('MAIL_PASSWORD'));

                $html =
                    '<!DOCTYPE html>
            <html>
            <head>
                <title>Alteração na Agenda</title>
            </head>
            <body>
                <h1>Alteração na Agenda</h1>



                <p>Olá, ' .
                    $user->name .
                    ',</p>
                <p>Este e-mail é para informá-lo(a) de que um evento na Agenda contabilidadeprf foi excluido. Por favor, verifique seus compromissos e certifique-se de estar ciente das mudanças.</p>
                <p>Obrigado!</p>
                <p>Equipe de Agenda contabilidadeprf</p>
            </body>
            </html>';

                // Criar a mensagem de e-mail
                $message = (new Swift_Message('Alterado evento na agenda via API. = ' . $evento->name))
                    ->setFrom([env('MAIL_FROM_ADDRESS') => 'Agenda contabilidadeprf - EVENTO ALTERADO'])
                    ->setTo([$user->email => $user->name])
                    ->setBody('Titulo da agenda: ' . $evento->name . ' <-///-> Link para a agenda: ' . $evento->htmlLink . $html, 'text/html');

                // Enviar a mensagem de e-mail
                $result = $mailer->send($message);

                // verifica se o e-mail foi enviado com sucesso
                if ($result) {
                    $Enviado .= $user->name . ', ';
                } else {
                    $NaoEnviado .= $user->name . ', ';
                }

            }

        }
            return redirect(route('Agenda.index'))
            ->with('success', 'Email enviados com sucesso para ' . $Enviado)
            ->with('error', 'Não foram enviados para ' . $NaoEnviado);

    }
}
