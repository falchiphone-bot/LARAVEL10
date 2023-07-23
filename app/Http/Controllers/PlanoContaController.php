<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanoContasCreateRequest;
use App\Models\Conta;
use App\Models\Empresa;
use App\Models\EmpresaUsuario;
use App\Models\Lancamento;
use App\Models\PlanoConta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Strings;
use PHPUnit\Framework\Constraint\Count;

class PlanoContaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:PLANO DE CONTAS - LISTAR'])->only('index');
        $this->middleware(['permission:PLANO DE CONTAS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:PLANO DE CONTAS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:PLANO DE CONTAS - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:PESQUISA AVANCADA'])->only('pesquisaavancada');
    }
    /**public function __construct()
    {
        $this->middleware('auth');
    }*/

    /**
     * Display a listing of the resource.
     */
    public function pesquisaavancada()
    {
        $pesquisa = Lancamento::Limit(100)
            ->join('Contabilidade.EmpresasUsuarios', 'Lancamentos.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
            ->Where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->orderBy('Lancamentos.ID', 'desc')
            ->select(['Lancamentos.ID', 'DataContabilidade', 'Lancamentos.Descricao', 'Lancamentos.EmpresaID', 'Lancamentos.Valor', 'Historicos.Descricao as DescricaoHistorico', 'Lancamentos.ContaDebitoID', 'Lancamentos.ContaCreditoID'])
            ->get();
        // dd($pesquisa->first());?

        if ($pesquisa->count() > 0) {
            session(['entrada' => 'A pesquisa abaixo mostra os 100 últimos lançamentos de todas as empresas autorizadas!']);
            session(['success' => '']);
            session(['error' => '']);
        }
        else
        {
            session(['error' => 'Nenhum lançamento encontrado para as empresas autorizadas!']);
        }

        $retorno['DataInicial'] = date('Y-m-d');
        $retorno['DataFinal'] = date('Y-m-d');
        $retorno['EmpresaSelecionada'] = null;

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        return view('PlanoContas.pesquisaavancada', compact('pesquisa', 'retorno', 'Empresas'));
    }

    public function pesquisaavancadapost(Request $Request)
    {
        $CompararDataInicial = $Request->DataInicial;

        $pesquisa = Lancamento::Limit($Request->Limite ?? 100)
            ->join('Contabilidade.EmpresasUsuarios', 'Lancamentos.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
            ->Where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->select(['Lancamentos.ID', 'DataContabilidade', 'Lancamentos.Descricao', 'Lancamentos.EmpresaID', 'Contabilidade.Lancamentos.Valor', 'Historicos.Descricao as DescricaoHistorico', 'Lancamentos.ContaDebitoID', 'Lancamentos.ContaCreditoID'])
            ->orderBy('Lancamentos.ID', 'desc');

        if ($Request->Texto) {
            $texto = $Request->Texto;
            $pesquisa->where(function ($query) use ($texto) {
                return $query->where('Lancamentos.Descricao', 'like', '%' . $texto . '%')->orWhere('Historicos.Descricao', 'like', '%' . $texto . '%');
            });
        }

        if ($Request->Valor) {
            $pesquisa->where('Lancamentos.Valor', '=', $Request->Valor);
        }

        if ($Request->DataInicial) {
            $DataInicial = Carbon::createFromFormat('Y-m-d', $Request->DataInicial);
            $pesquisa->where('DataContabilidade', '>=', $DataInicial->format('d/m/Y'));
        }

        if ($Request->DataFinal) {
            $DataFinal = Carbon::createFromFormat('Y-m-d', $Request->DataFinal);
            $pesquisa->where('DataContabilidade', '<=', $DataFinal->format('d/m/Y'));
        }

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        $retorno = $Request->all();

        if ($pesquisa->count() > 0) {
            session(['success' => 'A pesquisa abaixo mostra os lançamentos de todas as empresas autorizadas conforme a pesquisa proposta!']);
        }
        else
        {
            session(['error' => 'Nenhum lançamento encontrado para as empresas autorizadas!']);
        }

        if ($Request->DataInicial && $Request->DataFinal) {
            if ($DataInicial > $DataFinal) {
                session(['error' => 'Data de início MAIOR que a final. VERIFIQUE!']);
                return view('PlanoContas.pesquisaavancada', compact('pesquisa', 'retorno', 'Empresas'));
            }
        }

        if ($Request->EmpresaSelecionada) {
            $pesquisa->where('Lancamentos.EmpresaID', $Request->EmpresaSelecionada);
        }

        $pesquisa = $pesquisa->get();

        // dd($pesquisa->first()->ContaDebito->PlanoConta);
        return view('PlanoContas.pesquisaavancada', compact('pesquisa', 'retorno', 'Empresas'));
    }

    public function dashboard()
    {
        if (!session('Empresa')) {
            return redirect('/Empresas')->with('error', 'Necessário selecionar uma empresa');
        } else {
            $contasEmpresa = Conta::where('EmpresaID', session('Empresa')->ID)
                ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', '=', 'Contas.planocontas_id')
                ->orderBy('Codigo', 'asc')
                ->get(['Contas.ID', 'Descricao', 'Codigo', 'Grau']);
            // dd($contasEmpresa->first());

            return view('PlanoContas.dashboard', compact('contasEmpresa'));
        }
    }

    public function index()
    {
        $cadastros = PlanoConta::orderBy('codigo', 'asc')->get();
        //$cadastros = DB::table('PlanoConta')->get();        $num_rows = count($cadastros);

        $linhas = count($cadastros);

        return view('PlanoContas.index', compact('cadastros', 'linhas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('PlanoContas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlanoContasCreateRequest $request)
    {


        $dados = $request->all();

   $dados['Created'] = Carbon::now()->format('d/m/Y H:i:s');
   $dados['Modified'] = Carbon::now()->format('d/m/Y H:i:s');
        $dados['UsuarioID'] = auth()->user()->id;

     //dd($dados);

        PlanoConta::create($dados);
        return redirect(route('PlanoContas.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Empresa::find($id);

        return view('PlanoContas.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = PlanoConta::find($id);
        // dd($cadastro);

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
        ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        ->OrderBy('Descricao')
        ->select(['Empresas.ID', 'Empresas.Descricao'])
        ->get();



        return view('PlanoContas.edit', compact('cadastro','Empresas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $Empresa = $request->EmpresaSelecionada;
        $Descricao = Empresa::find($Empresa)->Descricao;
        $Registro = $id;
        if($Empresa){

            // dd('Empresa: '.$request->EmpresaSelecionada);

            $Conta = Conta::where('EmpresaID','=', $Empresa)
            ->where('Planocontas_id', '=' ,$id)->first();

            if($Conta){
                session(['error' => 'A conta já existe para a empresa: '. $Descricao .'!']);


                return redirect(route('PlanoContas.edit', $Registro));
            }
        }

        $cadastro = PlanoConta::find($id);

        $cadastro->fill($request->all());
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());



        return redirect(route('PlanoContas.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cadastro = PlanoConta::find($id);
        $cadastro->delete();
        return redirect(route('PlanoContas.index'));
    }
}
