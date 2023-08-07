<?php

namespace App\Http\Controllers;

use App\Http\Requests\CargoProfissionalCreateRequest;
use App\Models\CargoProfissional;
use App\Models\Preparadores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CargoProfissionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:CARGOPROFISSIONAL - LISTAR'])->only('index');
        $this->middleware(['permission:CARGOPROFISSIONAL - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:CARGOPROFISSIONAL - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:CARGOPROFISSIONAL - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:CARGOPROFISSIONAL - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $model= CargoProfissional::OrderBy('nome')->get();


        return view('CargoProfissional.index',compact('model'));
    }

    public function create()
    {
        return view('CargoProfissional.create');
    }


    public function store(CargoProfissionalCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = CargoProfissional::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('CargoProfissional.index'));
        }


        $model= $request->all();


        CargoProfissional::create($model);
        session(['success' => "Cargo Profissional:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('CargoProfissional.index'));

    }


    public function show(string $id)
    {
        $cadastro = CargoProfissional::find($id);
        return view('CargoProfissional.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= CargoProfissional::find($id);


        return view('CargoProfissional.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = CargoProfissional::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
        //     return redirect(route('CargoProfissional.index'));
        // }


        $cadastro = CargoProfissional::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('CargoProfissional.index'));
    }

    public function destroy(Request $request, string $id)
    {

       $Achar = Preparadores::where('CargoProfissional', $id)->get();

       if($Achar->Count() > 0)
       {

        session(['error' => "CARGO PROFISSIONAL:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
        return redirect(route('CargoProfissional.index'));
       }


        $model= CargoProfissional::find($id);

        $model->delete();

       session(['success' => "CARGO PROFISSIONAL:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('CargoProfissional.index'));

    }
}
