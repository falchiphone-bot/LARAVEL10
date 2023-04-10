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

// Inclua o autoload do Composer
// require __DIR__ . '/../../../Users/pedrorobertofalchi/Projetos/IniciacaoLaravel-10/vendor/autoload.php';
require '../vendor/autoload.php';

// Users/pedrorobertofalchi/Projetos/IniciacaoLaravel-10/vendor/autoload.php;

class GoogleCalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:CALENDARIO - LISTAR'])->only('index');
        // $this->middleware(['permission:MOEDAS - INCLUIR'])->only(['create', 'store']);
        // $this->middleware(['permission:MOEDAS - EDITAR'])->only(['edit', 'update']);
        // $this->middleware(['permission:MOEDAS - VER'])->only(['edit', 'update']);
        // $this->middleware(['permission:MOEDAS - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        return view('Google.dashboard');
    }

    public function index()
    {
        $eventos = new Event();
        $eventos = $eventos->get();

        // dd($eventos->first());

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
        //
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
        $evento = $evento->find( $id);

        if (empty($evento)) {
            return redirect(route('Agenda.index'))->with('error', 'Evento não encontrado!');
        }

        $evento->delete();

        return redirect(route('Agenda.index'))->with('success', 'Evento excluído com sucesso!');
    }
}
