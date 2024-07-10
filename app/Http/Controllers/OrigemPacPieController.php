<?php

namespace App\Http\Controllers;


use App\Models\OrigemPacpie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;
use App\Http\Requests\OrigemPacpieCreateRequest;
use Google\Service\AnalyticsData\OrderBy;
use Google\Service\ServiceControl\Auth as ServiceControlAuth;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Gate;

require_once app_path('helpers.php');

class OrigemPacpieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(['permission:ORIGEMPACPIE - LISTAR'])->only('index');
        $this->middleware(['permission:ORIGEMPACPIE - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:ORIGEMPACPIE - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:ORIGEMPACPIE - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:ORIGEMPACPIE - EXCLUIR'])->only('destroy');
    }


    public function AjustaCampos()
    {

        $model = OrigemPacpie::whereNotNull('email')->get();

        foreach ($model as $item) {
            $item->email = strtolower($item->email);
            $item->nome = strtoupper($item->nome);
            try {
                $item->save();
            } catch (Exception $e) {
                // Handle the exception as needed
                echo 'Falha ao salvar: ',  $e->getMessage(), "\n";
                session(['error' => 'Falha ao salvar: ',  $e->getMessage(), "\n"]);
            }
        }

        session(['success' => 'ATUALIZADO COM SUCESSO! Campo email para tudo minúsculo como padrão e campo nome para tudo maiusculo como padrão' ]);
        return redirect(route('OrigemPacpie.index', compact('model')));
        // return view('Pacpie.index', compact('model'));

    }


    public function index()
    {

                    $model = OrigemPacpie::all();

                    return view('OrigemPacpie.index', compact('model'));

    }





    public function create()
    {
        return view('OrigemPacpie.create');
    }

    public function store( OrigemPacpieCreateRequest $request)
    {
        $cnpj = $request->cnpj;

        $LiberaCNPJ = $request->liberacnpj;

        $limpacnpj = $request->limpacnpj;

        $request['nome'] = strtoupper($request['nome']);


        if ($LiberaCNPJ == null) {
            if ($cnpj) {
                if (validarCNPJ($cnpj)) {
                    session(['cnpj' => 'CNPJ:  ' . $request->cnpj . ', VALIDADO! ']);
                } else {
                    session(['error' => 'CNPJ:  ' . $request->cnpj . ', DEVE SER CORRIGIDO! NADA ALTERADO! ']);
                    return redirect(route('Pacpie.edit', $id));
                }
            }
        } else {
            if ($limpacnpj) {
                $request['cnpj'] = null;
            }
        }

        $enderecoEmailIncorreto = $request->email;
        $enderecoEmailCorrigido = corrigirEnderecoEmail($enderecoEmailIncorreto);
        $request['email'] = $enderecoEmailCorrigido;

        $request['user_created'] = Auth::user()->email;
        $request['EmpresaID'] = 11;
        $model = $request->all();



 OrigemPacpie::create($model);
        return redirect(route('OrigemPacpie.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = OrigemPacpie::find($id);

        return view('OrigemPacpie.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {



        $model = OrigemPacpie::find($id);


        return view('OrigemPacpie.edit', compact('model'));
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






        $cadastro = OrigemPacpie::find($id);


        $request['nome'] = strtoupper($request['nome']);
        $request['user_updated'] = Auth::user()->email;

        $cadastro->fill($request->all());



        $cadastro->save();

        session(['success' => 'NOME:  ' . $request->nome . ', ALTERADO! ']);
        // return redirect(route('Pacpie.edit',$id));

        // return redirect(route('Pacpie.index'));

        // return view('Pacpie/go-back-twice-and-refresh');
        return redirect(route('OrigemPacpie.index'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = OrigemPacpie::find($id);

        $model->delete();
        return redirect(route('OrigemPacpie.index'));
    }



}
