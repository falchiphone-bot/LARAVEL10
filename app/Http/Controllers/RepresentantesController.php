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
use App;


require_once app_path('helpers.php');


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



    public function index()
    {
       $model= Representantes::OrderBy('nome')->get();


        return view('Representantes.index',compact('model'));
    }

    public function create()
    {
        return view('Representantes.create');
    }

    public function store(RepresentantesCreateRequest $request)
    {
        $model= $request->all();

        $cpf = $request->cpf;

        if(validarCPF($cpf)){
            session(['cpf' => "CPF:  ". $request->cpf  .", VALIDADO! "]);
        }else {

            session(['error' => "CPF:  ". $request->cpf  .", DEVE SER CORRIGIDO! NADA ALTERADO! "]);
            return redirect(route('Representantes.index'));
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
            $cpf = $request->cpf;
            $cnpj = $request->cnpj;
                if($cpf )
                {

                    if(validarCPF($cpf)){
                        session(['cpf' => "CPF:  ". $request->cpf  .", VALIDADO! "]);
                    }else {

                        session(['error' => "CPF:  ". $request->cpf  .", DEVE SER CORRIGIDO! NADA ALTERADO! "]);
                        return  redirect(route('Representantes.edit', $id));
                    }
                }


                if($cnpj)
                {
                    if(validarCNPJ($cnpj)){
                        session(['cnpj' => "CNPJ:  ". $request->cnpj  .", VALIDADO! "]);
                    }else {

                        session(['error' => "CNPJ:  ". $request->cnpj  .", DEVE SER CORRIGIDO! NADA ALTERADO! "]);
                        return  redirect(route('Representantes.edit', $id));
                    }
                }





        $cadastro = Representantes::find($id);

        $cadastro->fill($request->all()) ;

 
        $cadastro->save();

        session(['success' => "NOME:  ". $request->nome  .", ALTERADO! "]);
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
