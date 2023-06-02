<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoedaCreateRequest;
use App\Http\Requests\MoedaValoresCreateRequest;
use App\Models\LancamentoDocumento;
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


    public function index(string $id)
    {

        if($id){
            $documentos = LancamentoDocumento::Where('ID',$id)->get();
        }else
        {
            $documentos = LancamentoDocumento::Limit(100)->OrderBy('ID','DESC' )->get();
        }
 

        return view('LancamentosDocumentos.index',compact('documentos'));
    }

    public function pesquisaavancada(Request $Request)
    {
        $CompararDataInicial = $Request->DataInicial;

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
            $pesquisa->where('LancamentoID', null);
        }

        if($Request->SelecionarComContabilidade)
        {

            $pesquisa->where('LancamentoID', '>', 0);

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

        // dd($pesquisa->first()->ContaDebito->PlanoConta);
        return view('LancamentosDocumentos.index', compact('pesquisa', 'retorno','documentos'  ));
    }

    public function edit(string $id)
    {
        $documento = LancamentoDocumento::find($id);


        return view('LancamentosDocumentos.edit',compact('documento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $idEdit = $id;

        $cadastro = LancamentoDocumento::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect()->route('LancamentosDocumentosID.index', ['id' => $id]);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $moedas = Moeda::find($id);


        $moedas->delete();
        return redirect(route('LancamentosDocumentos.index'));

    }
}
