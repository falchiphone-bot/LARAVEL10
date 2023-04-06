<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoricoCreateRequest;
use App\Http\Requests\HistoricosCreateRequest;
use App\Models\Empresa;
use App\Models\Historico;
use App\Models\Historicos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class HistoricoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:HISTORICOS - LISTAR'])->only('index');
        $this->middleware(['permission:HISTORICOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:HISTORICOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:HISTORICOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:HISTORICOS - EXCLUIR'])->only('destroy');
    }




    /**
     * Display a listing of the resource.
     */


    public function index()
    {

        $empresas = Empresa::get();

        $Historicos = Historicos::OrderBy('Descricao')->get();


        return view('Historicos.index',compact('Historicos','empresas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Historicos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HistoricosCreateRequest $request)
    {
        $Historicos= $request->all();
        //dd($dados);

        Historicos::create($Historicos);

        return redirect(route('Historicos.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Historicos::find($id);
        return view('Historicos.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $Historicos= Historicos::find($id);
        // dd($cadastro);

        return view('Historicos.edit',compact('Historicos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Historicos::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('Historicos.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Historicos = Historicos::find($id);


        $Historicos->delete();
        return redirect(route('Historicos.index'));

    }
}
