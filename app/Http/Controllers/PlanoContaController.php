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
            ->Where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->orderBy('Lancamentos.ID', 'desc')
            ->get();
        // $retorno[0] = "";
        return view('PlanoContas.pesquisaavancada', compact('pesquisa'));
    }

    public function pesquisaavancadapost(Request $Request)
    {
        $CompararDataInicial = $Request->DataInicial;
        $DataInicial = Carbon::createFromFormat('Y-m-d', $Request->DataInicial)->format('d/m/Y');
        $CompararDataFinal = $Request->DataFinal;
        $DataFinal = Carbon::createFromFormat('Y-m-d', $Request->DataFinal)->format('d/m/Y');

        $retorno = $Request->all();
        $DataInicialFinal = $Request->DataInicialFinal;

        // $validator = Validator::make($Request->all(), [
        //     'DataConvertidaDataApos' => 'required|date',
        //     'DataConvertidaDataAntes' => 'required|date|after_or_equal:DataConvertidaDataApos',
        // ]);

        // $retorno[0]= null;

        if ($CompararDataInicial > $CompararDataFinal) {
            $pesquisa = Lancamento::Limit(1)
                ->orderBy('Lancamentos.ID', 'desc')
                ->get();
            // return back()->with('status', "Data de início menor que a final. VERIFIQUE!");

            $DataInicialFinal = 'Data de início MAIOR que a final. VERIFIQUE!';
            $retorno[0] = $DataInicialFinal;

            return view('PlanoContas.pesquisaavancada', compact('pesquisa', 'retorno'))->with('success', 'Data de início MAIOR que a final. VERIFIQUE!');;
            // ->with('error', 'Data de início menor que a final. VERIFIQUE!');
        }

        $pesquisa = Lancamento::Limit($Request->Limite)
            ->join('Contabilidade.EmpresasUsuarios', 'Lancamentos.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->Where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->orderBy('Lancamentos.ID', 'desc');

        if ($Request->Texto) {
            $pesquisa->where('Descricao', 'like', '%' . $Request->Texto . '%');
        }
        if ($Request->Valor) {
            $pesquisa->where('Valor', '=', $Request->Valor);
        }
        if ($Request->DataInicial) {
            $pesquisa->where('DataContabilidade', '>=', $DataInicial);
        }
        if ($Request->DataFinal) {
            $pesquisa->where('DataContabilidade', '<=', $DataFinal);
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
