<?php

namespace App\Http\Controllers;
use App\Models;
use App\Helpers;
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
use App\Http\Requests\AvaliacaoFormandoBaseCreateRequest;
use App\Models\FormandoBaseArquivo;
use App\Models\FormandoBaseAvaliacao;
use App\Models\FormandoBasePosicoes;
use App\Models\FormandoBaseWhatsapp;
use App\Models\LancamentoDocumento;
use App\Models\Posicoes;
use App\Models\RecebimentoFormandoBase;
use App\Models\RedeSocial;
use App\Models\RedeSocialUsuarios;
use App\Models\TipoRepresentante;
use DateTime;
use Illuminate\Support\Facades\Auth;

require_once app_path('helpers.php');

class FormandoBaseWhatsappController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FORMANDOBASEWHATSAPP - LISTAR'])->only('index');
        $this->middleware(['permission:FORMANDOBASEWHATSAPP - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:FORMANDOBASEWHATSAPP - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:FORMANDOBASEWHATSAPP - VER'])->only('show');
        $this->middleware(['permission:FORMANDOBASEWHATSAPP - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:FORMANDOBASEWHATSAPP - VERIFICA FORMANDOS EXCLUIDOS'])->only('indexExcluidos');
    }

    public function index()
    {
        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
        ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        ->OrderBy('Descricao')
        ->select(['Empresas.ID', 'Empresas.Descricao'])
        ->get();

        $limite = 1000;
        $retorno['Limite'] = $limite;
        $model = FormandoBaseWhatsapp::limit($limite)
        ->orderBy('nome')
        ->get();


        return view('FormandoBaseWhatsapp.index', compact('model','Empresas', 'retorno'));
    }


    public function AtualizaWatsapp()
    {
        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
        ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        ->OrderBy('Descricao')
        ->select(['Empresas.ID', 'Empresas.Descricao'])
        ->get();


        $model = FormandoBaseWhatsapp::Where('whatsapp', NULL)
        // ->where('flow_description', '!=', NULL)
        // ->orwhere('flow_description', '!=', '')
        ->orderBy('nome')
        ->get();

        // dd($model, $model->Count());


       if($model->Count() == 0){
        session(['error' => 'NADA A ATUALIZAR! ']);
        return view('FormandoBaseWhatsapp.index', compact('model','Empresas' ));
       }

        foreach ($model as $item) {

            if($item->whatsapp){
                    continue;
            }

            $whatsapp = trim($item->flow_description); // Remove espaços em branco no início e no fim

            // Remove espaços em branco extras no meio (opcional, dependendo da sua necessidade)
            $whatsapp = preg_replace('/\s+/', '', $whatsapp);
            $whatsapp = substr($whatsapp, 0, 15);

            if($whatsapp){
                $whatsapp = "55" . $whatsapp;
            }
            $item->whatsapp = $whatsapp;

            // dd($whatsapp);
            $item->save();

            // session(['error' => 'ATUALIZADO O TELEFONE: '.$item->whatsapp ]);
            // return view('FormandoBaseWhatsapp.index', compact('model','Empresas' ));

            // break;
        }


        session(['success' => 'ATUALIZADO OS TELEFONES DE WHATSAPP NO BANCO DE DADOS! ']);
        return view('FormandoBaseWhatsapp.index', compact('model','Empresas' ));
    }

