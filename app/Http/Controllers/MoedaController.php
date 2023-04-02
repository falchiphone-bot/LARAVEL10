<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoedaCreateRequest;
use App\Models\Moeda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class MoedaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:MOEDAS - LISTAR'])->only('index');
        $this->middleware(['permission:MOEDAS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:MOEDAS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:MOEDAS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:MOEDAS - EXCLUIR'])->only('destroy');
    }




    /**
     * Display a listing of the resource.
     */


    public function index()
    {
       $moedas= Moeda::get();


        return view('Moedas.index',compact('moedas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Moedas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MoedaCreateRequest $request)
    {
        $moedas= $request->all();
        //dd($dados);

        Moeda::create($moedas);

        return redirect(route('Moedas.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Moeda::find($id);
        return view('Moedas.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $moedas= Moeda::find($id);
        // dd($cadastro);

        return view('Moedas.edit',compact('moedas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Moeda::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('Moedas.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Moeda::find($id);


        $role->delete();
        return redirect(route('Moedas.index'));

    }
}
