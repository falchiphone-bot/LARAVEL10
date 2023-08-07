<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreparadoresCreateRequest;
use App\Models\CargoProfissional;
use App\Models\FuncaoProfissional;
use App\Models\Preparadores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PreparadoresController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:PREPARADORES - LISTAR'])->only('index');
        $this->middleware(['permission:PREPARADORES - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:PREPARADORES - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:PREPARADORES - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:PREPARADORES - EXCLUIR'])->only('destroy');
    }


    public function index()
    {
       $model= Preparadores::OrderBy('nome')->get();


        return view('Preparadores.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Preparadores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PreparadoresCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Preparadores::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('Preparadores.index'));
        }


        $model= $request->all();


        Preparadores::create($model);
        session(['success' => "Preparador:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('Preparadores.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Preparadores::find($id);
        return view('Preparadores.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $model= Preparadores::find($id);

        $cargoprofissional = CargoProfissional::OrderBy('nome')->get();
        $funcaoprofissional = FuncaoProfissional::OrderBy('nome')->get();


        // dd( $cargoprofissional , $funcaoprofissional );
        return view('Preparadores.edit',compact('model','cargoprofissional','funcaoprofissional'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = Preparadores::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
        //     return redirect(route('Preparadores.index'));
        // }


        $cadastro = Preparadores::find($id);

        // $request['cargoProfissional'] = $request->cargoprofissional;
        // $request['FuncaoProfissional'] = $request->funcaoprofissional;

        // dd($request);
        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('Preparadores.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

        // session(['error' => "Preparador  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO! AVISAR ADMINISTRADOR - ERRO: L121"]);
        // return redirect(route('Preparadores.index'));

    //    $Posicao = Posicoes::where('tipo_esporte', $id)->get();

    //    if($Posicao->Count() > 0)
    //    {

    //     session(['error' => "TIPO DE DOCUMENTO:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
    //     return redirect(route('TipoArquivo.index'));
    //    }


        $model= Preparadores::find($id);

        $model->delete();

       session(['success' => "Preparador:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('Preparadores.index'));

    }
}
