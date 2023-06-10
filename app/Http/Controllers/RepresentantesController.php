<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeriadoCreateRequest;
use App\Http\Requests\FeriadosCreateRequest;
use App\Http\Requests\RepresentantesCreateRequest;
use App\Models\Feriado;
use App\Models\Representantes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\FinancaHelper;

class RepresentantesController extends Controller
{



    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:REPRESENTANTES - LISTAR'])->only('index');
        $this->middleware(['permission:REPRESENTANTES - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:REPRESENTANTES - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:REPRESENTANTES - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:REPRESENTANTES - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $model= Representantes::OrderBy('nome')->get();


        return view('Representantes.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Representantes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RepresentantesCreateRequest $request)
    {
        $model= $request->all();

        $cpf = $request->cpf;
        if (FinancaHelper::validarCPF($cpf)) {
           dd("CPF válido!");
        } else {
            dd("CPF inválido!");
        }


        Representantes::create($model);

        return redirect(route('Representantes.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Representantes::find($id);
        return view('Representantes.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= Representantes::find($id);
        // dd($cadastro);

        return view('Representantes.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Representantes::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('Representantes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model= Representantes::find($id);


        $model->delete();
        return redirect(route('Representantes.index'));

    }



}
