<?php

namespace App\Http\Controllers;


use App\Models\TipoFormandoBaseWhatsapp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;
use App\Http\Requests\TipoFormandoBaseWhatsappCreateRequest;
use Google\Service\AnalyticsData\OrderBy;
use Google\Service\ServiceControl\Auth as ServiceControlAuth;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Gate;

require_once app_path('helpers.php');

class TipoFormandoBaseWhatsappController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(['permission:TipoFormandoBaseWhatsapp - LISTAR'])->only('index');
        $this->middleware(['permission:TipoFormandoBaseWhatsapp - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:TipoFormandoBaseWhatsapp - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:TipoFormandoBaseWhatsapp - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:TipoFormandoBaseWhatsapp - EXCLUIR'])->only('destroy');
    }



    public function index()
    {
                    $model = TipoFormandoBaseWhatsapp::all();

                    return view('TipoFormandoBaseWhatsapp.index', compact('model'));
    }



    public function create()
    {
        return view('TipoFormandoBaseWhatsapp.create');
    }

    public function store( TipoFormandoBaseWhatsappCreateRequest $request)
    {


        $request['nome'] = strtoupper($request['nome']);

        $request['user_created'] = Auth::user()->email;
        $request['EmpresaID'] = 11;
        $model = $request->all();



 TipoFormandoBaseWhatsapp::create($model);
        return redirect(route('TipoFormandoBaseWhatsapp.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = TipoFormandoBaseWhatsapp::find($id);

        return view('TipoFormandoBaseWhatsapp.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {



        $model = TipoFormandoBaseWhatsapp::find($id);


        return view('TipoFormandoBaseWhatsapp.edit', compact('model'));
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






        $cadastro = TipoFormandoBaseWhatsapp::find($id);


        $request['nome'] = strtoupper($request['nome']);
        $request['user_updated'] = Auth::user()->email;

        $cadastro->fill($request->all());



        $cadastro->save();

        session(['success' => 'NOME:  ' . $request->nome . ', ALTERADO! ']);
        // return redirect(route('Pacpie.edit',$id));

        // return redirect(route('Pacpie.index'));

        // return view('Pacpie/go-back-twice-and-refresh');
        return redirect(route('TipoFormandoBaseWhatsapp.index'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = TipoFormandoBaseWhatsapp::find($id);

        $model->delete();
        return redirect(route('TipoFormandoBaseWhatsapp.index'));
    }



}
