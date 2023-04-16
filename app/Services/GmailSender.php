<?php

namespace App\Services;

use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Illuminate\Support\Facades\Log;
use app\Services\GmailSender;

class GmailSender
{
    private $client;

    public function __construct()
    {
        $path = base_path('google-api-php-client.json');
        $this->client = new Google_Client();
        $this->client->setApplicationName('Email_falchi');
        $this->client->setScopes([Google_Service_Gmail::GMAIL_SEND]);
        $this->client->setAuthConfig($path);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

//         $this->client-> ServiceAccountCredentials.fromStream(new FileInputStream($path).createScoped(GmailScopes.GMAIL_SEND, GmailScopes.GMAIL_COMPOSE, GmailScopes.GMAIL_MODIFY, GmailScopes.MAIL_GOOGLE_COM));
//         $this->client-> GoogleCredentials delegatedCreds = creds.createDelegated(userEmail);
//         $this->client->HttpRequestInitializer requestInitializer = new HttpCredentialsAdapter(delegatedCreds);
//         $this->client->HttpTransport transport = new NetHttpTransport.Builder().build();
//    Construct the gmail object.
//    $this->client->Gmail gm = new Gmail.Builder(transport, JSON_FACTORY, requestInitializer)
//          .setApplicationName(APPLICATION_NAME)
//          .build();



    }

    public function send($to, $subject, $body)
    {
        $service = new Google_Service_Gmail($this->client);
        $str = "To: $to\r\n";
        $str .= "Subject: $subject\r\n";
        $str .= "\r\n$body";


        try {
            $message = new Google_Service_Gmail_Message();
            $message->setRaw(base64_encode($str));
            $service->users_messages->send("me", $message);

        } catch (\Exception $e) {
            dd($e);
            Log::error($e);
        }
    }
}
