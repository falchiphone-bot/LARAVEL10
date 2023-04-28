<?php

namespace App\Http\Controllers;

use App\Http\Requests\LancamentoResquest;
use App\Models\Empresa;
use App\Models\Historicos;
use App\Models\Lancamento;
use App\Models\LancamentoDocumento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LancamentosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $ultimos_lancamentos = [];
        // if (session('Empresa')) {
        //     $ultimos_lancamentos = Lancamento::where('EmpresaID',session('Empresa')->ID)->limit(10)->orderBy('ID','DESC')->get();
        // }


        // return view('Lancamentos.index',compact('ultimos_lancamentos'));
        return redirect(route('planocontas.pesquisaavancada'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

            $historicos = Historicos::orderBy('Descricao')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->select(['Historicos.ID', 'Historicos.Descricao']);
            //  ->pluck('Descricao','ID');


        return view('Lancamentos.create', compact('Empresas','historicos'));
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

    public function baixarArquivo($id)
    {
        $download = LancamentoDocumento::find($id);
        if ($download) {
            return Storage::disk('ftp')->download($download->Nome.'.'.$download->Ext);
        }else {
            $this->addError('download','Arquivo não localizado para baixar.');
        }
    }
}
