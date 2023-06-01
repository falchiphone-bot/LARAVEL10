<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoedaCreateRequest;
use App\Http\Requests\MoedaValoresCreateRequest;
use App\Models\LancamentoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class LancamentosDocumentosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - LISTAR'])->only('index');
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:LANCAMENTOS DOCUMENTOS - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    // public function dashboard()
    // {
    //     return view('Moedas.dashboard');
    // }


    public function index()
    {
       $documentos = LancamentoDocumento::Limit(100)->OrderBy('ID','DESC' )->get();


        return view('LancamentosDocumentos.index',compact('documentos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     return view('LancamentosDocumentos.create');
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(MoedaValoresCreateRequest $request)
    // {
    //     $moedas= $request->all();
    //     //dd($dados);

    //     Moeda::create($moedas);

    //     return redirect(route('LancamentosDocumentos.index'));

    // }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     $cadastro = Moeda::find($id);
    //     return view('Moedas.show',compact('cadastro'));
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $documento = LancamentoDocumento::find($id);


        return view('LancamentosDocumentos.edit',compact('documento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = LancamentoDocumento::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('LancamentosDocumentos.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $moedas = Moeda::find($id);


        $moedas->delete();
        return redirect(route('LancamentosDocumentos.index'));

    }
}
