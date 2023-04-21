<?php

namespace App\Http\Controllers;

use App\Http\Requests\LancamentoResquest;
use App\Models\Lancamento;

class LancamentosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ultimos_lancamentos = [];
        if (session('Empresa')) {
            $ultimos_lancamentos = Lancamento::where('EmpresaID',session('Empresa')->ID)->limit(10)->orderBy('ID','DESC')->get();
        }
        return view('Lancamentos.index',compact('ultimos_lancamentos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Lancamentos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LancamentoResquest $request)
    {
        $lancamento = $request->all();
        Lancamento::created($lancamento);
        return redirect(route('Lancamentos.index'))->with('success','Lançamento Criado.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $lancamento = Lancamento::find($id);
        if (empty($lancamento)) {
            return redirect(route('Lancamentos.index'))->with('error','Lançamento não encontrado');
        }
        return view('Lancamentos.show',compact('lancamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $lancamento = Lancamento::find($id);
        if (empty($lancamento)) {
            return redirect(route('Lancamentos.index'))->with('error','Lançamento não encontrado');
        }
        return view('Lancamentos.edit',compact('lancamento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LancamentoResquest $request, $id)
    {
        $lancamento = Lancamento::find($id);
        if (empty($lancamento)) {
            return redirect(route('Lancamentos.index'))->with('error','Lançamento não encontrado');
        }
        $lancamento->fill($request->all());
        $lancamento->save();
        return redirect(route('Lancamentos.index'))->with('success','Lançamento atualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $lancamento = Lancamento::find($id);
        if (empty($lancamento)) {
            return redirect(route('Lancamentos.index'))->with('error','Lançamento não encontrado');
        }
        $lancamento->destroy();
        return redirect(route('Lancamentos.index'));
    }
}
