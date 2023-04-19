<?php

namespace App\Http\Controllers;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Illuminate\Support\Facades\Log;
use App\Services\GmailSender;
use App\Http\Controllers\auth2;

class EnviaEmailController extends Controller
{
    public function GoogleLogin()
    {
        

        return view('Google/GoogleLogin');

    }

    public function EnviarEmail()
    {


        $sender = new GmailSender();
        $sender->send('contabilidadeprf@gmail.com', 'teste', 'teste por Gmail');
        dd('Enviado');
    }


}

