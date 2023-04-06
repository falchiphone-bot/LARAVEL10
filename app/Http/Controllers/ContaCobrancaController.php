<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContaCobrancaRequest;
use App\Models\ContaCobranca;
use App\Models\DevSicredi;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContaCobrancaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $contasCobrancas = ContaCobranca::get();
        // $contasCobrancas = ContaCobranca::where('EmpresaID',session('Empresa')->ID)->get();
        return view('ContasCobranca.index', compact('contasCobrancas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $empresas = Empresa::orderBy('Descricao')->pluck('Descricao','ID');
        $contasDev = DevSicredi::orderBy('DESENVOLVEDOR')->pluck('DESENVOLVEDOR','id');
        return view('ContasCobranca.create',compact('empresas','contasDev'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContaCobrancaRequest $request)
    {
        $dados = $request->all();

        ContaCobranca::create($dados);

        return redirect(route('ContasCobranca.index'))->with('Cadastrado com sucesso');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $conta = ContaCobranca::find($id);

        return view('ContasCobranca.show', compact('conta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contaCobranca = ContaCobranca::find($id);

        return view('ContasCobranca.edit', compact('contaCobranca'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContaCobrancaRequest $request, string $id)
    {

        $conta = ContaCobranca::find($id);

        $conta->fill($request->all());
        //dd($dados);

        $conta->save();

        return redirect(route('ContasCobranca.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $conta = ContaCobranca::find($id);
        $conta->delete();
        return redirect(route('ContasCobranca.index'));
    }
}
