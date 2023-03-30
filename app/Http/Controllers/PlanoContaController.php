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
            ->select(["Lancamentos.ID","DataContabilidade","Lancamentos.Descricao","Lancamentos.EmpresaID","Lancamentos.Valor","Historicos.Descricao as DescricaoHistorico"])
            ->get();
            // dd($pesquisa->first());?

        session(['error' => '']);
        $retorno["DataInicial"] = date("Y-m-d");
        $retorno["DataFinal"] = date("Y-m-d");

        return view('PlanoContas.pesquisaavancada', compact('pesquisa',"retorno"));
    }

    public function pesquisaavancadapost(Request $Request)
    {
        $CompararDataInicial = $Request->DataInicial;

        $pesquisa = Lancamento::Limit($Request->Limite??100)
            ->join('Contabilidade.EmpresasUsuarios', 'Lancamentos.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
            ->Where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->select(["Lancamentos.ID","DataContabilidade","Lancamentos.Descricao","Lancamentos.EmpresaID","Contabilidade.Lancamentos.Valor","Historicos.Descricao as DescricaoHistorico"])
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

        $retorno = $Request->all();
        session(['error' => '']);
        if ($Request->DataInicial && $Request->DataFinal) {
            if ($DataInicial > $DataFinal) {
                session(['error' => 'Data de início MAIOR que a final. VERIFIQUE!']);
                return view('PlanoContas.pesquisaavancada', compact('pesquisa', 'retorno'));
            }
        }

        $pesquisa = $pesquisa->get();
        return view('PlanoContas.pesquisaavancada', compact('pesquisa', 'retorno'));
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

        return view('PlanoContas.edit', compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
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
