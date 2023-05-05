<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
// use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Google_Service_Drive_Permission;
use Google_Service_Drive;
use Google_Service_Exception;

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

            // Cache::put('token_google', $this->gClient->getAccessToken(), $seconds = 1800);
            session(['googleUserDrive'=>$this->gClient->getAccessToken()]);
            // dd($this->gClient);
            // Cache::put('dadoscliente_google', $this->gClient, $seconds = 1800);
            // dd('Autenticado no Google Drive');

            return redirect(route('googledrive.dashboard'));

        } else {
            // FOR GUEST USER, GET GOOGLE LOGIN URL
            $authUrl = $this->gClient->createAuthUrl();

            return redirect()->to($authUrl);
        }
    }




    public function googleDriveFileUpload(Request $request)
    {
        // https://laravel.com/docs/10.x/filesystem#the-local-driver


        $service = new \Google_Service_Drive($this->gClient);



        // $user= User::find(1);
        // Cache::put('token_google', session('googleUser')->token , $seconds = 1800);
        $this->gClient->setAccessToken(session('googleUserDrive'));


        if ($this->gClient->isAccessTokenExpired()) {


            $request->session()->put('token', false);
            return redirect('/drive/google/login');

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
        // $arquivo = $request->file('arquivo');


        // dd($request->file('arquivo'));


        /// usar na pasta do servidor - não apaga
        // $path = $request->file('arquivo')->store('contabilidade');

        /////// aqui fica na pasta temporário /temp/    - apaga
        $path = $request->file('arquivo')->getRealPath();

        $file = $request->file('arquivo');
        $Complemento = $request->complemento;
        $name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // $folder = '1SV8zXjgtfqViak_Jrlich-YVEM32bu8F';   FIXADO NO ARQUIVO .env
        $folder = env('FOLDER_DRIVE_GOOGLE');

        // $nome_arquivo = $request->file('arquivo')->getClientOriginalName();

        // // $nome_arquivo = Carbon::now().'-(100)-'.$request->file('arquivo')->getClientOriginalName();

        $nome_arquivo = Carbon::now().'-'.$request->file('arquivo')->getClientOriginalName();
        // preg_match('/\((\d+)\)/', $nome_arquivo, $matches);
        // $numero =  $matches[1];

        // dd($nome_arquivo);

        // $file = new \Google_Service_Drive_DriveFile(array('name' => 'piso1.jpg','parents' => array($folder->id)));
        $file = new \Google_Service_Drive_DriveFile(['name' => $nome_arquivo, 'parents' => [$folder]]);

        $result = $service->files->create($file, [
            // dd(Storage::path('contabilidade/sample.pdf')),
            // 'data' => file_get_contents(Storage::path($path)), // ADD YOUR FILE PATH WHICH YOU WANT TO UPLOAD ON GOOGLE DRIVE
            'data' => file_get_contents($path), // ADD YOUR FILE PATH WHICH YOU WANT TO UPLOAD ON GOOGLE DRIVE
            'mimeType' => 'application/octet-stream',
            'uploadType' => 'media',
        ]);


        // dd($result);

        $client = $this->gClient;
///////////////////////////////////////////////////////////////////////////////// tornar o arquivo privado
// $fileIdPrivado = '1CaOTqAaD71YtbMMM1g2djuJyXwMuwUAr';

// // Alterar as permissões do arquivo para torná-lo privado
// $permission = new Google_Service_Drive_Permission();

// $permission->setRole('owner');
// $permission->setType('user');
// $permission->setDomain('falchi.com.br');
// // $permission->setFileid($path);
// $permission->setEmailAddress('pedroroberto@falchi.com.br');
// $permission->setAllowFileDiscovery(false);
// // dd($service->permissions);
// //
// //  $permission->setSendNotificationEmail(false);
// $service->permissions->create($fileIdPrivado, $permission);
///////////////////////////////////////////////////////////////////////////////// /////////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////// Excluir arquivo

// $fileIdExcluir = '1CaOTqAaD71YtbMMM1g2djuJyXwMuwUAr';
// $service->files->delete($fileIdExcluir);
// dd($result);
///////////////////////////////////////////////////////////////////////////////// /////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////// verificar se exite o arquivo

try {
    $fileIdConsultar = '1CaOTqAaD71YtbMMM1g2djuJyXwMuwUAr';
    // $fileIdConsultar = '1l7xyPSqL8s07XyK-PeQ8D04Uapu2-8Py';

    // tenta buscar o arquivo pelo ID
    $file = $service->files->get($fileIdConsultar);


  } catch (Google_Service_Exception $e) {
    // trata o erro, se houver
    if($e->getCode() == 404)
    {
        $url = 'https://drive.google.com/open?id=' . $fileIdConsultar;
        return redirect($url);
        dd("O arquivo não existe.");
    }
    else
    {
        $url = 'https://drive.google.com/open?id=' .$fileIdConsultar;
        return redirect($url);
        dd("EXISTE");
    }
    // throw new Google_Service_Exception(dd('Não foi possível encontrar o arquivo especificado. ==> ERRO :'. $e->getCode()));

}




        ///////////////////////////////////////////////////////////////////////////////// tornar o arquivo público
        $fileIdPublico = $result->id; // Substitua pelo ID do seu arquivo
        $permission = new Google_Service_Drive_Permission();
        $permission->setRole('reader');
        $permission->setType('anyone');
        $permission->setAllowFileDiscovery(false);
        // $permission->setSendNotificationEmail(false);



        $driveService = new Google_Service_Drive($client); // Substitua $client pelo seu objeto de cliente autorizado
        $driveService->permissions->create($fileIdPublico, $permission, array('fields' => 'id'));
        /////////


        // GET URL OF UPLOADED FILE
        $url = 'https://drive.google.com/open?id=' . $result->id;

        return redirect($url);

        // dd($result);
    }
}
