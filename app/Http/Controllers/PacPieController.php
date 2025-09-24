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
use Illuminate\Support\Facades\Log;

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
        $perPage = (int) request()->query('perPage', 50);
        if ($perPage < 1) $perPage = 10;
        if ($perPage > 500) $perPage = 500;
        $query = Pacpie::query()->orderByDesc('id');
        $model = $query->paginate($perPage)->appends(['perPage' => $perPage]);
        return view('Pacpie.index', compact('model','perPage'));
    }

    public function BuscarTexto(Request $request)
    {
        // Armazena o texto da busca na sessão
        $request->session()->put('textoBusca', $request->Texto);

        $model = Pacpie::Where('nome', 'LIKE', '%' . $request->Texto . '%')
            ->OrWhere('email', 'LIKE', '%' . $request->Texto . '%')
            ->get();

        // dd('PACPIE INDEX', $model);

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
        $perPage = (int) $request->query('perPage', 100);
        if ($perPage < 1) $perPage = 25;
        if ($perPage > 1000) $perPage = 1000;
        $q = Pacpie::query();
        switch ($selecaoFiltro) {
            case 'SemPrimeiroContatoEmail':
                $q->whereNotNull('email')->whereNull('emailprimeirocontato')->whereNull('emailcomfalhas');
                break;
            case 'RetornoPrimeiroContatoEmail':
                $q->where('retornoemailprimeirocontato', true); break;
            case 'SemEmail':
                $q->whereNull('email'); break;
            case 'PromessaAporte':
                $q->where('promessa_aporte', true); break;
            case 'PromessaAporteValor':
                $q->where('promessa_aporte_valor', '>', 0); break;
            case 'Aportou':
                $q->where('aportou', true); break;
            case 'emailComFalha':
                $q->where('emailcomfalhas', true); break;
            case 'AportouValor':
                $q->where('aportou_valor', '>', 0); break;
            case 'SemNome':
                $q->where(function($sub){ $sub->whereNull('nome')->orWhere('nome',''); }); break;
            default:
                // sem filtro
                break;
        }
        $q->orderByDesc('id');
        $model = $q->paginate($perPage)->appends(['perPage'=>$perPage,'Selecao'=>$selecaoFiltro]);
        return view('Pacpie.index', compact('model','selecaoFiltro','perPage'));
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
        // Processa em chunks para não estourar memória
        Pacpie::whereNotNull('email')
            ->orderBy('id')
            ->chunkById(1000, function($registros){
                foreach ($registros as $item) {
                    $item->email = strtolower($item->email);
                    $item->nome = strtoupper($item->nome);
                    if (empty($item->email)) { $item->email = null; }
                    try { $item->save(); } catch (\Exception $e) {
                        Log::warning('AjustaCampos Pacpie falha', ['id'=>$item->id,'erro'=>$e->getMessage()]);
                    }
                }
            });
        session(['success' => 'Normalização concluída em lotes (chunk 1000).']);
        return redirect()->route('Pacpie.index');

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
        $whatsapp = $request->whatsapp;
        $aportevalor = $request->aporte_valor;


        $promessa_aporte_valor = $request->promessa_aporte_valor;
        $promessa_aporte_valor = str_replace('.', '', $promessa_aporte_valor);
        $promessa_aporte_valor = str_replace(',', '.', $promessa_aporte_valor);


        // dd($request->all(), $promessa_aporte_valor);
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
$request['recolhe_icms'] = $request->recolhe_icms;
$request['responsavel'] = strtoupper($request['responsavel']);
$request['origem_cadastro'] = $request['origem_cadastro'] ?? null;
$request['user_updated'] = Auth::user()->email;
$request['emailprimeirocontato'] = $request->emailprimeirocontato;
$request['retornoemailprimeirocontato'] = $request->retornoemailprimeirocontato;
$request['emailcomfalha'] = $request->emailcomfalha;
$request['promessa_aporte'] = $request->promessa_aporte;
$request['promessa_aporte_ano'] = $request->promessa_aporte_ano;
$request['promessa_aporte_valor'] = $promessa_aporte_valor;
$request['aportou'] = $request->aportou;
$request['aportou_ano'] = $request->aportou_ano;
$request['whatsapp'] = $whatsapp;
$request['aporte_valor'] = $request->aporte_valor;


$Valor = $request->input('aportou_valor');
// Remove pontos de milhar e substitui vírgula por ponto decimal
             $Valor = str_replace(".", "", $Valor);
             $Valor = str_replace(",", ".", $Valor);
             $ValorFloat = (float) $Valor; // Converte a string em um número de ponto flutuante
             $ValorDecimal = number_format($ValorFloat, 2, '.', ''); // Formata com duas casas decimais

             $request['aportou_valor'] = $ValorDecimal;


             $Valor = $request->input('promessa_aporte_valor');
             // Remove pontos de milhar e substitui vírgula por ponto decimal
                          $Valor = str_replace(".", "", $Valor);
                          $Valor = str_replace(",", ".", $Valor);
                          $ValorFloat = (float) $Valor; // Converte a string em um número de ponto flutuante
                          $ValorDecimal = number_format($ValorFloat, 2, '.', ''); // Formata com duas casas decimais

                          $request['promessa_aporte_valor'] = $ValorDecimal;

// dd('PARADO',$ValorDecimal);

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
