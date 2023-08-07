<?php

namespace App\Http\Controllers;

use App\Http\Requests\FuncaoProfissionalCreateRequest;
use App\Models\FuncaoProfissional;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class FuncaoProfissionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FUNCAOPROFISSIONAL - LISTAR'])->only('index');
        $this->middleware(['permission:FUNCAOPROFISSIONAL - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:FUNCAOPROFISSIONAL - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:FUNCAOPROFISSIONAL - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:FUNCAOPROFISSIONAL - EXCLUIR'])->only('destroy');
    }

    public function index()
    {
       $model= FuncaoProfissional::OrderBy('nome')->get();


        return view('FuncaoProfissional.index',compact('model'));
    }


    public function create()
    {
        return view('FuncaoProfissional.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FuncaoProfissionalCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = FuncaoProfissional::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('FuncaoProfissional.index'));
        }


        $model= $request->all();


        FuncaoProfissional::create($model);
        session(['success' => "FUNÇÃO PROFISSIONAL:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('FuncaoProfissional.index'));

    }


    public function show(string $id)
    {
        $cadastro = FuncaoProfissional::find($id);
        return view('FuncaoProfissional.show',compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model= FuncaoProfissional::find($id);


        return view('FuncaoProfissional.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = FuncaoProfissional::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
        //     return redirect(route('FuncaoProfissional.index'));
        // }


        $cadastro = FuncaoProfissional::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('FuncaoProfissional.index'));
    }


    public function destroy(Request $request, string $id)
    {

       $Achar = FuncaoProfissional::where('FuncaoProfissional', $id)->get();

       if($Achar->Count() > 0)
       {

        session(['error' => "FUNÇÃO PROFISSIONAL:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
        return redirect(route('FuncaoProfissional.index'));
       }


        $model= FuncaoProfissional::find($id);

        $model->delete();

       session(['success' => "FUNÇÃO PROFISSIONAL:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('FuncaoProfissional.index'));

    }
}
