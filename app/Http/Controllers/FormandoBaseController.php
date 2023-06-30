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
use App\Http\Requests\ArquivoFormandoBaseCreateRequest;
use App\Http\Requests\FormandoBaseCreateRequest as RequestsFormandoBaseCreateRequest;
use App\Http\Requests\PosicaoFormandoBaseCreateRequest;
use App\Http\Requests\RecebimentoFormandoBaseCreateRequest;
use App\Http\Requests\RedeSocialFormandoBaseCreateRequest;
use App\Models\FormandoBaseArquivo;
use App\Models\FormandoBasePosicoes;
use App\Models\LancamentoDocumento;
use App\Models\Posicoes;
use App\Models\RecebimentoFormandoBase;
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
        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
        ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        ->OrderBy('Descricao')
        ->select(['Empresas.ID', 'Empresas.Descricao'])
        ->get();



        $model = FormandoBase::where('deleted_at', '=', null)
            ->join('Contabilidade.EmpresasUsuarios', 'formandobase.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->orderBy('nome')
            ->get();




        return view('FormandoBase.index', compact('model','Empresas'));
    }
    public function ConsultaEmpresa(Request $request)
    {

        $EmpresaSelecionada = $request->EmpresaSelecionada;

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
        ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        ->OrderBy('Descricao')
        ->select(['Empresas.ID', 'Empresas.Descricao'])
        ->get();


        $model = FormandoBase::where('deleted_at', '=', null)
            ->where('EmpresaID',  $EmpresaSelecionada)
            ->orderBy('nome')
            ->get();


            $retorno['EmpresaSelecionada'] = $EmpresaSelecionada;

            return view('FormandoBase.ConsultaEmpresa', compact('model','retorno','Empresas'));
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
        $Posicao = Posicoes::orderBy('nome')->get();
        $documento = LancamentoDocumento::where('tipoarquivo','>',0)->orderBy('ID', 'desc')->get();



        $redesocialUsuario = RedeSocialUsuarios::where('RedeSocialFormandoBase_id', $id)
            ->orderBy('RedeSocial')
            ->get();

         $FormandoBasePosicao = FormandoBasePosicoes::where('FormandoBase_id', $id)
            ->orderBy('id')
            ->get();

         $redeSocialExiste = null;
         foreach ($redesocialUsuario as $usuario) {
            $redeSocialExiste = $usuario->RedeSocial;

        }
        $posicaoExiste = null;
        foreach ($FormandoBasePosicao as $posicao) {
           $posicaoExiste = $posicao->posicao_id;

       }

       $recebimentoExiste = null;
       $FormandoBaseRecebimento = RecebimentoFormandoBase::where('FormandoBase_id', $id)
            ->orderBy('id')
            ->get();

            foreach ($FormandoBaseRecebimento as $FormandoBaseRecebimentos) {
                $recebimentoExiste = $FormandoBaseRecebimentos->id;

            }


            $arquivoExiste = null;
            $FormandoBaseArquivo = FormandoBaseArquivo::where('FormandoBase_id', $id)
                 ->orderBy('id')
                 ->get();

                 foreach ($FormandoBaseArquivo as $FormandoBaseArquivos) {
                     $arquivoExiste = $FormandoBaseArquivos->id;

                 }


        $model = FormandoBase::find($id);
        $retorno['redesocial'] = $model->RedeSocialRepresentante_id;
        $tiporep['tiporepresentante'] = $model->tipo_representante;
        $retorno['EmpresaSelecionada'] = $model->EmpresaID;

        $representantes = Representantes::where('EmpresaID',$model->EmpresaID)->orderBy('nome')->get();

        return view('FormandoBase.edit', compact('model', 'RedeSocial', 'retorno', 'redesocialUsuario',
        'representantes', 'tiporep', 'Empresas','redeSocialExiste','Posicao','FormandoBasePosicao',
        'posicaoExiste','FormandoBaseRecebimento','recebimentoExiste', 'documento','arquivoExiste','FormandoBaseArquivo'));
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
    public function CreatePosicaoFormandoBase(PosicaoFormandoBaseCreateRequest  $request)
    {

        $id = $request->formandobase_id;

        $existe = FormandoBasePosicoes::where('formandobase_id',$request->formandobase_id)
        ->where('posicao_id',$request->posicao_id)
        ->First();
        if($existe){

            session(['error' => "Posição:  " . $existe->MostraPosicao->nome  .",  já existe para este registro!"]);
            return redirect(route('FormandoBase.edit', $id));
        }

        $request['user_created'] = Auth ::user()->email;

        $model = $request->all();





        FormandoBasePosicoes::create($model);

        return redirect(route('FormandoBase.edit', $id));

    }

    public function CreateRecebimentoFormandoBase(RecebimentoFormandoBaseCreateRequest $request)
    {

        $id = $request->formandobase_id;

        // $existe = RecebimentoFormandoBase::where('formandobase_id',$request->formandobase_id)
        // ->where('posicao_id',$request->posicao_id)
        // ->First();

        // if($existe){

        //     session(['error' => "Posição:  " . $existe->MostraPosicao->nome  .",  já existe para este registro!"]);
        //     return redirect(route('FormandoBase.edit', $id));
        // }

        $request['user_created'] = Auth ::user()->email;

        $model = $request->all();


        RecebimentoFormandoBase::create($model);

        return redirect(route('FormandoBase.edit', $id));

    }


    public function CreateArquivoFormandoBase(ArquivoFormandoBaseCreateRequest $request)
    {

        $id = $request->formandobase_id;
        $formandobase_id = $request->formandobase_id;
        $arquivo_id = $request->arquivo_id;



        $Existe = FormandoBaseArquivo::where('arquivo_id',$arquivo_id)
        ->where('formandobase_id',$formandobase_id)
        ->first();



        if($Existe){
            session(['error' => "ARQUIVO EXISTE:  " . $Existe->MostraLancamentoDocumento->Rotulo.  ' do tipo de arquivo: '. $Existe->MostraLancamentoDocumento->TipoArquivoNome->nome .",  já existe para este registro!"]);
            return redirect(route('FormandoBase.edit', $id));
        }

        $request['user_created'] = Auth ::user()->email;

        $model = $request->all();

        FormandoBaseArquivo::create($model);

        return redirect(route('FormandoBase.edit', $id));

    }

}
