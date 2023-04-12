<?php

namespace App\Http\Controllers;

use Google\Service\AnalyticsData\OrderBy;
use Illuminate\Http\Request;
use Google_Service;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Client;
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;
use Google_Service_Calendar_EventDateTime;

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

        return redirect(route('Agenda.index'));
    }

    public function show(string $id)
    {
        $evento = new Event();
        $evento = $evento->find($id);

        $participantes = $evento->attendees;

        //   dd($participantes);
        // $participantes = [];

        // foreach ($participantesatuais as $key => $participante) {
        //     $participantes[$key]['email'] = $participante->email;
        //     $participantes[$key]['name'] = $participante->name;
        //     $participantes[$key]['comment'] = $participante->comment;
        // }

        // array_push($participantes, [
        //     'email' => 'mauriciomgp5@gmail.com',
        //     'name' => 'Mauricio ',
        //     'comment' => 'NRaa',
        // ]);

        // foreach ($participantes as $inserir) {
        //     $evento->addAttendee([
        //         'email' => $inserir['email'],
        //         'name' => $inserir['name'],
        //         'comment' => $inserir['comment'],
        //     ]);
        //   $evento->save();

        // $evento->addAttendee(['email' => 'pedroroberto@falchi.com.br']);
        // $evento->addAttendee(['email' => 'admin@falchi.com.br']);
        // $evento->addAttendee(['email' => 'sem@falchi.com.br']);

        // $participantes = $evento->attendees;
        // if ($evento->attendees == null) {
        //     return view('Google.show', compact('evento'));
        // } else {

        // }

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

        $evento->save();

        return redirect(route('Agenda.index'));
    }

    public function destroy(string $id)
    {
        $evento = new Event();
        $evento = $evento->find($id);

        if (empty($evento)) {
            return redirect(route('Agenda.index'))->with('error', 'Evento não encontrado!');
        }

        $evento->delete();

        return redirect(route('Agenda.index'))->with('success', 'Evento excluído com sucesso!');
    }
}
