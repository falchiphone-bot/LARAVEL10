<?php

namespace App\Http\Controllers;

use App\Models\FormandoBaseArquivo;
use App\Models\FormandoBasePosicoes;

use Illuminate\Http\Request;



class FormandoBaseArquivosController  extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FORMANDOBASEARQUIVOS - LISTAR'])->only('index');
        $this->middleware(['permission:FORMANDOBASEARQUIVOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:FORMANDOBASEARQUIVOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:FORMANDOBASEARQUIVOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:FORMANDOBASEARQUIVOS - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
    //    $model= RedeSocial::OrderBy('nome')->get();


    //     return view('RedeSocial.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('RedeSocial.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RedeSocialCreateRequest $request)
    {
        // $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = RedeSocial::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
        //     return redirect(route('RedeSocial.index'));
        // }

        // $request['user_created'] = Auth::user()->email;

        // $model= $request->all();


        // RedeSocial::create($model);
        // session(['success' => "TIPO DE ESPORTE:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        // return redirect(route('RedeSocial.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $cadastro = RedeSocial::find($id);
        // return view('RedeSocial.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // $model= RedeSocial::find($id);


        // return view('RedeSocial.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = RedeSocial::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
        //     return redirect(route('RedeSocial.index'));
        // }


        // $cadastro = RedeSocial::find($id);


        // $request['user_updated'] = Auth::user()->email;
        // $cadastro->fill($request->all()) ;


        // $cadastro->save();


        // return redirect(route('RedeSocial.index'));
    }


    public function destroy(Request $request, string $id)
    {

        $model= FormandoBaseArquivo::find($id);
        
        $model->delete();

        session(['success' => "Referência ao arquivo:  ". $model->MostraLancamentoDocumento->Rotulo  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('FormandoBase.index'));

    }
}
