<?php

namespace App\Http\Controllers;
use App\Models;
use App\Models\Empresa;
use App\Http\Requests\FormandoBaseCreateRequest;
use App\Models\FormandoBase;
use App\Models\Representantes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;
use App\Http\Requests\FormandoBaseCreateRequest as RequestsFormandoBaseCreateRequest;
use App\Http\Requests\RedeSocialFormandoBaseCreateRequest;
use App\Models\RedeSocial;
use App\Models\RedeSocialUsuarios;
use App\Models\TipoRepresentante;
use Illuminate\Support\Facades\Auth;

require_once app_path('helpers.php');

class FormandoBaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FORMANDOBASE - LISTAR'])->only('index');
        $this->middleware(['permission:FORMANDOBASE - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:FORMANDOBASE - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:FORMANDOBASE - VER'])->only('show');
        $this->middleware(['permission:FORMANDOBASE - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:FORMANDOBASE - VERIFICA FORMANDOS EXCLUIDOS'])->only('indexExcluidos');
    }

    public function index()
    {
            $model = FormandoBase::where('deleted_at', '=', null)
            ->join('Contabilidade.EmpresasUsuarios', 'formandobase.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->orderBy('nome')
            ->get();



        return view('FormandoBase.index', compact('model'));
    }

    public function Excluidos(Request $request)
    {
        if ($request['opcao'] = 'Excluidos') {
            $model = FormandoBase::where('deleted_at', '!=', null)
                ->orderBy('nome')
                ->get();
        } elseif ($request['opcao'] = 'Ativados') {
            $model = FormandoBase::where('deleted_at', '=', null)
                ->orderBy('nome')
                ->get();
        }

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        return view('FormandoBase.excluidos', compact('model'));
    }

    public function create()
    {
        $representantes = Representantes::orderBy('nome')->get();

        $representante['representante'] = null;
        $retorno['EmpresaSelecionada'] = null;

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        return view('FormandoBase.create', compact('representantes', 'retorno', 'Empresas'));
    }

    public function store(FormandoBaseCreateRequest $request)
    {
        $cpf = $request->cpf;

        $request['nome'] = strtoupper($request['nome']);

        $existecadastro = FormandoBase::where('nome', trim($request['nome']))->first();
        if ($existecadastro) {
            session(['error' => 'NOME:  ' . $request->nome . ', já existe! NADA INCLUÍDO! ']);
            return redirect(route('FormandoBase.index'));
        }

        if (validarCPF($cpf)) {
            session(['cpf' => 'CPF:  ' . $request->cpf . ', VALIDADO! ']);
        } else {
            session(['error' => 'CPF:  ' . $request->cpf . ', DEVE SER CORRIGIDO! NADA ALTERADO! ']);
            return redirect(route('FormandoBase.index'));
        }

        $enderecoEmailIncorreto = $request->email;
        $enderecoEmailCorrigido = corrigirEnderecoEmail($enderecoEmailIncorreto);
        $request['email'] = $enderecoEmailCorrigido;

        $request['email'] = strtolower($request->email);

        $request['EmpresaID'] = session('Empresa')->ID;


        $request["EmpresaID"] = $request->EmpresaSelecionada ;

        $model= $request->all();


        FormandoBase::create($model);

        return redirect(route('FormandoBase.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = FormandoBase::find($id);
        return view('FormandoBase.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        session(['FormandoBase' => $id]);
        $RedeSocial = RedeSocial::orderBy('nome')->get();

        $redesocialUsuario = RedeSocialUsuarios::where('RedeSocialFormandoBase_id', $id)
            ->orderBy('RedeSocial')
            ->get();

         $redeSocialExiste = null;
         foreach ($redesocialUsuario as $usuario) {
            $redeSocialExiste = $usuario->RedeSocial;

        }
         

        $representantes = Representantes::orderBy('nome')->get();

        $model = FormandoBase::find($id);
        $retorno['redesocial'] = $model->RedeSocialRepresentante_id;
        $tiporep['tiporepresentante'] = $model->tipo_representante;
        $retorno['EmpresaSelecionada'] = $model->EmpresaID;

        return view('FormandoBase.edit', compact('model', 'RedeSocial', 'retorno', 'redesocialUsuario', 'representantes', 'tiporep', 'Empresas','redeSocialExiste'));
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
                    return redirect(route('FormandoBase.edit', $id));
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
                    return redirect(route('FormandoBase.edit', $id));
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
            return redirect(route('Representantes.edit', $id));

            // Definir o endereço de e-mail corrigido como vazio ou null
            // $emailCorrigido = '';
        }

        // Atualiza a propriedade email do objeto $request com o endereço corrigido
        $request['email'] = $emailCorrigido;

        $cadastro = FormandoBase::find($id);
        $request['nome'] = strtoupper($request['nome']);
        $cadastro->fill($request->all());

        $cadastro->save();

        session(['success' => 'NOME:  ' . $request->nome . ', ALTERADO! ']);
        return redirect(route('FormandoBase.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = FormandoBase::find($id);

        if ($model['deleted_at'] == null) {
            $model['deleted_at'] = Carbon::now();
        } else {
            $model['deleted_at'] = null;
        }

        // $model->delete();
        $model->save();
        return redirect(route('FormandoBase.index'));
    }

    public function CreateRedeSocialFormandoBase(RedeSocialFormandoBaseCreateRequest $request)
    {

        $request['user_created'] = Auth ::user()->email;
        $model= $request->all();

        $id = $request->RedeSocialFormandoBase_id;
        RedeSocialUsuarios::create($model);

        return redirect(route('FormandoBase.edit', $id));

    }



}
