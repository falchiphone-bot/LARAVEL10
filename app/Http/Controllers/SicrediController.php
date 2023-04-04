<?php

namespace App\Http\Controllers;

use App\Helpers\SicredApiHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;


class SicrediController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dia = Carbon::now()->subDay(1)->format('d/m/Y');
        $consulta = SicredApiHelper::boletoLiquidadoDia('72334','703','16',$dia);
        if ($consulta['error']??null) {
            return $consulta['message'];
        }

        return view('Sicredi.index',compact('consulta','dia'));

    }

    public function salvarLiquidacaoDia($dia)
    {
        $lista = SicredApiHelper::boletoLiquidadoDia('72334','0703','16','03/04/2023');
        dd(count($lista));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Testes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TesteCreateRequest $request)
    {


        $dados = $request->all();
        //dd($dados);

        Teste::create($dados);

        return redirect(route('Testes.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Teste::find($id);

        return view('Testes.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = Teste::find($id);
        // dd($cadastro);

        return view('Testes.edit', compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Teste::find($id);

        $cadastro->fill($request->all());
        //dd($dados);

        $cadastro->save();

        return redirect(route('Testes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cadastro = Teste::find($id);
        $cadastro->delete();
        return redirect(route('Testes.index'));
    }
}
