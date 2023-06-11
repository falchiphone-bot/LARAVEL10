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


        $cpf = $request->cpf;

        $request["nome"] = strtoupper($request["nome"]);

        $existecadastro = Representantes::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('Representantes.index'));
        }

        if(validarCPF($cpf)){
            session(['cpf' => "CPF:  ". $request->cpf  .", VALIDADO! "]);
        }else {

            session(['error' => "CPF:  ". $request->cpf  .", DEVE SER CORRIGIDO! NADA ALTERADO! "]);
            return redirect(route('Representantes.index'));
        }

        $enderecoEmailIncorreto = $request->email;
        $enderecoEmailCorrigido = corrigirEnderecoEmail($enderecoEmailIncorreto);
        $request["email"] = $enderecoEmailCorrigido;


        $model= $request->all();
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
            $LiberaCPF = $request->liberacpf;
            $LiberaCNPJ = $request->liberacnpj;
            $limpacpf = $request->limpacpf;
            $limpacnpj = $request->limpacnpj;


        if($LiberaCPF == null)
        {


            if($cpf)
                {
                    if(validarCPF($cpf)){
                        session(['cpf' => "CPF:  ". $request->cpf  .", VALIDADO! "]);
                    }else
                    {

                        session(['error' => "CPF:  ". $request->cpf  .", DEVE SER CORRIGIDO! NADA ALTERADO! "]);
                        return  redirect(route('Representantes.edit', $id));

                    }
                }

        }
        else{
            if($limpacpf){
                $request["cpf"] = "";
            }

        }



        if($LiberaCNPJ == null)
        {


                if($cnpj)
                {
                    if(validarCNPJ($cnpj)){
                        session(['cnpj' => "CNPJ:  ". $request->cnpj  .", VALIDADO! "]);
                    }else {

                        session(['error' => "CNPJ:  ". $request->cnpj  .", DEVE SER CORRIGIDO! NADA ALTERADO! "]);
                        return  redirect(route('Representantes.edit', $id));
                    }
                }

        }
        else{
            if($limpacnpj){
                $request["cnpj"] = null;
            }

        }


// Obtém o endereço de e-mail do objeto $request
$email = $request->email;

// Remove caracteres inválidos do endereço de e-mail
$emailCorrigido = preg_replace('/[^a-zA-Z0-9.@_-]/', '', $email);

// Verifica se o símbolo "@" está presente no endereço corrigido
if (strpos($emailCorrigido, '@') === false) {
    // Endereço de e-mail inválido, pode lidar com o erro aqui
    // Por exemplo, lançar uma exceção ou retornar uma mensagem de erro
    // ...

    session(['error' => "EMAIL:  ". $request->email  .", DEVE SER CORRIGIDO! NADA ALTERADO! RETORNADO AO VALOR JÁ REGISTRADO! "]);
    return  redirect(route('Representantes.edit', $id));

    // Definir o endereço de e-mail corrigido como vazio ou null
    // $emailCorrigido = '';
}

// Atualiza a propriedade email do objeto $request com o endereço corrigido
$request["email"] = $emailCorrigido;




        $cadastro = Representantes::find($id);
        $request["nome"] = strtoupper($request["nome"]);
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
