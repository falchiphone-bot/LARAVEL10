<?php

namespace App\Http\Controllers;

use App\Models\Conta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function extrato($contaID)
    {
        return view('Contas.extrato')->with(['contaID'=>$contaID]);
    }

    public function index()
    {
        $contas = Conta::where('EmpresaID',session('Empresa')->ID)->get();
        return view('Contas.index', compact('contas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Contas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $dados = $request->all();
        //dd($dados);

        Conta::create($dados);

        return redirect(route('Contas.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if ($id == 'Extrato') {
            return redirect('/PlanoContas/dashboard')->with('error','Nenhuma conta foi selecionada');
        }
        $conta = Conta::find($id);

        return view('Contas.show', compact('conta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $conta = Conta::find($id);
        // dd($conta);

        return view('Contas.edit', compact('conta'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $conta = Conta::find($id);

        $conta->fill($request->all());
        //dd($dados);

        $conta->save();

        return redirect(route('Contas.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $conta = Conta::find($id);
        $conta->delete();
        return redirect(route('Contas.index'));
    }
}
