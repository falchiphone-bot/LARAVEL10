<?php

namespace App\Http\Controllers;

use Google\Service\AnalyticsData\OrderBy;
use Illuminate\Http\Request;

use Google_Service_Calendar;
use Google_Client;
use Google_Service;

// Inclua o autoload do Composer
// require __DIR__ . '/../../../Users/pedrorobertofalchi/Projetos/IniciacaoLaravel-10/vendor/autoload.php';
require '../vendor/autoload.php';

// Users/pedrorobertofalchi/Projetos/IniciacaoLaravel-10/vendor/autoload.php;

class GoogleCalendarController extends Controller
{
    public function Calendario()
    {
        // Crie um objeto cliente Google API
        $client = new Google_Client();
         $client->setDeveloperKey('AIzaSyDI7EoB-HXEURqRec-XIUo2t5EmXxi5HEM');
        // $client->setApplicationName('falchi_laravel');
        // $client->setAuthConfig('../credentials.json');




        $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
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
$params = array(
    'timeMin' => '2023-04-01T00:00:00.000Z',
    'timeMax' => '2023-04-09T23:59:59.999Z'
);
$results = $service->events->listEvents($calendarId, $params);

        // Renderize os resultados no Blade
        // return view('/Google.Calendario', ['eventos' => $results->getItems()]);
        return view('/Google.Calendario', ['eventos' => $results]);

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
