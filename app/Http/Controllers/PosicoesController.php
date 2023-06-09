<?php

namespace App\Http\Controllers;


use App\Http\Requests\PosicoesCreateRequest;
use App\Models\Posicoes;
use App\Models\TipoEsporte;

use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $TipoEsporte = TipoEsporte::get();
        return view('Posicoes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PosicoesCreateRequest $request)
    {
        $model= $request->all();


        Posicoes::create($model);

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
        $retorno['TipoEsporte'] = null;
        $model= Posicoes::find($id);
        $TipoEsporte = TipoEsporte::get();
        return view('Posicoes.edit',compact('model', 'TipoEsporte', 'retorno',));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Posicoes::find($id);

// dd($request->all());

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('Posicoes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model= Posicoes::find($id);


        $model->delete();
        return redirect(route('Posicoes.index'));

    }
}
