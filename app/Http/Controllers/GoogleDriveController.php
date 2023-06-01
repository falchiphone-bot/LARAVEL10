<?php

namespace App\Http\Controllers;

use App\Models\LancamentoDocumento;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
// use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Google_Service_Drive_Permission;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Exception;
use  Google_Service_Drive_Comment;
use Illuminate\Support\Facades\Auth;

class GoogleDriveController extends Controller
{
    public $gClient;

    function __construct()
    {
        // BIBLIOTECAS - https://developers.google.com/identity/protocols/oauth2?hl=pt-br
        $this->gClient = new \Google_Client();
        $this->middleware('auth');
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
        $folder = 'https://drive.google.com/drive/u/O/folders/' . ' ' . env('FOLDER_DRIVE_GOOGLE');

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

        if (session('googleUser')) {
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
            session(['googleUserDrive' => $this->gClient->getAccessToken()]);
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

        /// usar na pasta do servidor - não apaga
        // $path = $request->file('arquivo')->store('contabilidade');

        /////// aqui fica na pasta temporário /temp/    - apaga
        $path = $request->file('arquivo')->getRealPath();

        $file = $request->file('arquivo');
        $Complemento = $request->complemento;
        $name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // $folder = '1Jzih3qPaWpf7HISQEsDpUpH0ab7eS-yJ';   //FIXADO NO ARQUIVO .env
        $folder = config('services.google_drive.folder');
        // $folder = null;
        if ($folder == null) {
            session([
                'InformacaoArquivo' => 'Pasta não informada! Verifique o arquivo de configuração env( FOLDER_DRIVE_GOOGLE ). Execute: # php artisan config:clear no SERVIDOR DOCKER LARAVEL'
            ]);
            return redirect(route('informacao.arquivos'));
        }
        $folderTemp =config('services.google_drive.folder');
        // $folderTemp = null;
        if ($folderTemp == null) {
            session([
                'InformacaoArquivo' => 'Pasta não informada! Verifique o arquivo de configuração env( FOLDER_DRIVE_GOOGLE_TEMPORARIA ).',
            ]);
            return redirect(route('informacao.arquivos'));
        }
        // $nome_arquivo = $request->file('arquivo')->getClientOriginalName();

        // // $nome_arquivo = Carbon::now().'-(100)-'.$request->file('arquivo')->getClientOriginalName();

        $nome_arquivo = Carbon::now() . '-' . $request->file('arquivo')->getClientOriginalName();
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

        $client = $this->gClient;



        // dd($result, explode('.', $result->getId()), explode('.', $result->getName())[1]);
                  $Documentos= LancamentoDocumento::create([
            'Rotulo' => $Complemento,
            'LancamentoID' => null,
            'Nome' => $result->getId(),
            'Created' => date('d-m-Y H:i:s'),
            'UsuarioID' => Auth::user()->id,
            'Ext' => explode('.', $result->getName())[1],
        ]);

                session([
                    'InformacaoArquivo' => 'Arquivo enviado com sucesso. O ID do mesmo é '.$result->id,
                ]);
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

        // try {
        //     $fileIdConsultar = '1CaOTqAaD71YtbMMM1g2djuJyXwMuwUAr';
        //     // $fileIdConsultar = '1l7xyPSqL8s07XyK-PeQ8D04Uapu2-8Py';

        //     // tenta buscar o arquivo pelo ID
        //     $file = $service->files->get($fileIdConsultar);

        // } catch (Google_Service_Exception $e) {
        //     // trata o erro, se houver
        //     if($e->getCode() == 404)
        //     {
        //         $url = 'https://drive.google.com/open?id=' . $fileIdConsultar;
        //         return redirect($url);
        //         dd("O arquivo não existe.");
        //     }
        //     else
        //     {
        //         $url = 'https://drive.google.com/open?id=' .$fileIdConsultar;
        //         return redirect($url);
        //         dd("EXISTE");
        //     }
        //     // throw new Google_Service_Exception(dd('Não foi possível encontrar o arquivo especificado. ==> ERRO :'. $e->getCode()));

        // }

        // ///////////////////////////////////////////////////////////////////////////////// tornar o arquivo público
        // $fileIdPublico = $result->id; // Substitua pelo ID do seu arquivo
        // $permission = new Google_Service_Drive_Permission();
        // $permission->setRole('reader');
        // $permission->setType('anyone');
        // $permission->setAllowFileDiscovery(false);
        // // $permission->setSendNotificationEmail(false);

        // $driveService = new Google_Service_Drive($client); // Substitua $client pelo seu objeto de cliente autorizado
        // $driveService->permissions->create($fileIdPublico, $permission, array('fields' => 'id'));
        // /////////

        // GET URL OF UPLOADED FILE
        // $url = 'https://drive.google.com/open?id=' . $result->id;

        // return redirect($url);

        // dd($result);

        return redirect(route('informacao.arquivos'));
    }

    public function googleDriveFileDelete(Request $request)
    {
        $service = new \Google_Service_Drive($this->gClient);

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

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////// Excluir arquivo para a Lixeira do Google Drive
        try {
        # Obter informações sobre o arquivo ou pasta
        // $fileArquivoFolder = $service->files->get($request->iddeletar);
        // if ($fileArquivoFolder->mimeType == 'application/vnd.google-apps.folder') {
        //     session(['InformacaoArquivo' => 'Encontrado o id:' . $request->iddeletar . '. Ele é uma pasta de nome: ' . $fileArquivoFolder->name . '. NÃO POSSO EXCLUIR POR ESTE PROCEDIMENTO!']);
        //     return redirect(route('informacao.arquivos'));
        // }
        $fileIdExcluir = $request->iddeletar;
        if ($fileIdExcluir == '1Jzih3qPaWpf7HISQEsDpUpH0ab7eS-yJ') {
            session([
                'InformacaoArquivo' => 'Isso é id da pasta dos arquivos do sistema! NÃO PODE SER EXCLUÍDA!',
            ]);
            return redirect(route('informacao.arquivos'));
        }

            $fileMetadata = new \Google_Service_Drive_DriveFile([
                // 'name' => 'Prfcontabilidade', // ADD YOUR GOOGLE DRIVE FOLDER NAME
                // 'mimeType' => 'application/vnd.google-apps.folder',
                //// foram retirados para não alterar nada
            ]);
            $fileMetadata->setTrashed(true);
            $result = $service->files->update($fileIdExcluir, $fileMetadata);

            session(['InformacaoArquivo' => 'Arquivo:' . $fileIdExcluir . '. EXCLUÍDO COM SUCESSO PARA A LIXEIRA DO GOOGLE DRIVE!']);

        } catch (Google_Service_Exception $e) {
            ////////////PROPRIETÁRIO DO ARQUIVO
            // Fazer a consulta de metadados do arquivo
            $file = $service->files->get($fileIdExcluir, ['fields' => 'owners']);

            # Obter informações sobre o arquivo ou pasta
            $fileArquivoFolder = $service->files->get($fileIdExcluir);

            if ($file) {
                $owner = $file->getOwners()[0];

                session([
                    'avatar' => $owner->getPhotoLink(),
                ]);

                session([
                    'InformacaoArquivoProprietário' => 'Encontrado o arquivo:' . $fileIdExcluir . '. O proprietário é ' . $owner->getDisplayName() . '. Email: ' . $owner->getEmailAddress(),
                ]);

                //////

                session([
                    'InformacaoArquivo' => 'Arquivo:' . $fileIdExcluir . '. Não foi possível excluir o arquivo especificado pelos motivos a seguir: ==> ERRO :' . $e->GetMessage(),
                ]);
            }
        }
        return redirect(route('informacao.arquivos'));
        ///////////////////////////////////////////////////////////////////////////////// /////////////////////////////////////////////////////////////////////////////////
    }

    public function googleDriveFileConsultar(Request $request)
    {
        // $service = new \Google_Service_Drive($this->gClient);
        $service = new Google_Service_Drive($this->gClient);
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

        $client = $this->gClient;

        // ID do arquivo a ser consultado
        // $fileId = '1HOEUTvekJzsGNchPLJA7MUupY1L_DQgz';
        $fileIdConsultar = $request->idconsultararquivo;

        try {
            // Fazer a consulta de metadados do arquivo
            $file = $service->files->get($fileIdConsultar, ['fields' => 'owners']);

            # Obter informações sobre o arquivo ou pasta
            $fileArquivoFolder = $service->files->get($fileIdConsultar);



        //     $fileMetadata = new Google_Service_Drive_DriveFile(array(
        //         'description' => 'CONTRATO COM  FERNANDO CHAVES EM 10.05.2023, em 7 parcelas de 2000,00'
        //     ));
        //     $file = $service->files->update($fileIdConsultar, $fileMetadata, array(
        //         'fields' => 'description'
        //     ));

            $fields = 'id,name,mimeType,createdTime,modifiedTime,size,description,webContentLink';
            $filemetadados = $service->files->get($fileIdConsultar, array(
                'fields' => $fields
            ));




            # Verificar se o ID se refere a uma pasta ou arquivo
            if ($fileArquivoFolder->mimeType == 'application/vnd.google-apps.folder') {
                session(['InformacaoArquivo' => 'Encontrado o id:' . $fileIdConsultar . '. Ele é uma pasta de nome: ' . $fileArquivoFolder->name]);
                return redirect(route('informacao.arquivos'));
            }

            // Verificar se o arquivo existe e mostrar o nome do proprietário
            if ($file) {
                $owner = $file->getOwners()[0];

                session([
                    'avatar' => $owner->getPhotoLink(),
                ]);


                $informacoes = array(
                    'fileIdConsultar' => $fileIdConsultar,
                    'ownerDisplayName' => $owner->getDisplayName(),
                    'emailAddress' => $owner->getEmailAddress(),
                    'webContentLink' => $filemetadados->getWebContentLink(),
                    'description' => $filemetadados->getDescription()
                );
                // $informacaoArquivo = implode('|', $informacoes);
                // session(['InformacaoArquivo' => $informacaoArquivo]);
                session(['InformacaoArquivo' => null]);
                session(['InformacaoArquivoConsulta' => $informacoes]);

                return redirect(route('informacao.arquivos'));
            } else {
                ////////// Quando o id não é localizado no Google Drive é causado uma Exception
            }
        } catch (Google_Service_Exception $e) {
            session([
                'InformacaoArquivo' => 'Erro de pesquisa. Provávelmente arquivo não encontrado:' . $fileIdConsultar.' Mais informações: '.$e,
            ]);
            return redirect(route('informacao.arquivos'));
        }
    }

    public function googleDriveFileMover(Request $request)
    {


        // $service = new \Google_Service_Drive($this->gClient);
        $service = new Google_Service_Drive($this->gClient);
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

        // $client = $this->gClient;

        $fileIdMover = $request->idmoverarquivo;

        try {
            // Fazer a consulta de metadados do arquivo
            $fileMover = $service->files->get($fileIdMover, ['fields' => 'owners']);

            # Obter informações sobre o arquivo ou pasta
            $fileArquivoFolder = $service->files->get($fileIdMover);

            # Verificar se o ID se refere a uma pasta ou arquivo
            if ($fileArquivoFolder->mimeType == 'application/vnd.google-apps.folder') {
                session(['InformacaoArquivo' => 'Encontrado o id:' . $fileIdMover . '. Ele é uma pasta de nome: ' . $fileArquivoFolder->name]);
                return redirect(route('informacao.arquivos'));
            }

            // Verificar se o arquivo existe e mostrar o nome do proprietário
            if ($fileMover) {
                $owner = $fileMover->getOwners()[0];

                session([
                    'avatarProprietário' => $owner->getPhotoLink(),
                ]);

                session([
                    'InformacaoArquivo' => 'Encontrado o arquivo:' . $fileIdMover . '. O proprietário é ' . $owner->getDisplayName() . '. Email: ' . $owner->getEmailAddress(),
                ]);
                ////////////////////////////MOVER

                                                        $fileId = $fileIdMover; // ID do arquivo que deseja atualizar
                                                        $newContent = 'Novo conteúdo do arquivo'; // Novo conteúdo do arquivo (opcional)

                                                        // Obter informações atuais do arquivo
                                                        $file = $service->files->get($fileId, ['fields' => 'id, parents, name, mimeType']);

                                                        // Mover o arquivo para a pasta pai desejada (opcional)
                                                        $folderId = env('FOLDER_DRIVE_GOOGLE_TEMPORARIA');  // ID da pasta pai desejada
                                                        if (!in_array($folderId, $file->parents)) {
                                                            $previousParents = join(',', $file->parents);
                                                            $file->parents = [$folderId];
                                                            $updatedFile = $service->files->update($fileId, $file, [
                                                                'addParents' => $folderId,
                                                                'removeParents' => $previousParents,
                                                                'fields' => 'id, parents',
                                                            ]);
                                                        }

                                                        // Atualizar o arquivo
                                                        $fileMetadata = new Google_Service_Drive_DriveFile([
                                                            'name' => $file->name,
                                                            'parents' => [$folderId], // Define a nova pasta pai do arquivo
                                                            'mimeType' => $file->mimeType,
                                                        ]);

                                                        if ($newContent) {
                                                            $file = $service->files->update($fileId, $fileMetadata, [
                                                                'data' => $newContent,
                                                                'uploadType' => 'media',
                                                            ]);
                                                        } else {
                                                            $file = $service->files->update($fileId, $fileMetadata);
                                                        }

                ////////////////////////////FIM DE MOVER

                session([
                    'InformacaoArquivo' => 'Encontrado o arquivo:' . $fileIdMover . '. O proprietário é ' . $owner->getDisplayName() . '. Email: ' . $owner->getEmailAddress() . ' MOVIDO COM SUCESSO!',
                ]);
                return redirect(route('informacao.arquivos'));
            } else {
                ////////// Quando o id não é localizado no Google Drive é causado uma Exception
            }
        } catch (Google_Service_Exception $e) {
            session([
                'InformacaoArquivo' => 'Erro de pesquisa. Provávelmente arquivo não encontrado:' . $fileIdMover . '. MAIS INFORMAÇÕES:' . $e->GetMessage(),
            ]);
            return redirect(route('informacao.arquivos'));
        }
    }

    public function googleDriveFileAlterarNome(Request $request)
    {
        // $service = new \Google_Service_Drive($this->gClient);
        $service = new Google_Service_Drive($this->gClient);
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

        // $client = $this->gClient;

        $fileId = $request->idarquivo;
        $nome = $request->NovoNome;
        try {
            // Fazer a consulta de metadados do arquivo
            $fileMover = $service->files->get($fileId, ['fields' => 'owners']);

            # Obter informações sobre o arquivo ou pasta
            $fileArquivoFolder = $service->files->get($fileId);

            # Verificar se o ID se refere a uma pasta ou arquivo
            if ($fileArquivoFolder->mimeType == 'application/vnd.google-apps.folder') {
                session(['InformacaoArquivo' => 'Encontrado o id:' . $fileId . '. Ele é uma pasta de nome: ' . $fileArquivoFolder->name]);
                return redirect(route('informacao.arquivos'));
            }

            // Verificar se o arquivo existe e mostrar o nome do proprietário
            if ($fileMover) {
                $owner = $fileMover->getOwners()[0];

                session([
                    'avatarProprietário' => $owner->getPhotoLink(),
                ]);

                session([
                    'InformacaoArquivo' => 'Encontrado o arquivo:' . $fileId . '. O proprietário é ' . $owner->getDisplayName() . '. Email: ' . $owner->getEmailAddress(),
                ]);
                //////////////////////////////ALTERAR NOME DO ARQUIVO
                // $fileId = $fileId; // ID do arquivo que deseja atualizar
                $newName = $nome; // Novo nome do arquivo
                $newContent = 'Novo conteúdo do arquivo'; // Novo conteúdo do arquivo (opcional)

                $file =  new \Google_Service_Drive_DriveFile();


                $file->setName($newName); // Define o novo nome do arquivo

                if ($newContent) {
                    // Define o novo conteúdo do arquivo

                    $updatedFile = $service->files->update($fileId, $file, [
                        'data' => $newContent,
                        'uploadType' => 'media'
                    ]);
                } else {
                    $updatedFile = $service->files->update($fileId, $file);
                }
                ///// FIM DE MOVER



                session([
                    'InformacaoArquivo' => 'Encontrado o arquivo:' . $fileId . '. O proprietário é ' . $owner->getDisplayName() . '. Email: ' . $owner->getEmailAddress() . ' MOVIDO COM SUCESSO!',
                ]);
                return redirect(route('informacao.arquivos'));
            } else {
                ////////// Quando o id não é localizado no Google Drive é causado uma Exception
            }
        } catch (Google_Service_Exception $e) {
            session([
                'InformacaoArquivo' => 'Erro de pesquisa. Provávelmente arquivo não encontrado:' . $fileId . '. MAIS INFORMAÇÕES:' . $e->GetMessage(),
            ]);
            return redirect(route('informacao.arquivos'));
        }
    }

    public function googleDriveFileDeleteDefinitivo(Request $request)
    {
        $service = new \Google_Service_Drive($this->gClient);

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

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////// Excluir arquivo DEFINITIVAMENTE do Google Drive
        try {

        $fileIdExcluir = $request->iddeletar;
        if ($fileIdExcluir == '1Jzih3qPaWpf7HISQEsDpUpH0ab7eS-yJ') {
            session([
                'InformacaoArquivo' => 'Isso é id da pasta dos arquivos do sistema! NÃO PODE SER EXCLUÍDA!',
            ]);
            return redirect(route('informacao.arquivos'));
        }

            $service->files->delete($fileIdExcluir);/// exclui definitivamente do Google Drive

            // $fileMetadata = new \Google_Service_Drive_DriveFile([
            //     // 'name' => 'Prfcontabilidade', // ADD YOUR GOOGLE DRIVE FOLDER NAME
            //     // 'mimeType' => 'application/vnd.google-apps.folder',
            //     //// foram retirados para não alterar nada
            // ]);
            // $fileMetadata->setTrashed(true);
            // $result = $service->files->update($fileIdExcluir, $fileMetadata);

            session(['InformacaoArquivo' => 'Arquivo:' . $fileIdExcluir . '. EXCLUÍDO DEFINITIVAMENTE COM SUCESSO!']);

        } catch (Google_Service_Exception $e) {
            ////////////PROPRIETÁRIO DO ARQUIVO
            // Fazer a consulta de metadados do arquivo
            $file = $service->files->get($fileIdExcluir, ['fields' => 'owners']);

            # Obter informações sobre o arquivo ou pasta
            $fileArquivoFolder = $service->files->get($fileIdExcluir);

            if ($file) {
                $owner = $file->getOwners()[0];

                session([
                    'avatar' => $owner->getPhotoLink(),
                ]);

                session([
                    'InformacaoArquivoProprietário' => 'Encontrado o arquivo:' . $fileIdExcluir . '. O proprietário é ' . $owner->getDisplayName() . '. Email: ' . $owner->getEmailAddress(),
                ]);

                //////

                session([
                    'InformacaoArquivo' => 'Arquivo:' . $fileIdExcluir . '. Não foi possível excluir o arquivo especificado pelos motivos a seguir: ==> ERRO :' . $e->GetMessage(),
                ]);
            }
        }
        return redirect(route('informacao.arquivos'));
        ///////////////////////////////////////////////////////////////////////////////// /////////////////////////////////////////////////////////////////////////////////
    }


    public function googleDriveFileComentario(Request $request)
    {
        // $service = new \Google_Service_Drive($this->gClient);
        $service = new Google_Service_Drive($this->gClient);
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

        $client = $this->gClient;

        // ID do arquivo a ser consultado
        // $fileId = '1HOEUTvekJzsGNchPLJA7MUupY1L_DQgz';
        $fileIdComentario = $request->idcomentarioarquivo;

        try {
            // Fazer a consulta de metadados do arquivo

            // Verificar se o arquivo existe e mostrar o nome do proprietário
            if ($fileIdComentario) {

                $fields = 'id,name,mimeType,createdTime,modifiedTime,size,description,webContentLink';
            $filemetadados = $service->files->get($fileIdComentario, array(
                'fields' => $fields
            ));

                //////////////////////////////////////// comentários no arquivo
            $ComentarioAnterior = $filemetadados->description;
            $fileMetadata = new Google_Service_Drive_DriveFile(array(
                'description' =>  $ComentarioAnterior." Em ".Carbon::now()->format('d/m/Y H:i:s')." -> ".$request->NovoComentario." | "
            ));
            $file = $service->files->update($fileIdComentario, $fileMetadata, array(
                        'fields' => 'description'
                    ));

                  $informacoes = array(
                    'id' => $fileIdComentario,
                    'mensagem' => 'INSERIDO NOVO COMENTÁRIO',
                    'novocomentario' => $request->NovoComentario,
                );
                session(['idArquivo' => $informacoes]);

                // return redirect(route('informacao.arquivos'));
                return redirect(route('consultar.arquivos'));

            } else {
                ////////// Quando o id não é localizado no Google Drive é causado uma Exception
            }
        } catch (Google_Service_Exception $e) {
            session([
                'InformacaoArquivo' => 'Erro de pesquisa. Provávelmente arquivo não encontrado:' . $fileIdComentario.' Mais informações: '.$e,
            ]);
            return redirect(route('informacao.arquivos'));
        }
    }


}
