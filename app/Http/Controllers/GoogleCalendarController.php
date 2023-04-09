<?php

namespace App\Http\Controllers;

use Google\Service\AnalyticsData\OrderBy;
use Illuminate\Http\Request;
use Google_Service;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Client;


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



    public function Calendario()
    {
        // Crie um objeto cliente Google API
        $client = new Google_Client();
        // $client->setAuthConfig('path/to/credentials.json');
        $client->setDeveloperKey('AIzaSyDI7EoB-HXEURqRec-XIUo2t5EmXxi5HEM');
        $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);

        $service = new Google_Service_Calendar($client);

        $event = new Google_Service_Calendar_Event(($client));
        // Autentique o cliente com as credenciais de API
        $service = new Google_Service_Calendar($client);

        // Faça uma chamada para a API do Google Calendar para obter eventos do calendário
        // $calendarId = 'pedroroberto@falchi.com.br';

        // $results = $service->events->listEvents($calendarId);

        // dd(['eventos' => $results->getItems()]);

        // $calendarId = 'pedroroberto@falchi.com.br';
        // $params = array(
        //     'orderBy' => 'updated',
        //     'maxResults' => 1000000000
        // );
        // $results = $service->events->listEvents($calendarId, $params);

        $calendarId = 'pedroroberto@falchi.com.br';
        $params = [
            'timeMin' => '2023-04-01T00:00:00.000Z',
            'timeMax' => '2023-04-09T23:59:59.999Z',
        ];
        $results = $service->events->listEvents($calendarId, $params);
        // dd($results[3]);
        // Renderize os resultados no Blade
        // return view('/Google.Calendario', ['eventos' => $results->getItems()]);
        return view('/Google.Calendario', ['eventos' => $results]);
    }

    public function InserirCalendario()
    {
        $client = new Google_Client();
        $service = new Google_Service_Calendar($client);
        $client->setApplicationName('calendar-pedro-r-falchi');
        $client->setAuthConfig('../client_secret_netrubifibra.json');


        $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $client->useApplicationDefaultCredentials();

        // Autentica o cliente
        $authUrl = $client->createAuthUrl();





        $event = new Google_Service_Calendar_Event([
            'summary' => 'Título do evento',
            'location' => 'Localização do evento',
            'description' => 'Descrição do evento',
            'start' => [
                'dateTime' => '2023-04-09T10:00:00-03:00',
                'timeZone' => 'America/Sao_Paulo',
            ],
            'end' => [
                'dateTime' => '2023-04-09T12:00:00-03:00',
                'timeZone' => 'America/Sao_Paulo',
            ],
        ]);

        $calendarId = 'pedroroberto@falchi.com.br';
        // dd($service);
        // $event = $service->events->insert($calendarId, $event);

        echo 'Ainda não funcionando para criar evento. Precisa autenticar. PENDENTE! : $event = $service->events->insert($calendarId, $event); ' . $event->htmlLink;


    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
