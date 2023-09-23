<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgrupamentosContasCreateRequest;
use App\Models\AgrupamentosContas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AgrupamentosContasController  extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:AGRUPAMENTOS CONTAS - LISTAR'])->only('index');
        $this->middleware(['permission:AGRUPAMENTOS CONTAS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:AGRUPAMENTOS CONTAS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:AGRUPAMENTOS CONTAS - VER'])->only(['show', 'update']);
        $this->middleware(['permission:AGRUPAMENTOS CONTAS - EXCLUIR'])->only('destroy');
    }


    public function index()
    {
       $model= AgrupamentosContas::OrderBy('nome')->get();


        return view('AgrupamentosContas.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('AgrupamentosContas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AgrupamentosContasCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = AgrupamentosContas::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('AgrupamentosContas.index'));
        }

        $request['user_created'] = Auth::user()->email;

        $model= $request->all();


        AgrupamentosContas::create($model);
        session(['success' => "AGRUPAMENTO DE CONTAS  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('AgrupamentosContas.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = AgrupamentosContas::find($id);
        return view('AgrupamentosContas.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= AgrupamentosContas::find($id);


        return view('AgrupamentosContas.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = AgrupamentosContas::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! Porém confira os demais dados! "]);

        }


        $cadastro = AgrupamentosContas::find($id);


        $request['user_updated'] = Auth::user()->email;
        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('AgrupamentosContas.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

    //    $RedeSocialUsuario = RedeSocialUsuarios::where('RedeSocialRepresentante', $id)->get();

    //    if($RedeSocialUsuario->Count() > 0)
    //    {
    //     session(['error' => "REDE SOCIAL:  ". $request->nome  .",  SELECIONADO NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
    //     return redirect(route('RedeSocial.index'));
    //    }


        $model = AgrupamentosContas::find($id);

        $model->delete();

       session(['success' => "Agrupamentos Contas:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('AgrupamentosContas.edit'));

    }
}
