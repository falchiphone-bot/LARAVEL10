<?php

namespace App\Http\Controllers;


use App\Models\Pacpie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;
use App\Http\Requests\PacpieCreateRequest;
use Google\Service\ServiceControl\Auth as ServiceControlAuth;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Gate;

require_once app_path('helpers.php');

class PacpieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(['permission:PACPIE - LISTAR'])->only('index');
        $this->middleware(['permission:PACPIE - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:PACPIE - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:PACPIE - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:PACPIE - EXCLUIR'])->only('destroy');
    }



    public function retornar2paginasatualizar()
    {
        dd(39);
        return view('Pacpie/go-back-twice-and-refresh');
    }


    public function index()
    {

                    // $model = Pacpie::join('Contabilidade.EmpresasUsuarios', 'Representantes.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
                    //     ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
                    //     ->orderBy('nome')
                    //     ->get();
                    //

                    $model = Pacpie::all();

                    // dd('PACPIE INDEX', $Pacpie);

                    return view('Pacpie.index', compact('model'));

    }



    public function create()
    {
        return view('Pacpie.create');
    }

    public function store( PacpieCreateRequest $request)
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

        $request['user_updated'] = Auth::user()->email;
        $request['EmpresaID'] = 11;
        $model = $request->all();


// dd($model);
 Pacpie::create($model);
        return redirect(route('Pacpie.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Pacpie::find($id);

        return view('Pacpie.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        session(['Representante_id' => $id]);


        $model = Pacpie::find($id);


        return view('Pacpie.edit', compact('model'));
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


        if ($LiberaCPF == null) {
            if ($cpf) {
                if (validarCPF($cpf)) {
                    session(['cpf' => 'CPF:  ' . $request->cpf . ', VALIDADO! ']);
                } else {
                    session(['error' => 'CPF:  ' . $request->cpf . ', DEVE SER CORRIGIDO! NADA ALTERADO! ']);
                    return redirect(route('Representantes.edit', $id));
                }
            }
        } else {
            if ($limpacpf) {
                $request['cpf'] = '';
            }
        }

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

        // Obtém o endereço de e-mail do objeto $request
        $email = $request->email;


        // Remove caracteres inválidos do endereço de e-mail
        $emailCorrigido = preg_replace('/[^a-zA-Z0-9.@_-]/', '', $email);

        // Verifica se o símbolo "@" está presente no endereço corrigido
        if (strpos($emailCorrigido, '@') === false) {
            // Endereço de e-mail inválido, pode lidar com o erro aqui
            // Por exemplo, lançar uma exceção ou retornar uma mensagem de erro
            // ...

            session(['error' => 'EMAIL:  ' . $request->email . ', DEVE SER CORRIGIDO! NADA ALTERADO! RETORNADO AO VALOR JÁ REGISTRADO! ']);
            return redirect(route('Pacpie.edit', $id));

            // Definir o endereço de e-mail corrigido como vazio ou null
            // $emailCorrigido = '';
        }

        // Atualiza a propriedade email do objeto $request com o endereço corrigido
        $request['email'] = $emailCorrigido;

        $cadastro = Pacpie::find($id);


        $request['nome'] = strtoupper($request['nome']);
        $request['user_updated'] = Auth::user()->email;
        $request['emailprimeirocontato'] = $request->emailprimeirocontato;
        $cadastro->fill($request->all());

        // dd($request->all());

        $cadastro->save();
// dd($cadastro);
        session(['success' => 'NOME:  ' . $request->nome . ', ALTERADO! ']);
        // return redirect(route('Pacpie.edit',$id));

        // return redirect(route('Pacpie.index'));

        return view('Pacpie/go-back-twice-and-refresh');
 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = Pacpie::find($id);

        $model->delete();
        return redirect(route('Pacpie.index'));
    }

    public function CreateRedeSocialRepresentantes(RedeSocialRepresentantesCreateRequest $request)
    {
        $request['user_created'] = Auth::user()->email;
        $model = $request->all();
        RedeSocialUsuarios::create($model);

        return redirect(route('Representantes.edit',$request->RedeSocialRepresentante_id));
    }

    // public function UpdateRedeSocialRepresentantes(Request $request, string $id)
    // {

    //     $cadastro =  RedeSocialUsuarios::find($id);
    //     $request['user_updated'] = Auth ::user()->email;
    //     $cadastro->fill($request->all()) ;

    //     $cadastro->save();

    //     return redirect(route('Representantes.index'));
    // }
    public function DestroyRedeSocialRepresentantes(string $id)
    {
        dd($id);

        $model = RedeSocialUsuarios::find($id);

        $model->delete();
        session(['success' => 'REDE SOCIAIS:  ' . $model->RedeSocialRepresentantes->nome . ' EXCLUÍDO COM SUCESSO!']);
        return redirect(route('Representantes.edit'));
    }
}
