<?php

namespace App\Http\Controllers;


use App\Http\Requests\PosicoesCreateRequest;
use App\Http\Requests\TipoEsporteCreateRequest;
use App\Models\Posicoes;
use App\Models\TipoEsporte;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class TipoEsporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:TIPOESPORTE - LISTAR'])->only('index');
        $this->middleware(['permission:POSICOES - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:POSICOES - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:POSICOES - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:POSICOES - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $model= TipoEsporte::OrderBy('nome')->get();


        return view('TipoEsporte.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('TipoEsporte.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipoEsporteCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoEsporte::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('TipoEsporte.index'));
        }


        $model= $request->all();


        TipoEsporte::create($model);
        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoEsporte.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = TipoEsporte::find($id);
        return view('TipoEsporte.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= TipoEsporte::find($id);


        return view('TipoEsporte.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoEsporte::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('TipoEsporte.index'));
        }


        $cadastro = TipoEsporte::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('TipoEsporte.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

       $Posicao = Posicoes::where('tipo_esporte', $id)->get();

       if($Posicao->Count() > 0)
       {

        session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
        return redirect(route('TipoEsporte.index'));
       }


        $model= TipoEsporte::find($id);

        $model->delete();

       session(['success' => "TIPO DE ESPORTE:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoEsporte.index'));

    }
}
