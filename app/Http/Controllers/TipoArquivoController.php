<?php

namespace App\Http\Controllers;

use App\Http\Requests\TipoArquivoCreateRequest;
use App\Models\TipoArquivo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class TipoArquivoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:TIPOARQUIVO - LISTAR'])->only('index');
        $this->middleware(['permission:TIPOARQUIVO - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:TIPOARQUIVO - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:TIPOARQUIVO - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:TIPOARQUIVO - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $model= TipoArquivo::OrderBy('nome')->get();


        return view('TipoArquivo.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('TipoArquivo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipoArquivoCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoArquivo::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('TipoArquivo.index'));
        }


        $model= $request->all();


        TipoArquivo::create($model);
        session(['success' => "TIPO DE DOCUMENTO:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoArquivo.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = TipoArquivo::find($id);
        return view('TipoArquivo.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= TipoArquivo::find($id);


        return view('TipoArquivo.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoArquivo::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('TipoArquivo.index'));
        }


        $cadastro = TipoArquivo::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('TipoArquivo.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

        session(['error' => "TIPO DE DOCUMENTO:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO! AVISAR ADMINISTRADOR - ERRO: L121"]);
        return redirect(route('TipoArquivo.index'));

       $Posicao = Posicoes::where('tipo_esporte', $id)->get();

       if($Posicao->Count() > 0)
       {

        session(['error' => "TIPO DE DOCUMENTO:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
        return redirect(route('TipoArquivo.index'));
       }


        $model= TipoArquivo::find($id);

        $model->delete();

       session(['success' => "TIPO DE ARQUIVO:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoArquivo.index'));

    }
}
