<?php

namespace App\Http\Controllers;

use App\Models\Representante;
use App\Models\TipoRepresentante;
use App\Http\Requests\TipoRepresentanteCreateRequest;
use App\Models\Representantes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class TipoRepresentanteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:TIPOREPRESENTANTE - LISTAR'])->only('index');
        $this->middleware(['permission:TIPOREPRESENTANTE - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:TIPOREPRESENTANTE - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:TIPOREPRESENTANTE - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:TIPOREPRESENTANTE - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $model= TipoRepresentante::OrderBy('nome')->get();


        return view('TipoRepresentante.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
           return view('TipoRepresentante.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipoRepresentanteCreateRequest $request)
    {


        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoRepresentante::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('TipoRepresentante.index'));
        }


        $model= $request->all();


        TipoRepresentante::create($model);
        session(['success' => "TIPO DE REPRESENTANTE:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoRepresentantes.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = TipoRepresentante::find($id);
        return view('TipoRepresentante.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= TipoRepresentante::find($id);


        return view('TipoRepresentante.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Representantes::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('TipoRepresentantes.index'));
        }


        $cadastro = TipoRepresentante::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('TipoRepresentantes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

       $Representante = Representantes::where('tipo_representante', $id)->get();

       if($Representante->Count() > 0)
       {

        session(['error' => "TIPO DE REPRESENTANTE  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
        return redirect(route('TipoEsporte.index'));
       }


        $model= TipoRepresentante::find($id);

        $model->delete();

       session(['success' => "TIPO DE REPRESENTANTES:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoRepresentantes.index'));

    }
}
