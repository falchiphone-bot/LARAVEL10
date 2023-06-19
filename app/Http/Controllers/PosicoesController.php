<?php

namespace App\Http\Controllers;


use App\Http\Requests\PosicoesCreateRequest;
use App\Models\FormandoBasePosicoes;
use App\Models\Posicoes;
use App\Models\TipoEsporte;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class PosicoesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:POSICOES - LISTAR'])->only('index');
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
       $model= Posicoes::OrderBy('nome')->get();


        return view('Posicoes.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $retorno['TipoEsporte'] = null;
        $TipoEsporte = TipoEsporte::get();
        return view('Posicoes.create', compact('TipoEsporte', 'retorno'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PosicoesCreateRequest $request)
    {

        if($request->tipo_esporte === null){

            session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .", DEVE SER SELECIONADO! NADA INCLUÍDO! "]);
            return redirect(route('Posicoes.index'));
        }

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Posicoes::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! "]);
            return redirect(route('Posicoes.index'));
        }

        $request['user_created'] = Auth::user()->email;
        $model = $request->all();


        Posicoes::create($model);

        session(['success' => 'Posição inserida com sucesso!']);

        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  ." INCLUÍDO COM SUCESSO!"]);
        return redirect(route('Posicoes.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Posicoes::find($id);
        return view('Posicoes.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)

    {

        $model= Posicoes::find($id);
        $retorno['TipoEsporte'] = $model->tipo_esporte;
        $TipoEsporte = TipoEsporte::get();

        // DD($model, $retorno['TipoEsporte']);
        return view('Posicoes.edit',compact('model', 'TipoEsporte', 'retorno',));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {




        if($request->tipo_esporte === null){

            session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .", DEVE SER SELECIONADO! NADA FOI ALTERADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
            return  redirect(route('Posicoes.edit', $id));
        }
        else
        if($request->nome === null || $request->nome === ''){

            session(['error' => "TIPO DE ESPORTE, DEVE SER PREENCHIDO. NÃO PODE SER VAZIO! NADA ALTERADO! "]);
             return  redirect(route('Posicoes.edit', $id));
        }

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Posicoes::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('Posicoes.index'));
        }


        $cadastro = Posicoes::find($id);

        $request['user_updated'] = Auth::user()->email;
        $cadastro->fill($request->all()) ;


        $cadastro->save();

        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  ." ALTERADO COM SUCESSO!"]);
        return  redirect(route('Posicoes.edit', $id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $FormandoBasePosicao = FormandoBasePosicoes::where('posicao_id',$id)->first();

        if($FormandoBasePosicao){
            session(['error' => "POSIÇÃO:  ". $FormandoBasePosicao->MostraPosicao->nome  ." sendo usado!"]);
            return redirect(route('Posicoes.index'));
        }
        
        $model = Posicoes::find($id);




        $model->delete();
        session(['success' => "TIPO DE ESPORTE:  ". $model->nome  ." EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('Posicoes.index'));

    }
}
