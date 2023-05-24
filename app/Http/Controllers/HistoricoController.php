<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoricoCreateRequest;
use App\Http\Requests\HistoricosCreateRequest;
use App\Models\Empresa;
use App\Models\Historico;
use App\Models\Historicos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HistoricoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:HISTORICOS - LISTAR'])->only('index');
        $this->middleware(['permission:HISTORICOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:HISTORICOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:HISTORICOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:HISTORICOS - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

              return view('Historicos.index', compact('Empresas'));
    }

    public function pesquisapost(Request $Request)
    {
        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        $pesquisa = Historicos::where('EmpresaID', $Request->EmpresaSelecionada)->where('Descricao','like', "%".$Request->PesquisaTexto."%")->get();

        $retorno["EmpresaSelecionada"] = $Request->EmpresaSelecionada;
        $retorno["PesquisaTexto"] = $Request->PesquisaTexto;

        return view('Historicos.pesquisapost', compact('pesquisa','Empresas',"retorno"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Historicos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HistoricosCreateRequest $request)
    {
        $request['UsuarioID'] = auth()->user()->id;
        $request['Descricao'] = trim($request->Descricao);
        $Historicos = $request->all();

        Historicos::create($Historicos);

        return redirect(route('Historicos.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Historicos::find($id);
        return view('Historicos.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('Historicos.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cadastro = Historicos::find($id);

        $request['UsuarioID'] = auth()->user()->id;
        $request['Descricao'] = trim($request->Descricao);
        $cadastro->fill($request->all());

        $cadastro->save();

        return redirect(route('Historicos.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Historicos = Historicos::find($id);

        $Historicos->delete();
        return redirect(route('Historicos.index'));
    }
}
