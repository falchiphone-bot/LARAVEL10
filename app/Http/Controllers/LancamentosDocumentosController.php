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
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Strings;
use PHPUnit\Framework\Constraint\Count;
use Illuminate\Support\Facades\Storage;


class LancamentosDocumentosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['sites', 'documentosvideos' ,'alvaresflorence']);
        // $this->middleware('auth');
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
    $perPage = (int) request()->query('Limite', 25); // paginação padrão
    if ($perPage <= 0 || $perPage > 1000) { $perPage = 25; }
        $documentos = LancamentoDocumento::with('TipoArquivoNome')
            ->select(['ID','Rotulo','Created','ArquivoFisico','TipoArquivo','Nome','NomeLocalTimeStamps','Ext','LancamentoID','Publico'])
            ->orderBy('ID','DESC')
            ->paginate($perPage)
            ->withQueryString();
        $tipoarquivo = Cache::remember('tipoarquivo_all', 600, fn() => TipoArquivo::orderBy('nome')->get());
        $retorno['TipoArquivo'] = null;


        return view('LancamentosDocumentos.index',compact('documentos','tipoarquivo','retorno'));
    }

    public function sites()
    {

    //     \Illuminate\Support\Facades\Auth::logout(); // Garante que não está logado
    // return "Acesso liberado sem login!"; // Teste simples


        // dd(request()->route()->gatherMiddleware());


        $dominio = request()->getHost(); // Obtém o domínio atual

        $url = request()->url();

        $documentos = LancamentoDocumento::limit(100)
        ->where('Publico', 1)
        ->whereNotNull('AnotacoesGerais')
        ->where('AnotacoesGerais', 'LIKE', '%'.$dominio. '%')
        ->orderBy('ID', 'DESC')
        ->get();


        $tipoarquivo = TipoArquivo::get();
        $retorno['TipoArquivo'] = null;

        // dd($url);

        if ($dominio === 'tanabisaf.com.br') {
                 return view('tanabisaf.documentos',compact('documentos','tipoarquivo','retorno'));
        } elseif ($dominio === 'vec.org.br') {

            return view('vec.documentos',compact('documentos','tipoarquivo','retorno'));
        }

    }


    public function documentosvideos()
    {

        $dominio = request()->getHost(); // Obtém o domínio atual
        $url = request()->url();

        $documentos = LancamentoDocumento::limit(100)
        ->where('Publico', 1)
        ->whereNotNull('AnotacoesGerais')
        ->where('AnotacoesGerais', 'LIKE', '%'.$url. '%')
        ->orderBy('ID', 'DESC')
        ->get();


        $tipoarquivo = TipoArquivo::get();
        $retorno['TipoArquivo'] = null;

        // dd($url);

        if ($url === 'http://tanabisaf.com.br/documentosvideos') {
                 return view('tanabisaf.documentos',compact('documentos','tipoarquivo','retorno'));
        } elseif ($url === 'http://vec.org.br/documentosvideos') {

            return view('vec.documentos',compact('documentos','tipoarquivo','retorno'));
        }

    }

    public function alvaresflorence()
    {
        // $dominio = request()->getHost(); // Obtém o domínio atual

        $url = request()->url();

        $documentos = LancamentoDocumento::limit(100)
        ->where('Publico', 1)
        ->whereNotNull('AnotacoesGerais')
        ->where('AnotacoesGerais', 'LIKE', '%'.$url. '%')
        ->orderBy('ID', 'DESC')
        ->get();


        $tipoarquivo = TipoArquivo::get();
        $retorno['TipoArquivo'] = null;

        // dd($url);

        if ($url === 'http://tanabisaf.com.br/alvaresflorence') {
                 return view('tanabisaf.documentos',compact('documentos','tipoarquivo','retorno'));
        } elseif ($url === 'http://vec.org.br/alvaresflorence') {

            return view('vec.documentos',compact('documentos','tipoarquivo','retorno'));
        }

    }



    public function indexpost(string $id)
    {

        if($id){
            $documentos = LancamentoDocumento::with('TipoArquivoNome')
                ->select(['ID','Rotulo','Created','ArquivoFisico','TipoArquivo','Nome','NomeLocalTimeStamps','Ext','LancamentoID','Publico'])
                ->where('ID',$id)
                ->get();
        }else
        {
            $documentos = LancamentoDocumento::with('TipoArquivoNome')
                ->select(['ID','Rotulo','Created','ArquivoFisico','TipoArquivo','Nome','NomeLocalTimeStamps','Ext','LancamentoID','Publico'])
                ->limit(100)
                ->orderBy('ID','DESC')
                ->get();
        }
        $tipoarquivo = Cache::remember('tipoarquivo_all', 600, fn() => TipoArquivo::orderBy('nome')->get());
        $retorno['TipoArquivo'] = null;
        return view('LancamentosDocumentos.index',compact('documentos','tipoarquivo','retorno'));
    }

    public function pesquisaavancada(Request $Request)
    {
        $CompararDataInicial = $Request->DataInicial;
        $tipoarquivo = Cache::remember('tipoarquivo_all', 600, fn() => TipoArquivo::orderBy('nome')->get());
        $limit = (int)($Request->Limite ?? 25); // usa como itens por página
        if ($limit <= 0 || $limit > 1000) { $limit = 25; }
        $pesquisa =  LancamentoDocumento::with('TipoArquivoNome')
            ->select(['ID','Rotulo','Created','ArquivoFisico','TipoArquivo','Nome','NomeLocalTimeStamps','Ext','LancamentoID','Publico']);

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

    $documentos = $pesquisa->paginate($limit)->appends($Request->all());

        $retorno['TipoArquivo'] = $Request->SelecionarTipoArquivo;
        // dd($pesquisa->first()->ContaDebito->PlanoConta);
        return view('LancamentosDocumentos.index', compact('pesquisa', 'retorno','documentos','tipoarquivo'    ));
    }

    public function edit(string $id)
    {
        $documento = LancamentoDocumento::with('TipoArquivoNome')->find($id);



        $documentoListado = LancamentoDocumento::with('TipoArquivoNome')
        ->where('TipoArquivo', '>', 0)
        ->where('ID', '!=', $id)
        ->orderBy('ID', 'desc')
        ->get();


        $retorno['TipoArquivo'] = $documento->TipoArquivo ?? null;
        $tipoarquivo = TipoArquivo::OrderBy('nome')->get();


       $DocumentoArquivo = DocumentosArquivoVinculo::with(['MostraLancamentoDocumento','MostraLancamentoDocumento.TipoArquivoNome'])
           ->where('documento_id','=', $id)
           ->orWhere('arquivo_id_vinculo','=', $id)
           ->orderBy('id')
           ->get();
       $arquivoExiste = $DocumentoArquivo->isNotEmpty() ? optional($DocumentoArquivo->last())->id : null;




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
            $cadastro->NomeLocalTimeStamps = $currentTimestamp;
        }


        $cadastro->fill($request->all()) ;

        $cadastro->Publico = $request->Publico ?? 0;

        // DD($cadastro);


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
        $timestamps = time();
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



        /////// aqui fica na pasta temporário /temp/    - apaga
        $path = $request->file('arquivo')->getRealPath();

        $file = $request->file('arquivo');
        $Complemento = $request->complemento;
        $name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

