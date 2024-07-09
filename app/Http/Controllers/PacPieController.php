<?php

namespace App\Http\Controllers;

use App\Models\Pacpie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;
use App\Http\Requests\PacpieCreateRequest;
use App\Models\OrigemPacpie;
use Google\Service\AnalyticsData\OrderBy;
use Google\Service\ServiceControl\Auth as ServiceControlAuth;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
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

        $model = Pacpie::limit(0);

        // dd('PACPIE INDEX', $Pacpie);

        return view('Pacpie.index', compact('model'));
    }

    public function BuscarTexto(Request $request)
    {
        // Armazena o texto da busca na sessão
        $request->session()->put('textoBusca', $request->Texto);

        $model = Pacpie::Where('nome', 'LIKE', '%' . $request->Texto . '%')
            ->OrWhere('email', 'LIKE', '%' . $request->Texto . '%')

            ->get();

        // dd('PACPIE INDEX', $Pacpie);

        // return view('Pacpie.index', compact('model'));
        return view('Pacpie.index', compact('model'))->with('textoBusca', $request->Texto);
    }

    public function indexSelecao(Request $request)
    {
        $selecaoFiltro = $request->Selecao;

        if ($selecaoFiltro == null) {
            $request = session('request');


            $selecaoFiltro = $request['emailprimeirocontato'] ?? null;
        }
//    dd($request->all());
        if ($selecaoFiltro == 'SemPrimeiroContatoEmail') {
            ///////////////////////////////////FUNCIONANDO////////////////////////////////////////
            // $model = Pacpie::where(function($query) {
            //     $query->whereNull('emailprimeirocontato')
            //           ->orWhere('emailprimeirocontato', '=', false);
            // })

            // ->where(function($query) {
            //     $query->where('emailcomfalhas', '=', false);
            // })
            // ->whereNotNull('email')
            // ->get();
            ///////////////////////////////////FUNCIONANDO////////////////////////////////////////

            ///////////////////////////////////FUNCIONANDO////////////////////////////////////////
            $model = Pacpie::where(function ($query) {
                $query->whereNotNull('email')->orWhere('email', '=', '');
            })

                ->where(function ($query) {
                    $query->whereNull('emailprimeirocontato')->orWhere('emailprimeirocontato', '=', false)->where('emailcomfalhas', '=', false);
                })

                ->get();

            ///////////////////////////////////FUNCIONANDO////////////////////////////////////////
        }
        elseif ($selecaoFiltro == 'RetornoPrimeiroContatoEmail') {
            $model = Pacpie::where('retornoemailprimeirocontato', '=', true)->get();
        }
        elseif ($selecaoFiltro == 'SemEmail') {
            $model = Pacpie::where('email', '=', null)->get();
        }
        elseif ($selecaoFiltro == 'SemNome') {
            $model = Pacpie::whereNull('nome')->orWhere('nome', '=', '')->orderBy('nome', 'desc')->get();
        } else {
            $model = Pacpie::all();
        }

        // dd('PACPIE INDEX', $Pacpie);

        return view('Pacpie.index', compact('model'));
    }

    public function create()
    {
        $OrigemPacpie = OrigemPacpie::orderBy('Nome')->get();

        return view('Pacpie.create', compact('OrigemPacpie'));
    }

    public function store(PacpieCreateRequest $request)
    {
        $cnpj = $request->cnpj;

        $LiberaCNPJ = $request->liberacnpj;

        $limpacnpj = $request->limpacnpj;

        $request['nome'] = strtoupper($request['nome']);
        $id = $request->id;

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

        $request->session()->put('textoBusca', $request['nome']);

        Pacpie::create($model);
        return redirect(route('Pacpie.index'));
        // return view('Pacpie.index', compact('model'))->with('textoBusca', $request->Texto);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Pacpie::find($id);

        return view('Pacpie.show', compact('cadastro'));
    }


    public function AjustaCampos()
    {

        $model = Pacpie::whereNotNull('email')->get();

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
        return redirect(route('Pacpie.index', compact('model')));
        // return view('Pacpie.index', compact('model'));

    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // session(['Representante_id' => $id]);

        $model = Pacpie::find($id);
        $OrigemPacpie = OrigemPacpie::orderBy('Nome')->get();

        $retorno['origem_cadastro'] = $model->origem_cadastro ?? null;

        // dd($model, $retorno['origem_cadastro']);

        return view('Pacpie.edit', compact('model', 'OrigemPacpie', 'retorno'));
    }

    /*
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
        // $OrigemPacpie = $request->origem_cadastro;

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


        $request['email'] = $emailCorrigido;



$cadastro = Pacpie::find($id);

$request['nome'] = strtoupper($request['nome']);
$request['responsavel'] = strtoupper($request['responsavel']);
$request['origem_cadastro'] = $request['origem_cadastro'] ?? null;
$request['user_updated'] = Auth::user()->email;
$request['emailprimeirocontato'] = $request->emailprimeirocontato;
$request['retornoemailprimeirocontato'] = $request->retornoemailprimeirocontato;
$request['emailcomfalha'] = $request->emailcomfalha;
$request['promessa_aporte'] = $request->promessa_aporte;
$request['promessa_aporte_ano'] = $request->promessa_aporte_ano;
$request['aportou'] = $request->aportou;
$request['aportou_ano'] = $request->aportou_ano;



// Adicione a data e o usuário às novas informações de observação
$dataAtual = date('d-m-Y H:i:s');
$usuario = Auth::user()->email;

$observacaoNova = $request->observacaonova ?? 'Anotação não informada!';
$novaObservacao = "[Data: $dataAtual - Usuário: $usuario]: Nova anotação: $observacaoNova";

// $novaObservacao = "[Data:   $dataAtual - Usuário: . $usuario]: " . ' Nova anotação: '. $request->observacaonova ??  'Anotação não informada!';

// Concatenar as novas informações com as informações anteriores
$observacaoAnterior = $cadastro->observacao;
$observacaoAtualizada = $novaObservacao . "\n\n" . $observacaoAnterior ;

// Atualizar a observação no request
$request['observacao'] = $observacaoAtualizada;

$cadastro->fill($request->all());
$cadastro->save();




        session(['success' => 'NOME:  ' . $request->nome . ', ALTERADO! ']);
        return redirect(route('Pacpie.edit', $id));

        // return redirect(route('Pacpie.index'));

        // return view('Pacpie/go-back-twice-and-refresh');
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

        return redirect(route('Representantes.edit', $request->RedeSocialRepresentante_id));
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

    public function MarcaEnviadoemailparaprimeirocontato(Request $request)
    {
        $id = $request->id;
        $cadastro = Pacpie::find($id);

        //  dd(274, $request->all(), $request->id,  $cadastro);

        $request['user_updated'] = Auth::user()->email;
        $request['emailprimeirocontato'] = true;
        $request['emailcomfalhas'] = false;

        $cadastro->fill($request->all());
        $cadastro->save();
        // return view('Pacpie/go-back-twice-and-refresh');
        return redirect()
            ->route('Pacpie.indexSelecao')
            ->with([
                'request' => $request->all(),
            ]);
    }

    public function MarcaRetornoEnviadoemailparaprimeirocontato(Request $request)
    {
        $id = $request->id;
        $cadastro = Pacpie::find($id);

        //  dd(274, $request->all(), $request->id,  $cadastro);

        $request['user_updated'] = Auth::user()->email;
        $request['retornoemailprimeirocontato'] = true;


        $cadastro->fill($request->all());
        $cadastro->save();
        // return view('Pacpie/go-back-twice-and-refresh');
        return redirect()
            ->route('Pacpie.indexSelecao')
            ->with([
                'request' => $request->all(),
            ]);
    }




    public function Marcaemailcomfalhas(Request $request)
    {
        $id = $request->id;
        $cadastro = Pacpie::find($id);
        $request['user_updated'] = Auth::user()->email;
        $request['emailcomfalhas'] = true;
        $request['emailprimeirocontato'] = false;
        $cadastro->fill($request->all());
        $cadastro->save();

        // dd($cadastro);
        // return view('Pacpie/go-back-twice-and-refresh');
        return redirect(route('Pacpie.indexSelecao'));
    }
}
