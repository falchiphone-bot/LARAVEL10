<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GoogleDriveController extends Controller
{
    public $gClient;

    function __construct()
    {
        // BIBLIOTECAS - https://developers.google.com/identity/protocols/oauth2?hl=pt-br
        $this->gClient = new \Google_Client();

        // $this->gClient->setApplicationName('YOUR APPLICATION NAME'); // ADD YOUR AUTH2 APPLICATION NAME (WHEN YOUR GENERATE SECRATE KEY)
        // $this->gClient->setClientId('154506411439-v35hmhf8t50s6lloljhb6q69blt7vaa0.apps.googleusercontent.com');
        // $this->gClient->setClientSecret('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');
        $this->gClient->setClientId('152725472725-7vb6t4u4092uijg7lrgqhnha9ka22vbc.apps.googleusercontent.com');
        $this->gClient->setClientSecret('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');
        $this->gClient->setRedirectUri(route('google.login'));
        $this->gClient->setDeveloperKey('AIzaSyCZtB2vF2JDA5m3ZbskCT-ku2P-QaqPvZ4');
        $this->gClient->setScopes(['https://www.googleapis.com/auth/drive.file', 'https://www.googleapis.com/auth/drive']);

        $this->gClient->setAccessType('offline');

        $this->gClient->setApprovalPrompt('force');
    }

    public function dashboard(Request $request)
    {
        return view('GoogleDrive.dashboard')->with('tokenGoogle', session('tokengoogledrive'));
    }

    public function showGoogleClientInfo()
    {
        // dd(session('googleUser'));
        // $gClientInfo = [
        //     'authConfig' => $this->gClient->getAuthConfig(),
        //     'scopes' => $this->gClient->getScopes(),
        //     'accessToken' => $this->gClient->getAccessToken(),
        //     // Adicione outras informações que desejar aqui
        // ];
        // // $gClientInfo = 'teste';


        if(session('googleUser')) {
            return view('GoogleDrive/DadosClienteGoogle');
         } else {
             return redirect('/auth/google');
         }


    }

    public function googleLogin(Request $request)
    {
        $google_oauthV2 = new \Google_Service_Oauth2($this->gClient);

        if ($request->get('code')) {
            $this->gClient->authenticate($request->get('code'));

            $request->session()->put('token', $this->gClient->getAccessToken());
        }

        if ($request->session()->get('token')) {
            $this->gClient->setAccessToken($request->session()->get('token'));
        }

        if ($this->gClient->getAccessToken()) {
            //FOR LOGGED IN USER, GET DETAILS FROM GOOGLE USING ACCES
            // $user = User::find(1);

            // $user->access_token = json_encode($request->session()->get('token'));

            // $user->save();

            Cache::put('token_google', $this->gClient->getAccessToken(), $seconds = 1800);
            dd($this->gClient);
            Cache::put('dadoscliente_google', $this->gClient, $seconds = 1800);
            // dd('Autenticado no Google Drive');

            return redirect(route('google.showGoogleClientInfo'));

        } else {
            // FOR GUEST USER, GET GOOGLE LOGIN URL
            $authUrl = $this->gClient->createAuthUrl();

            return redirect()->to($authUrl);
        }
    }

    public function googleDriveFileUpload()
    {
        $service = new \Google_Service_Drive($this->gClient);

        // $user= User::find(1);
        // Cache::put('token_google', session('googleUser')->token , $seconds = 1800);
        $this->gClient->setAccessToken(Cache::get('token_google'));

        // dd($this->gClient);

        if ($this->gClient->isAccessTokenExpired()) {
            // SAVE REFRESH TOKEN TO SOME VARIABLE
            $refreshTokenSaved = $this->gClient->getRefreshToken();

            // UPDATE ACCESS TOKEN
            $this->gClient->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

            // PASS ACCESS TOKEN TO SOME VARIABLE
            $updatedAccessToken = $this->gClient->getAccessToken();

            // APPEND REFRESH TOKEN
            $updatedAccessToken['refresh_token'] = $refreshTokenSaved;

            // SET THE NEW ACCES TOKEN
            $this->gClient->setAccessToken($updatedAccessToken);

            $user->access_token = $updatedAccessToken;

            $user->save();
        }

        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => 'Prfcontabilidade', // ADD YOUR GOOGLE DRIVE FOLDER NAME
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        // $folder = $service->files->create($fileMetadata, array('fields' => 'id'));

        // printf("Folder ID: %s\n", $folder->id);

        $folder = '1SV8zXjgtfqViak_Jrlich-YVEM32bu8F';
        // $file = new \Google_Service_Drive_DriveFile(array('name' => 'piso1.jpg','parents' => array($folder->id)));
        $file = new \Google_Service_Drive_DriveFile(['name' => 'piso1.jpg', 'parents' => [$folder]]);

        $result = $service->files->create($file, [
            // dd(Storage::path('contabilidade/sample.pdf')),
            'data' => file_get_contents(Storage::path('contabilidade/piso1.jpg')), // ADD YOUR FILE PATH WHICH YOU WANT TO UPLOAD ON GOOGLE DRIVE
            'mimeType' => 'application/octet-stream',
            'uploadType' => 'media',
        ]);

        // GET URL OF UPLOADED FILE

        $url = 'https://drive.google.com/open?id=' . $result->id;

        dd($result);
    }
}
