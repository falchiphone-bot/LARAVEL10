<?php

namespace App\Http\Controllers;


use App\Http\Requests\CentroCustosCreateRequest;
use App\Models\CentroCustos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CentroCustosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:CENTROCUSTOS - DASHBOARD'])->only('dashboard');
        $this->middleware(['permission:CENTROCUSTOS - LISTAR'])->only('index');
        $this->middleware(['permission:CENTROCUSTOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:CENTROCUSTOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:CENTROCUSTOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:CENTROCUSTOS - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */

     public function dashboard()
     {
         return view('CentroCustos.dashboard');
     }

    public function index()
    {
       $CentroCustos = CentroCustos::OrderBy('Descricao')->get();


        return view('CentroCustos.index',compact('CentroCustos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('CentroCustos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CentroCustosCreateRequest $request)
    {
        $CentroCustos = $request->all();


        CentroCustos::create($CentroCustos);

        return redirect(route('CentroCustos.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = CentroCustos::find($id);
        return view('CentroCustos.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $CentroCustos= CentroCustos::find($id);
        // dd($cadastro);

        return view('CentroCustos.edit',compact('CentroCustos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = CentroCustos::find($id);

        
        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('CentroCustos.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $CentroCustos = CentroCustos::find($id);


        $CentroCustos->delete();
        return redirect(route('CentroCustos.index'));

    }
}
