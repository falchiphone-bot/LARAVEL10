<?php

namespace App\Http\Controllers;

use App\Models\FormandoBase;
use App\Models\FormandoBasePosicoes;
use App\Models\RecebimentoFormandoBase;
use App\Models\Representantes;
use Illuminate\Http\Request;



class FormandoBaseRecebimentosController  extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FORMANDOBASERECEBIMENTOS - LISTAR'])->only('index');
        $this->middleware(['permission:FORMANDOBASERECEBIMENTOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:FORMANDOBASERECEBIMENTOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:FORMANDOBASERECEBIMENTOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:FORMANDOBASERECEBIMENTOS - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $model= RecebimentoFormandoBase::OrderBy('created_at')->get();


        return view('FormandoBaseRecebimentos.index',compact('model'));
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

    public function show(string $id)
    {
        // $cadastro = RedeSocial::find($id);
        // return view('RedeSocial.show',compact('cadastro'));
    }


    public function edit(string $id)
    {
        $representante = Representantes::orderBy('nome')->get();
        $formandosbase = FormandoBase::orderBy('nome')->get();



        $model= RecebimentoFormandoBase::find($id);
        $retorno['formandobase'] = $model->formandobase_id;
        $retorno['representante'] = $model->representante_id;

        return view('FormandoBaseRecebimentos.edit',compact('model','representante','formandosbase','retorno'));
    }


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

        $model= RecebimentoFormandoBase::find($id);

        $model->delete();

        session(['success' => "RECEBIMENTOS/REPRESENTANTES:  ". $model->MostraRepresentante->nome  .",  EXCLUÍDA COM SUCESSO!"]);
        return redirect(route('FormandoBase.index'));

    }
}
