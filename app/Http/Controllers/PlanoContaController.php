<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanoContasCreateRequest;
use App\Models\Conta;
use App\Models\PlanoConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PlanoContaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:PLANO DE CONTAS - LISTAR'])->only('index');
        $this->middleware(['permission:PLANO DE CONTAS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:PLANO DE CONTAS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:PLANO DE CONTAS - EXCLUIR'])->only('destroy');
    }
    /**public function __construct()
    {
        $this->middleware('auth');
    }*/

    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        if (!session('Empresa')) {
            return redirect('/Empresas')->with('error','Necesserário selecionar uma empresa');
        }else{
            $contasEmpresa = Conta::where('EmpresaID',session('Empresa')->ID)
            ->join('Contabilidade.PlanoContas','PlanoContas.ID','=','Contas.planocontas_id')
            ->orderBy('Codigo', 'asc')
            ->get(['Descricao','Codigo','Grau']);
            // dd($contasEmpresa->first());

            return view('PlanoContas.contas',compact('contasEmpresa'));
        }
    }


    public function index()
    {
        $cadastros = PlanoConta::orderBy('codigo', 'asc')->get();
        //$cadastros = DB::table('PlanoConta')->get();        $num_rows = count($cadastros);

        $linhas = count($cadastros);

        //  return view('PlanoContas.index',compact('cadastros'));

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
