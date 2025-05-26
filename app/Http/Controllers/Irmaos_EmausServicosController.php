<?php

namespace App\Http\Controllers;

use App\Http\Requests\Irmaos_EmausServicosCreateRequest;
use App\Models\Irmaos_EmausServicos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class Irmaos_EmausServicosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_SERVICO - LISTAR'])->only('index');
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_SERVICO - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_SERVICO - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_SERVICO - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_SERVICO - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $model= Irmaos_EmausServicos::OrderBy('nomeServico')->get();


        return view('Irmaos_EmausServicos.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Irmaos_EmausServicos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Irmaos_EmausServicosCreateRequest $request)
    {
        $Servicos = $request->all();

        $Servicos['user_created'] = auth()->user()->email;
        $Servicos['nomeServicos'] = trim($Servicos['nomeServico']);

        Irmaos_EmausServicos::create($Servicos);

        return redirect(route('Irmaos_EmausServicos.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Irmaos_EmausServicos::find($id);
        return view('Irmaos_EmausServicos.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= Irmaos_EmausServicos::find($id);
        // dd($model);

        return view('Irmaos_EmausServicos.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Irmaos_EmausServicosCreateRequest $request, string $id)
    {

        $Servicos = Irmaos_EmausServicos::find($id);

        $Servicos->fill($request->all()) ;


        $Servicos->user_updated = auth()->user()->email;
         $Servicos['nomeServico'] = trim($Servicos['nomeServico']);


        $Servicos->save();


        return redirect(route('Irmaos_EmausServicos.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $servicos= Irmaos_EmausServicos::find($id);


        $servicos->delete();
        return redirect(route('Irmaos_EmausServicos.index'));

    }
}
