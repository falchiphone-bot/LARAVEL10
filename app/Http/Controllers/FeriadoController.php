<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeriadoCreateRequest;
use App\Http\Requests\FeriadosCreateRequest;
use App\Models\Feriado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class FeriadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FERIADOS- LISTAR'])->only('index');
        $this->middleware(['permission:FERIADOS- INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:FERIADOS- EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:FERIADOS- VER'])->only(['edit', 'update']);
        $this->middleware(['permission:FERIADOS- EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $feriados= Feriado::OrderBy('data')->get();


        return view('Feriados.index',compact('feriados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Feriados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FeriadosCreateRequest $request)
    {
        $Feriados= $request->all();


        Feriado::create($Feriados);

        return redirect(route('Feriados.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Feriado::find($id);
        return view('Feriados.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $feriados= Feriado::find($id);
        // dd($cadastro);

        return view('Feriados.edit',compact('feriados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Feriado::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('Feriados.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $feriados= Feriado::find($id);


        $feriados->delete();
        return redirect(route('Feriados.index'));

    }
}
