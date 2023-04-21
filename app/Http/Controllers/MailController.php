<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailController extends Controller
{

    private $email;
    private $name;
    private $client_id;
    private $client_secret;
    private $token;
    private $provider;

    /**
     * Default Constructor
     */
    public function __construct()
    {
        $this->client_id        = config('services.google.client_id');
        $this->client_secret    = config('services.google.client_secret');
        $this->provider         = new Google(
            [
                'clientId'      => $this->client_id,
                'clientSecret'  => $this->client_secret
            ]
        );

    }

    /**
     * Send Email via PHPMailer Library
     */
    public function send(Request $request)
    {
        $this->email            =  Auth()->user()->email; // ex. example@gmail.com
        $this->name             = Auth()->user()->name;     // ex. Abidhusain

        $this->token = $request->get('oauth_token');

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPAuth = true;
            $mail->AuthType = 'XOAUTH2';
            $mail->setOAuth(
                new OAuth(
                    [
                        'provider'          => $this->provider,
                        'clientId'          => $this->client_id,
                        'clientSecret'      => $this->client_secret,
                        'refreshToken'      => $this->token,
                        'userName'          => $this->email
                    ]
                )
            );

            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($this->email, $this->name);
            $mail->addCC('mauricio@net-rubi.com.br');
            $mail->Subject = 'Enviado pelo sistema Laravel 10';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $body = "Olá <b>$this->name</b>,<br><br>Depois de muitas horas, consegui fazer o envio de email via usuário autenticado.<br><br>Agradecimenots à<br><b>Google kkk</b>";
            $mail->msgHTML($body);
            $mail->AltBody = 'Uma previa do email ao passar o mouse em cima.';
            if( $mail->send() ) {
                return redirect(route('mail.home'))->with('success', 'Teste de e-mail realizado com sucesso!');
            } else {
                return redirect(route('mail.home'))->with('error', 'Erro ao enviar e-mail.');
            }
        } catch(Exception $e) {
            dd($e);
        }
    }
}