// DD($file, $Complemento, $name, $extension);



        // $nome_arquivo = Carbon::now() . '-' . $request->file('arquivo')->getClientOriginalName();


        // $Documentos = LancamentoDocumento::create([
        //     'Rotulo' => $Complemento,
        //     'LancamentoID' => null,
        //     'Nome' => time(),
        //     'Created' => date('d-m-Y H:i:s'),
        //     'UsuarioID' => Auth::user()->id,
        //     'Ext' => explode('.',  $extension)[1],
        // ]);

        $Documentos = new LancamentoDocumento([
            'Rotulo' => $Complemento,
            'LancamentoID' => null,
            'NomeLocalTimeStamps' => $timestamps,
            'Created' => date('Y-m-d H:i:s'), // Alterado para o formato de data mais comum
            'UsuarioID' => Auth::user()->id,
            'Ext' => $extension,
        ]);

        $Documentos->save();



            // Validar a existência do arquivo na requisição
            $request->validate([
                'arquivo' => 'required|file', // ajuste as validações conforme necessário
            ]);

            // Gerar o nome do arquivo com o timestamp e o nome original
            $nomeArquivo = $timestamps.'.'.$extension;

            // Salvar o arquivo na pasta storage/arquivos
            $caminhoArquivo = $request->file('arquivo')->storeAs('arquivos', $nomeArquivo);

            // Retornar o caminho completo ou qualquer resposta necessária
            // return response()->json(['caminho' => Storage::url($caminhoArquivo)]);





        session([
            'InformacaoArquivo' => 'Arquivo enviado com sucesso. O ID do mesmo é ' .  $timestamps,
        ]);
               return redirect(route('informacao.arquivos'));
    }


}