// ==============================
public function AtualizaIdade()
{
    $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
    ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
    ->OrderBy('Descricao')
    ->select(['Empresas.ID', 'Empresas.Descricao'])
    ->get();

    $limite = 10000000;
    $retorno['Limite'] = $limite;
    $model = FormandoBaseWhatsapp::limit($limite)
    ->orderBy('nome')
    ->get();

    foreach ($model as $item) {
        $dataNascimento = $item->nascimento;


        // Converte a string da data de nascimento para um objeto DateTime
        $data_nascimento = new DateTime($dataNascimento);

        // Obtém a data atual
        $data_atual = new DateTime();

        // Calcula a diferença entre a data atual e a data de nascimento
        $intervalo = $data_atual->diff($dataNascimento);

        // Obtém a idade em anos
        $idade = $intervalo->y;


        $item->idade = $idade;
        $item->save();

        // dd($item->idade);
    }

    session(['success' => 'ATUALIZADO AS IDADES NO BANCO DE DADOS! ']);
    return view('FormandoBaseWhatsapp.index', compact('model','Empresas', 'retorno'));
}




    public function indexBusca(Request $request)
    {
        $limite = null;
        if($request->Limite >= 1){
            $limite = $request->Limite;
        }
        else{
            $request['Limite'] = null;
        }


       $Avaliacao = $request->Avaliacao;

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
        ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        ->OrderBy('Descricao')
        ->select(['Empresas.ID', 'Empresas.Descricao'])
        ->get();

            if ($request->BuscarNome) {
                $texto = $request->BuscarNome;
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('nome', 'like', '%' . $texto . '%')
                ->orderBy('nome', 'asc')
                ->get();
            }
            else{
                $model = FormandoBaseWhatsapp::limit($limite)
               ->orderBy('nome', 'asc')
                ->get();
            }


            if($Avaliacao == 1){
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('motivo_cadastro', '!=', '')
                ->orderBy('motivo_cadastro', 'asc')
                ->orderBy('nome', 'asc')
                ->get();
            }
            else
             {
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('motivo_cadastro', $Avaliacao)
                ->orderBy('motivo_cadastro', 'asc')
                ->orderBy('nome', 'asc')
                ->get();
            }




            $retorno = $request->all();


            if($retorno['Categoria'] == 'SUB11')
            {
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('idade','=', 11)
                ->orderBy('nome', 'asc')
                ->get();
            }
            else
            if($retorno['Categoria'] == 'SUB12')
            {
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('idade','=', 12)
                ->orderBy('nome', 'asc')
                ->get();
            }
            else
            if($retorno['Categoria'] == 'SUB13')
            {
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('idade','=', 13)
                ->orderBy('nome', 'asc')
                ->get();
            }
            else
            if($retorno['Categoria'] == 'SUB14')
            {
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('idade','=', 14)
                ->orderBy('nome', 'asc')
                ->get();
            }
            else
            if($retorno['Categoria'] == 'SUB15')
            {
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('idade','=', 15)
                ->orderBy('nome', 'asc')
                ->get();
            }
            else
            if($retorno['Categoria'] == 'SUB17')
            {
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('idade','<=', 17)
                ->where('idade','>=', 16)
                ->orderBy('nome', 'asc')
                ->get();
            }
            else
            if($retorno['Categoria'] == 'SUB20')
            {
                $model = FormandoBaseWhatsapp::limit($limite)
                ->where('idade','<=', 20)
                ->where('idade','>=', 18)
                ->orderBy('nome', 'asc')
                ->get();
            }

// dd($retorno);
            // dd($model, $model->Count()
        return view('FormandoBaseWhatsapp.index', compact('model','Empresas', 'retorno'));
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

        return view('FormandoBaseWhatsapp.create', compact('representantes', 'retorno', 'Empresas'));
    }

    public function store(FormandoBaseWhatsappCreateRequest $request)
    {
        $cpf = $request->cpf;

        $LiberaCPF = $request->liberacpf;
        $limpacpf = $request->limpacpf;

        $request['nome'] = strtoupper($request['nome']);

        $existecadastro = FormandoBase::where('nome', trim($request['nome']))->first();
        if ($existecadastro) {
            session(['error' => 'NOME:  ' . $request->nome . ', já existe! NADA INCLUÍDO! ']);
            return redirect(route('FormandoBase.index'));
        }

        // if (validarCPF($cpf)) {
        //     session(['cpf' => 'CPF:  ' . $request->cpf . ', VALIDADO! ']);
        // } else {
        //     session(['error' => 'CPF:  ' . $request->cpf . ', DEVE SER CORRIGIDO! NADA ALTERADO! ']);
        //     return redirect(route('FormandoBase.index'));
        // }

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

        $enderecoEmailIncorreto = $request->email;
        $enderecoEmailCorrigido = corrigirEnderecoEmail($enderecoEmailIncorreto);
        $request['email'] = $enderecoEmailCorrigido;

        $request['email'] = strtolower($request->email);




        $request["EmpresaID"] = $request->EmpresaSelecionada ;

        $model= $request->all();


        FormandoBaseWhatsapp::create($model);

        return redirect(route('FormandoBaseWhatsapp.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = FormandoBaseWhatsapp::find($id);
        return view('FormandoBaseWhatsapp.show', compact('cadastro'));
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

        session(['FormandoBaseWhatsapp' => $id]);


        $RedeSocial = RedeSocial::orderBy('nome')->get();
        $Posicao = Posicoes::orderBy('nome')->get();
        $documento = LancamentoDocumento::where('tipoarquivo','>',0)->orderBy('ID', 'desc')->get();



        $redesocialUsuario = RedeSocialUsuarios::where('RedeSocialFormandoBase_id', $id)
            ->orderBy('RedeSocial')
            ->get();

         $FormandoBasePosicao = FormandoBasePosicoes::where('FormandoBase_id', $id)
            ->orderBy('id')
            ->get();

            $FormandoBaseAvaliacao = FormandoBaseAvaliacao::where('FormandoBase_id', $id)
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

       $avaliacaoExiste = null;
       if($FormandoBaseAvaliacao)
       {
        $avaliacaoExiste = true;
       }


       $recebimentoExiste = null;
       $FormandoBaseRecebimento = RecebimentoFormandoBase::where('FormandoBase_id', $id)
            ->orderBy('id')
            ->get();

            foreach ($FormandoBaseRecebimento as $FormandoBaseRecebimentos) {
                $recebimentoExiste = $FormandoBaseRecebimentos->id;


            }
            $TotalRecebido =  $FormandoBaseRecebimento->sum('patrocinio');

            $arquivoExiste = null;
            $FormandoBaseArquivo = FormandoBaseArquivo::where('FormandoBase_id', $id)
                 ->orderBy('id')
                 ->get();

                 foreach ($FormandoBaseArquivo as $FormandoBaseArquivos) {
                     $arquivoExiste = $FormandoBaseArquivos->id;

                 }


        $model = FormandoBaseWhatsapp::find($id);
        $retorno['redesocial'] = $model->RedeSocialRepresentante_id;
        $tiporep['tiporepresentante'] = $model->tipo_representante;
        $retorno['EmpresaSelecionada'] = $model->EmpresaID;

        $representantes = Representantes::where('EmpresaID',$model->EmpresaID)->orderBy('nome')->get();

        return view('FormandoBaseWhatsapp.edit', compact('model', 'RedeSocial', 'retorno', 'redesocialUsuario',
        'representantes', 'tiporep', 'Empresas','redeSocialExiste','Posicao','FormandoBasePosicao', 'FormandoBaseAvaliacao',
        'posicaoExiste', 'avaliacaoExiste','FormandoBaseRecebimento',
        'recebimentoExiste', 'documento','arquivoExiste','FormandoBaseArquivo','TotalRecebido'));
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
        $whatsapp = $request->whatsapp;
        $idade = $request->idade;

        if ($LiberaCPF == null) {
            if ($cpf) {
                if (validarCPF($cpf)) {
                    session(['cpf' => 'CPF:  ' . $request->cpf . ', VALIDADO! ']);
                } else {
                    session(['error' => 'CPF:  ' . $request->cpf . ', DEVE SER CORRIGIDO! NADA ALTERADO! ']);
                    return redirect(route('FormandoBaseWhatsapp.edit', $id));
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
                    return redirect(route('FormandoBaseWhatsapp.edit', $id));
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

            // session(['error' => 'EMAIL:  ' . $request->email . ', DEVE SER CORRIGIDO! NADA ALTERADO! RETORNADO AO VALOR JÁ REGISTRADO! ']);
            // return redirect(route('Representantes.edit', $id));

            // Definir o endereço de e-mail corrigido como vazio ou null
            // $emailCorrigido = '';
        }

        // Atualiza a propriedade email do objeto $request com o endereço corrigido
        $request['email'] = $emailCorrigido;
        $request['whatsapp'] = $whatsapp;
        $cadastro = FormandoBaseWhatsapp::find($id);
        $request['nome'] = strtoupper($request['nome']);
        $request['idade'] = strtoupper($request['idade']);
        // $cadastro->avaliacao = round($request->avaliacao, 2);
        // dd($request->all());
        $cadastro->fill($request->all());

        $cadastro->save();

        session(['success' => 'NOME:  ' . $request->nome . ', ALTERADO! ']);
        return redirect(route('FormandoBaseWhatsapp.edit',$id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

       dd('NÃO AUTORIZADO A EXCLUIR. PROCURE O ADMINISTRADOR DO SISTEMA!');
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

    public function CreateAvaliacaoFormandoBase(AvaliacaoFormandoBaseCreateRequest $request)
    {

        $id = $request->formandobase_id;

        // $existe = FormandoBaseAvaliacao::where('formandobase_id',$request->formandobase_id)
        // ->where('posicao_id',$request->posicao_id)
        // ->First();
        // if($existe){

        //     session(['error' => "Posição:  " . $existe->MostraPosicao->nome  .",  já existe para este registro!"]);
        //     return redirect(route('FormandoBase.edit', $id));
        // }

        $request['user_created'] = Auth ::user()->email;

        $avaliacao = str_replace(',', '.', $request['avaliacao']);
        $request['avaliacao'] =  $avaliacao;



        $model = $request->all();





        FormandoBaseAvaliacao::create($model);

        return redirect(route('FormandoBase.edit', $id));

    }



}
