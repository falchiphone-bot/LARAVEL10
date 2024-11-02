<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArquivoDocumentosCreateRequest;
use App\Http\Requests\MoedaCreateRequest;
use App\Http\Requests\MoedaValoresCreateRequest;
use App\Models\DocumentosArquivoVinculo;
use App\Models\LancamentoDocumento;
use App\Models\TipoArquivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Strings;
use PHPUnit\Framework\Constraint\Count;


class LancamentosDocumentosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - LISTAR'])->only('index');
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    // public function dashboard()
    // {
    //     return view('Moedas.dashboard');
    // }

    public function index()
    {

        $documentos = LancamentoDocumento::Limit(100)->OrderBy('ID','DESC' )->get();
        $tipoarquivo = TipoArquivo::get();
        $retorno['TipoArquivo'] = null;


        return view('LancamentosDocumentos.index',compact('documentos','tipoarquivo','retorno'));
    }

    public function indexpost(string $id)
    {

        if($id){
            $documentos = LancamentoDocumento::Where('ID',$id)->get();
        }else
        {
            $documentos = LancamentoDocumento::Limit(100)->OrderBy('ID','DESC' )->get();
        }
        $tipoarquivo = TipoArquivo::get();
        $retorno['TipoArquivo'] = null;
        return view('LancamentosDocumentos.index',compact('documentos','tipoarquivo','retorno'));
    }

    public function pesquisaavancada(Request $Request)
    {
        $CompararDataInicial = $Request->DataInicial;
        $tipoarquivo = TipoArquivo::get();
        $pesquisa =  LancamentoDocumento::Limit($Request->Limite ?? 100);

        // $pesquisa = Lancamento::Limit($Request->Limite ?? 100)
        //     ->join('Contabilidade.EmpresasUsuarios', 'Lancamentos.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
        //     ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
        //     ->Where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        //     ->select(['Lancamentos.ID', 'DataContabilidade', 'Lancamentos.Descricao', 'Lancamentos.EmpresaID', 'Contabilidade.Lancamentos.Valor', 'Historicos.Descricao as DescricaoHistorico', 'Lancamentos.ContaDebitoID', 'Lancamentos.ContaCreditoID'])
        //     ->orderBy('Lancamentos.ID', 'desc');



        if ($Request->Texto) {
            $texto = $Request->Texto;
            // $pesquisa->where(function ($query) use ($texto) {
            //     // return $query->where('LancamentosDocumentos.Rotulo', 'like', '%' . $texto . '%')->orWhere('Historicos.Descricao', 'like', '%' . $texto . '%');
            //     return $query->where('LancamentosDocumentos.Rotulo', 'like', '%' . $texto . '%');
            // });
            $pesquisa->where('LancamentosDocumentos.Rotulo', 'like', '%' . $texto . '%');
        }

        // if ($Request->Valor) {
        //     $pesquisa->where('Lancamentos.Valor', '=', $Request->Valor);
        // }

        // if ($Request->DataInicial) {
        //     $DataInicial = Carbon::createFromFormat('Y-m-d', $Request->DataInicial);
        //     $pesquisa->where('DataContabilidade', '>=', $DataInicial->format('d/m/Y'));
        // }

        // if ($Request->DataFinal) {
        //     $DataFinal = Carbon::createFromFormat('Y-m-d', $Request->DataFinal);
        //     $pesquisa->where('DataContabilidade', '<=', $DataFinal->format('d/m/Y'));
        // }

        // $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
        //     ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        //     ->OrderBy('Descricao')
        //     ->select(['Empresas.ID', 'Empresas.Descricao'])
        //     ->get();

        $retorno = $Request->all();

        if ($pesquisa->count() > 0) {
            session(['success' => 'A pesquisa abaixo mostra os lançamentos de todas as empresas autorizadas conforme a pesquisa proposta!']);
        }
        else
        {
            session(['error' => 'Nenhum lançamento encontrado para as empresas autorizadas!']);
        }

        // if ($Request->DataInicial && $Request->DataFinal) {
        //     if ($DataInicial > $DataFinal) {
        //         session(['error' => 'Data de início MAIOR que a final. VERIFIQUE!']);
        //         return view('LancamentosDocumentos.index', compact('pesquisa', 'retorno'  ));
        //     }
        // }

        // if ($Request->EmpresaSelecionada) {
        //     $pesquisa->where('Lancamentos.EmpresaID', $Request->EmpresaSelecionada);
        // }

        if($Request->SelecionarSemContabilidade)
        {
            $pesquisa->where('LancamentoID', null)
            ->where('Documento', null);
        }

        if($Request->SelecionarClubeComContabilidade)
              {
                $pesquisa->where('Documento', '>' ,0);
                }

        if($Request->SelecionarComContabilidade)
        {

            $pesquisa->where('LancamentoID', '>', 0);

        }

        if($Request->SelecionarTipoArquivo)
        {

            $pesquisa->where('TipoArquivo', '=', $Request->SelecionarTipoArquivo);

        }

        if($Request->ordem == 'crescente')
        {
            $pesquisa->OrderBy('ID','ASC');
        }

        if($Request->ordem == 'decrescente')
        {
            $pesquisa->OrderBy('ID', 'DESC');

        }

        $pesquisaFinal = $pesquisa->get();
        $documentos = $pesquisaFinal;

        $retorno['TipoArquivo'] = $Request->SelecionarTipoArquivo;
        // dd($pesquisa->first()->ContaDebito->PlanoConta);
        return view('LancamentosDocumentos.index', compact('pesquisa', 'retorno','documentos','tipoarquivo'    ));
    }

    public function edit(string $id)
    {
        $documento = LancamentoDocumento::find($id);



        $documentoListado = LancamentoDocumento::with('TipoArquivoNome')
        ->where('TipoArquivo', '>', 0)
        ->where('ID', '!=', $id)
        ->orderBy('ID', 'desc')
        ->get();


        $retorno['TipoArquivo'] = $documento->TipoArquivo ?? null;
        $tipoarquivo = TipoArquivo::OrderBy('nome')->get();


        $arquivoExiste = null;
        $DocumentoArquivo = DocumentosArquivoVinculo::where('documento_id','=', $id)
             ->Orwhere('arquivo_id_vinculo','=', $id)
             ->orderBy('id')
             ->get();

             foreach ($DocumentoArquivo as $DocumentoArquivos) {
                 $arquivoExiste = $DocumentoArquivos->id;

             }




        return view('LancamentosDocumentos.edit',compact('documento','tipoarquivo','retorno','tipoarquivo','arquivoExiste','DocumentoArquivo','documentoListado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $idEdit = $id;


        $cadastro = LancamentoDocumento::find($id);

        if($cadastro->NomeLocalTimeStamps == null){
            $currentTimestamp = time();
            // dd($currentTimestamp);
            $cadastro->NomeLocalTimestamps = $currentTimestamp;
        }


        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect()->route('LancamentosDocumentosID.index', ['id' => $id]);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       dd('PROCEDIMENTO DE EXCLUSÃO AINDA NÃO DEFINIDA!');
        // $moedas = Moeda::find($id);


        // $moedas->delete();
        return redirect(route('LancamentosDocumentos.index'));

    }

    public function createArquivoDocumentos(ArquivoDocumentosCreateRequest $request)
    {


        $id = $request->documento_id;

        $arquivo_id_vinculo = $request->arquivo_id_vinculo;


        $Existe = DocumentosArquivoVinculo::where('arquivo_id_vinculo',$arquivo_id_vinculo)
        ->where('documento_id',$id)
        ->first();

        if($Existe){
            session(['error' => "ARQUIVO EXISTE:  "
            . $Existe->MostraLancamentoDocumento->Rotulo.  ' do tipo de arquivo: '
            . $Existe->MostraLancamentoDocumento->TipoArquivoNome->nome
            .",  já existe para este registro!"]);
            return redirect(route('LancamentosDocumentos.edit', $id));
        }

        $request['user_created'] = Auth ::user()->email;

        // dd($request->all());
        $model = $request->all();
        DocumentosArquivoVinculo::create($model);
        return redirect(route('LancamentosDocumentos.edit', $id));
    }

    public function DriveLocalFileUpload(Request $request)
    {
        $complemento = $request->complemento;

        if ($complemento == 'RETIRAR PONTOABCDEFG.') {
            $complemento_sem_pontos = str_replace('.', '', $complemento);

            session([
                'InformacaoArquivo' => 'O complemento possui  caracteres  com pontos. RETIRADO! QUALQUER DÚVIDA CONSULTE O ADMINISTRADOR DO SISTEMA. TEXTO:' . $complemento_sem_pontos,
            ]);
            return redirect(route('informacao.arquivos'));
        }

        $complemento_sem_pontos = str_replace('.', '', $complemento);
        $complemento = $complemento_sem_pontos;

        $quantidadeCaracteres = trim(strlen($complemento));
        if ($quantidadeCaracteres > 150) {
            session([
                'InformacaoArquivo' => 'O complemento possui ' . $quantidadeCaracteres . ' caracteres. Quantidade de caracteres maior que o permitido que é 150.',
            ]);
            return redirect(route('informacao.arquivos'));
        }


        dd($complemento);

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
                'InformacaoArquivo' => 'Pasta não informada! Verifique o arquivo de configuração env( FOLDER_DRIVE_GOOGLE ). Execute: # php artisan config:clear no SERVIDOR DOCKER LARAVEL',
            ]);
            return redirect(route('informacao.arquivos'));
        }
        $folderTemp = config('services.google_drive.folder');
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

        // $nome_arquivo_sem_pontos = str_replace('.', '', $nome_arquivo);


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
        $Documentos = LancamentoDocumento::create([
            'Rotulo' => $Complemento,
            'LancamentoID' => null,
            'Nome' => $result->getId(),
            'Created' => date('d-m-Y H:i:s'),
            'UsuarioID' => Auth::user()->id,
            'Ext' => explode('.', $result->getName())[1],
        ]);

        session([
            'InformacaoArquivo' => 'Arquivo enviado com sucesso. O ID do mesmo é ' . $result->id,
        ]);
               return redirect(route('informacao.arquivos'));
    }


}
