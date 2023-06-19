<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriasCreateRequest;
use App\Models\Categorias;
use App\Models\TipoEsporte;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class CategoriasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:CATEGORIAS - LISTAR'])->only('index');
        $this->middleware(['permission:CATEGORIAS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:CATEGORIAS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:CATEGORIAS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:CATEGORIAS - EXCLUIR'])->only('destroy');
    }

    public function index()
    {
       $model= Categorias::OrderBy('nome')->get();


        return view('Categorias.index',compact('model'));
    }


    public function create()
    {
        $retorno['TipoEsporte'] = null;
        $TipoEsporte = TipoEsporte::get();
        return view('Categorias.create', compact('TipoEsporte', 'retorno'));
    }


    public function store(CategoriasCreateRequest $request)
    {

        if($request->tipo_esporte === null){

            session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .", DEVE SER SELECIONADO! NADA INCLUÍDO! "]);
            return redirect(route('Posicoes.index'));
        }

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Categorias::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! "]);
            return redirect(route('Posicoes.index'));
        }

        $request['user_created'] = Auth::user()->email;
        $model = $request->all();


        Categorias::create($model);

        session(['success' => 'Categoria inserida com sucesso!']);

        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  ." INCLUÍDO COM SUCESSO!"]);
        return redirect(route('Categorias.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Categorias::find($id);
        return view('Categorias.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)

    {

        $model= Categorias::find($id);
        $retorno['TipoEsporte'] = $model->tipo_esporte;
        $TipoEsporte = TipoEsporte::get();

        // DD($model, $retorno['TipoEsporte']);
        return view('Categorias.edit',compact('model', 'TipoEsporte', 'retorno',));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {




        if($request->tipo_esporte === null){

            session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .", DEVE SER SELECIONADO! NADA FOI ALTERADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
            return  redirect(route('Categorias.edit', $id));
        }
        else
        if($request->nome === null || $request->nome === ''){

            session(['error' => "TIPO DE ESPORTE, DEVE SER PREENCHIDO. NÃO PODE SER VAZIO! NADA ALTERADO! "]);
             return  redirect(route('Categorias.edit', $id));
        }

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Categorias::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('Categorias.index'));
        }


        $cadastro = Categorias::find($id);

        $request['user_updated'] = Auth::user()->email;
        $cadastro->fill($request->all()) ;


        $cadastro->save();

        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  ." ALTERADO COM SUCESSO!"]);
        return  redirect(route('Categorias.edit', $id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        // $FormandoBasePosicao = FormandoBasePosicoes::where('posicao_id',$id)->first();

        // if($FormandoBasePosicao){
        //     session(['error' => "CATEGORIA:  ". $FormandoBasePosicao->MostraPosicao->nome  ." sendo usado!"]);
        //     return redirect(route('Categorias.index'));
        // }

        $model = Categorias::find($id);

        $model->delete();
        session(['success' => "CATEGORIA:  ". $model->nome  ." EXCLUÍDA COM SUCESSO!"]);
        return redirect(route('Categorias.index'));

    }
}
