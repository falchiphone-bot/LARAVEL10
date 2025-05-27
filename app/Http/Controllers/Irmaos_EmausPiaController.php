<?php

namespace App\Http\Controllers;

use App\Http\Requests\Irmaos_EmausPiaCreateRequest;
use App\Models\Irmaos_EmausPia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class Irmaos_EmausPiaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_PIA - LISTAR'])->only('index');
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_PIA - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_PIA - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_PIA - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:IRMAOS_EMAUS_NOME_PIA - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $model= Irmaos_EmausPia::OrderBy('nomePia')->get();


        return view('Irmaos_EmausPia.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Irmaos_EmausPia.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Irmaos_EmausPiaCreateRequest $request)
    {
        $Pia = $request->all();

        $Pia['user_created'] = auth()->user()->email;
        $Pia['nomePia'] = trim($Pia['nomePia']);
        $Pia['empresa'] = 1039;

        Irmaos_EmausPia::create($Pia);

        return redirect(route('Irmaos_EmausPia.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Irmaos_EmausPia::find($id);
        return view('Irmaos_EmausPia.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= Irmaos_EmausPia::find($id);
        // dd($model);

        return view('Irmaos_EmausPia.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Irmaos_EmausPiaCreateRequest $request, string $id)
    {

        $Pia = Irmaos_EmausPia::find($id);

        $Pia->fill($request->all()) ;


        $Pia->user_updated = auth()->user()->email;
         $Pia['nomePia'] = trim($Pia['nomePia']);


        $Pia->save();


        return redirect(route('Irmaos_EmausPia.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $servicos= Irmaos_EmausPia::find($id);


        $servicos->delete();
        return redirect(route('Irmaos_EmausPia.index'));

    }
}
