<?php

namespace App\Http\Controllers;


use App\Models\ContasPagar;
use App\Helpers\SaldoLancamentoHelper;
use App\Helpers\FinancaHelper;
use App\Http\Requests\ArquivoContasPagarCreateRequest;
use App\Http\Requests\CentroCustosCreateRequest;
use App\Http\Requests\ContasCentroCustosCreateRequest;
use App\Http\Requests\ContasPagarCreateRequest;
use App\Models\CentroCustos;
use App\Models\Conta;
use App\Models\ContasCentroCustos;
use App\Models\ContasPagarArquivo;
use App\Models\Empresa;
use App\Models\Feriado;
use App\Models\Lancamento;
use App\Models\LancamentoDocumento;
use App\Models\PlanoConta;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Calculation\Engineering\ConvertDecimal;
use Ramsey\Uuid\Type\Decimal;

class ContasPagarController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware(['permission:CONTASCENTROCUSTOS - DASHBOARD'])->only('dashboard');
        $this->middleware(['permission:CONTASPAGAR - LISTAR'])->only('index');
        $this->middleware(['permission:CONTASPAGAR - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:CONTASPAGAR - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:CONTASPAGAR - VER'])->only(['show',]);
        $this->middleware(['permission:CONTASPAGAR - EXCLUIR'])->only('destroy');
    }


    public function index(Request $request)
    {

        $Texto =  session('Texto');

    $perPage = (int) ($request->query('Limite', 25)); // paginação padrão (ajustável via query)
    if ($perPage <= 0 || $perPage > 1000) { $perPage = 25; }
        $contasPagar = ContasPagar::with([
        'Empresa',
        'ContaDebito.PlanoConta',
        'ContaCredito.PlanoConta',
        ])
        ->join('Contabilidade.EmpresasUsuarios', 'ContasPagar.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
        ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        ->select(['ContasPagar.*'])
            ->orderBy('ContasPagar.Created', 'desc')
            ->paginate($perPage)
            ->withQueryString();


        if ($contasPagar->count() > 0) {
            session(['entrada' => 'A pesquisa abaixo mostra os 100 últimos lançamentos de todas as empresas autorizadas!']);
            session(['success' => '']);
            session(['error' => '']);
        } else {
            session(['error' => 'Nenhum lançamento encontrado para as empresas autorizadas!']);
        }

        $retorno['DataInicial'] = date('Y-m-d');
        $retorno['DataFinal'] = date('Y-m-d');
        $retorno['EmpresaSelecionada'] = null;

        $retorno['Texto'] = $Texto;

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        return view('ContaPagar.index', compact('contasPagar', 'retorno', 'Empresas'));
        // return redirect()->route('contaspagar.index', ['Texto' => $Texto])
        // ->with([
        //     'contasPagar' => $contasPagar,
        //     'retorno' => $retorno,
        //     'Empresas' => $Empresas,
        // ]);


    }

    public function indexpost(Request $Request)
    {
        $CompararDataInicial = $Request->DataInicial;

    $limit = (int) ($Request->Limite ?? 25); // itens por página
    if ($limit <= 0 || $limit > 1000) { $limit = 25; }

        $contasPagar = ContasPagar::with([
                'Empresa',
                'ContaDebito.PlanoConta',
                'ContaCredito.PlanoConta',
            ])
            ->join('Contabilidade.EmpresasUsuarios', 'ContasPagar.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->select(['ContasPagar.*']);

        if ($Request->Texto) {
            $texto = $Request->Texto;
            $contasPagar->where(function ($query) use ($texto) {
                return $query->where('ContasPagar.Descricao', 'like', '%' . $texto . '%');
            });
        }

        if ($Request->Valor) {
            $contasPagar->where('ContasPagar.Valor', '>=', $Request->Valor);
        }

        if ($Request->DataInicial) {
            $DataInicial = Carbon::createFromFormat('Y-m-d', $Request->DataInicial);
            $contasPagar->whereDate('ContasPagar.DataProgramacao', '>=', $DataInicial->format('Y-m-d'));
        }

        if ($Request->DataFinal) {
            $DataFinal = Carbon::createFromFormat('Y-m-d', $Request->DataFinal);
            $contasPagar->whereDate('ContasPagar.DataProgramacao', '<=', $DataFinal->format('Y-m-d'));
        }

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        $retorno = $Request->all();

        if ($contasPagar->count() > 0) {
            session(['success' => 'A pesquisa abaixo mostra os lançamentos de todas as empresas autorizadas conforme a pesquisa proposta!']);
        } else {
            session(['error' => 'Nenhum lançamento encontrado para as empresas autorizadas!']);
        }

        if ($Request->DataInicial && $Request->DataFinal) {
            if ($DataInicial > $DataFinal) {
                session(['error' => 'Data de início MAIOR que a final. VERIFIQUE!']);
                // Retorna com paginação mesmo em caso de erro de datas
                $contasPagar = $contasPagar->orderBy('ContasPagar.Created', 'desc')
                    ->paginate($limit)
                    ->appends($Request->all());
                return view('ContaPagar.index', compact('contasPagar', 'retorno', 'Empresas'));
            }
        }

        if ($Request->EmpresaSelecionada) {
            $contasPagar->where('ContasPagar.EmpresaID', $Request->EmpresaSelecionada);
        }
        // Ordenação e limite aplicados por último
        if ($Request->Valor) {
            $contasPagar = $contasPagar->orderBy('ContasPagar.Valor', 'asc');
        } else {
            $contasPagar = $contasPagar->orderBy('ContasPagar.Created', 'desc');
        }

        $contasPagar = $contasPagar->paginate($limit)->appends($Request->all());



        return view('ContaPagar.index', compact('contasPagar', 'retorno', 'Empresas'));
    }


    public function alterarvalormultiplos(Request $request)
    {
            // Receber os dados do formulário
            $contasPagar = $request->input('contasPagar'); // Array com IDs das contas a serem alteradas
            $valor = $request->input('Valor');

            // Converte do formato brasileiro para o formato padrão (3333.32)
            $valor = str_replace('.', '', $valor); // Remove os pontos
            $valor = str_replace(',', '.', $valor); // Substitui vírgula por ponto
            $valor = floatval($valor); // Converte a string em um número de ponto flutuante
            // Formata o valor com ponto como separador decimal
            $valor = number_format($valor, 2, ',', '');

            $valorAlterar = $request->input('ValorAlterar');
            $valorAlterar = str_replace('.', '', $valorAlterar);
            $valorAlterar = str_replace(',', '.', $valorAlterar);

            $Texto = $request->input('Texto');
            session(['success' => 'Nenhum valor alterado!']);
            foreach ($contasPagar as $conta) {
                $jsonString = $conta;
                // Decodifica o JSON em um array associativo
                $data = json_decode($jsonString, true);
                // Acessa o campo 'ID'
                $id = $data['ID'];
                session(['Texto' => $data['Descricao']]);
                $contaPagar = ContasPagar::find($id);

                // dd('Antes de ser alterado...', $valorAlterar, $data['Valor'], $valor);

                if ($contaPagar) {
                    $ValorRegistrado = $data['Valor'];

                   if($valorAlterar == $ValorRegistrado)
                   {
                        $valor = str_replace(',', '.', $valor); // Troca vírgula por ponto
                        $valor = floatval($valor); // Converte para float
                        $valorFormatado = number_format($valor, 2, '.', '');

                        //  dd('Sendo alterado...',$valorAlterar, $data['Valor'], $valor);
                        $contaPagar->update(['Valor' => $valor]);
                        $contaPagar->save();

                        $lancamento = lancamento::where('ID', $contaPagar->LancamentoID)->first();



                        if($lancamento)
                        {
                            $lancamento->update(['Valor' => $valor]);
                            $lancamento->save();
                        }
                        // dd($lancamento, $contaPagar->LancamentoID);
                        session(['success' => 'Valor alterado com sucesso!']);
                   }
                }
            }

            return redirect()->route('ContasPagar.index');
    }


    public function create()
    {
        $contasPagar = new ContasPagar;


        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();


        $ContaFornecedor = Conta::join('Contabilidade.PlanoContas', 'PlanoContas.ID', '=', 'Contas.Planocontas_id')
            ->join('Contabilidade.Empresas', 'Empresas.ID', '=', 'Contas.EmpresaID')
            ->where('Contabilidade.PlanoContas.Grau', '=', '5')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%Aplicação%')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%Subscricao%')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%transferencia%')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%modobank%')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%poupanca%')
            ->select('Contas.ID', DB::raw("CONCAT(PlanoContas.Descricao,' | ', Empresas.Descricao) as Descricao"))
            ->orderby('PlanoContas.Descricao')
            ->get();

        $ContaPagamento = Conta::join('Contabilidade.PlanoContas', 'PlanoContas.ID', '=', 'Contas.Planocontas_id')
            ->join('Contabilidade.Empresas', 'Empresas.ID', '=', 'Contas.EmpresaID')
            ->where('Contabilidade.PlanoContas.Grau', '=', '5')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%Aplicação%')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%Subscricao%')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%transferencia%')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%modobank%')
            ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%poupanca%')
            ->select('Contas.ID', DB::raw("CONCAT(PlanoContas.Descricao,' | ', Empresas.Descricao) as Descricao"))
            ->orderby('PlanoContas.Descricao')
            ->get();


        return view('ContaPagar.create', compact('contasPagar', 'Empresas', 'ContaFornecedor', 'ContaPagamento'));
    }

    public function store(ContasPagarCreateRequest $request)
    {

        // $Existe = ContasCentroCustos::where('CentroCustoID','=', $request->CentroCustoID)
        // ->where('ContaID', '=', $request->ContaID)
        // ->first();


        // if($Existe){
        //     session(['success' => ' Registro já inserido. '
        //     .$request->ContaID. ': '. $Existe->MostraContaCentroCusto->PlanoConta->Descricao
        //     . ' em '.$request->CentroCustoID.': '. $Existe->MostraCentroCusto->Descricao]
        // );
        //     return redirect(route('ContasCentroCustos.create'));

        // }


        // $contasPagar = $request->all();



        $EmpresaSelecionada = $request->input('EmpresaID');

        $EmpresaBloqueada = Empresa::where('ID', '=', $request->EmpresaID)->first();

        session(['NomeEmpresa' => $EmpresaBloqueada->Descricao]);

        session(['EmpresaID' =>  $request->input('EmpresaID')]);
        session(['ContaFornecedorID' => $request['ContaFornecedorID']]);
        session(['ContaPagamentoID' => $request['ContaPagamentoID']]);


        $ContasPagar = $request->input('EmpresaID');

        /////////////////////////////////////////////////////////////////////////////////////////////// feriado e dia da semana
        $DataContabilidade = $request->input('DataProgramacao');
        if ($DataContabilidade) {
            // Ajusta para próximo dia útil com cache de feriados por ano (reduz roundtrips)
            $DataContabilidade = $this->nextBusinessDay($DataContabilidade);
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////
        $ContaDebito = Conta::find($request->ContaFornecedorID);
        $ContaCredito = Conta::find($request->ContaPagamentoID);



        if ($ContaDebito->EmpresaID != $EmpresaSelecionada) {

            session(['error' => 'A contas DÉBITO não pertence a empresa!']);
            return back();
        }

        if ($ContaCredito->EmpresaID != $EmpresaSelecionada) {
            session(['error' => 'A contas CRÉDITO não pertence a empresa!']);
            return back();
        }


        $data_lancamento_bloqueio_debito = $ContaDebito->Bloqueiodataanterior;




        if ($data_lancamento_bloqueio_debito !== null && $data_lancamento_bloqueio_debito->greaterThanOrEqualTo($DataContabilidade)) {

            session([
                'Lancamento' =>
                'Conta DÉBITO: ' .
                    $ContaDebito->PlanoConta->Descricao .
                    ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                     da conta para seguir este procedimento. Bloqueada para até ' .
                    $data_lancamento_bloqueio_debito->format('d/m/Y') .  '  - CÓDIGO L275'
            ]);
            return back();
            // return redirect()->route('ContasPagar.create');
        }

        $data_lancamento_bloqueio_credito = $ContaCredito->Bloqueiodataanterior;
        if ($data_lancamento_bloqueio_credito !== null && $data_lancamento_bloqueio_credito->greaterThanOrEqualTo($DataContabilidade)) {
            // O código aqui será executado se $data_lancamento_bloqueio_debito não for nulo e for maior ou igual a $DataContabilidade
            session([
                'Lancamento' =>
                'Conta CRÉDITO: ' .
                    $ContaCredito->PlanoConta->Descricao .
                    ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                     da conta para seguir este procedimento. Bloqueada para até ' .
                    $data_lancamento_bloqueio_credito->format('d/m/Y') .  '  - CÓDIGO L290'
            ]);
            return back();
            // return redirect()->route('ContasPagar.create');
        }


        // $EmpresaBloqueada = Empresa::where('ID', '=', $request->EmpresaID)->first();

        // session(['NomeEmpresa' => $EmpresaBloqueada->Descricao]);
        $data_lancamento_bloqueio_empresa = $EmpresaBloqueada->Bloqueiodataanterior;

        $dataLimite = $data_lancamento_bloqueio_empresa;

        if ($DataContabilidade <= $dataLimite) {
            // A data de lançamento é maior do que a data limite permitida
            session([
                'Lancamento' =>
                'A data de lançamento não pode ser MENOR ou IGUAL a ' . $data_lancamento_bloqueio_empresa->format('d/m/Y') . ' que é a data limite do bloqueio. - CÓDIGO L308'
            ]);
            return back();
            // return redirect()->route('ContasPagar.create');
        }

        if ($data_lancamento_bloqueio_empresa->greaterThanOrEqualTo($DataContabilidade)) {
            session([
                'Lancamento' =>
                'Conta DÉBITO: ' .
                    $EmpresaBloqueada->Descricao .
                    ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                     da empresa para seguir este procedimento. Bloqueada para até ' .
                    $EmpresaBloqueada .  '  - CÓDIGO L321'

            ]);
            return back();
            // return redirect()->route('ContasPagar.create');
        }



        $request['ContaFornecedorID'] = $ContaDebito->ID;
        $request['ContaPagamentoID'] = $ContaCredito->ID;
        $request['UsuarioID'] = Auth::user()->id;
        $request['Created'] = Carbon::now()->format('Y-m-d');
        $request['DataProgramacao'] = $DataContabilidade;


        $contasPagar = collect($request->all());

        $request['Valor'] = str_replace(",", ".", str_replace('.', '', $request['Valor']));



        // dd(session['Empresalecionada'], session['ContaFornecedorID'], session['ContaPagamentoID']);

        $data = [
            'EmpresaID' => $request->input('EmpresaID'),
            'Descricao' => $request->input('Descricao'),
            'Valor' => $request->input('Valor'),
            'DataProgramacao' => $request->input('DataProgramacao'),
            'DataVencimento' => $request->input('DataVencimento'),
            'DataDocumento' => $request->input('DataDocumento'),
            'NumTitulo' => $request->input('NumTitulo'),
            'ContaFornecedorID' => $request->input('ContaFornecedorID'),
            'ContaPagamentoID' => $request->input('ContaPagamentoID'),
            'UsuarioID' => $request->input('UsuarioID'),
            'Created' => $request->input('Created'),
        ];

        $ContasPagar = $data;
        ContasPagar::create($data);

        session(['success' => 'Conta a pagar inserida com sucesso!']);

        return redirect()->route('ContasPagar.index');
    }

    /**
     * Calcula o próximo dia útil (pula fins de semana e feriados) com cache anual de feriados.
     * Entrada/saída no formato 'Y-m-d'.
     */
    private function nextBusinessDay(string $dateYmd): string
    {
        $carbon = Carbon::createFromFormat('Y-m-d', $dateYmd);

        // Função para carregar feriados de um ano, cacheando por 1h
        $loadYear = function (int $year) {
            return Cache::remember("feriados_{$year}", 3600, function () use ($year) {
                return Feriado::whereYear('data', $year)
                    ->pluck('data')
                    ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                    ->toArray();
            });
        };

        $feriados = $loadYear((int)$carbon->year);

        while (in_array($carbon->format('Y-m-d'), $feriados, true) || $carbon->isWeekend()) {
            $carbon->addDay();
            // Se virar o ano, recarrega feriados
            if (!array_key_exists($carbon->year, [ (int)$carbon->year => true ])) {
                // no-op, apenas para clareza
            }
            if ($carbon->isStartOfYear()) {
                $feriados = $loadYear((int)$carbon->year);
            }
        }

        return $carbon->format('Y-m-d');
    }

    public function show($id)
    {
        // Lógica para exibir um registro específico
    }

    public function edit($id, Request $request)
    {


        session(['ContaPagarID' => $id ]);

        // dd(trim(session('ContaPagarID')));

        // $id = $request->ID;
        // Eager load to avoid N+1 on view (ContaDebito/ContaCredito -> PlanoConta)
        $contasPagar = ContasPagar::with([
            'ContaDebito.PlanoConta',
            'ContaCredito.PlanoConta',
            'Empresa',
        ])->find($id);

        if ($contasPagar == null) {

            session(['error' => 'ID não localizado. VERIFIQUE! CÓDIGO L385']);

            return redirect()->route('ContasPagar.index');
        }



        // Observação: a listagem de empresas não é utilizada nesta view; evitando consulta adicional


        // Dropdowns de contas: adicionar filtro de Grau=5 e cache leve para reduzir latência
        $empresaId = $contasPagar->EmpresaID;
        $ContaFornecedor = Cache::remember("contas_fornecedor_{$empresaId}", 300, function () use ($empresaId) {
            return Conta::join('Contabilidade.PlanoContas', 'PlanoContas.ID', '=', 'Contas.Planocontas_id')
                ->join('Contabilidade.Empresas', 'Empresas.ID', '=', 'Contas.EmpresaID')
                ->where('Contas.EmpresaID', '=', $empresaId)
                ->where('Contabilidade.PlanoContas.Grau', '=', '5')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%Aplicação%')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%Subscricao%')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%transferencia%')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%modobank%')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%poupanca%')
                ->select('Contas.ID', DB::raw("CONCAT(PlanoContas.Descricao,' | ', Empresas.Descricao) as Descricao"))
                ->orderBy('PlanoContas.Descricao')
                ->get();
        });


        $ContaPagamento = Cache::remember("contas_pagamento_{$empresaId}", 300, function () use ($empresaId) {
            return Conta::join('Contabilidade.PlanoContas', 'PlanoContas.ID', '=', 'Contas.Planocontas_id')
                ->join('Contabilidade.Empresas', 'Empresas.ID', '=', 'Contas.EmpresaID')
                ->where('Contas.EmpresaID', '=', $empresaId)
                ->where('Contabilidade.PlanoContas.Grau', '=', '5')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%Aplicação%')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%Subscricao%')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%transferencia%')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%modobank%')
                ->where('Contabilidade.PlanoContas.Descricao', 'not like', '%poupanca%')
                ->select('Contas.ID', DB::raw("CONCAT(PlanoContas.Descricao,' | ', Empresas.Descricao) as Descricao"))
                ->orderBy('PlanoContas.Descricao')
                ->get();
        });

            // Tipos de documentos: cache reduz reprocessamento; carregar apenas colunas necessárias
            $documento = Cache::remember('lancamento_documentos_tipo_ativos', 300, function () {
                return LancamentoDocumento::with('TipoArquivoNome')
                    ->select('ID', 'Rotulo', 'tipoarquivo')
                    ->where('tipoarquivo', '>', 0)
                    ->orderBy('ID', 'desc')
                    ->get();
            });

            // Anexos do Contas a Pagar: eager load para evitar N+1 no blade
            $ContasPagarArquivo = ContasPagarArquivo::with(['MostraLancamentoDocumento', 'MostraLancamentoDocumento.TipoArquivoNome'])
                ->where('contaspagar_id', '=', $id)
                ->orderBy('id')
                ->get();

            // Sinalizar se há algum arquivo já associado (evita loop manual)
            $arquivoExiste = $ContasPagarArquivo->isNotEmpty() ? optional($ContasPagarArquivo->last())->id : null;


    return view('ContaPagar.edit', compact('contasPagar', 'id', 'ContaFornecedor', 'ContaPagamento','documento','arquivoExiste', 'ContasPagarArquivo'));
    }

    public function update(Request $request, string $id)
    {

        $contasPagar = ContasPagar::find($id);

        // if($request->input('LancamentoID') == null){
        if($contasPagar->LancamentoID == null){
            // dd($request->input('LancamentoID'));
            $contasPagar->update([

                'LancamentoID' =>  " ",
              ]);

            $contasPagar->save();

            return redirect()->route('ContasPagar.edit', $id)
                ->with('success', 'Conta a pagar atualizada com sucesso! Não foi localizado o ID do lançamento! VERIFICAR LINHA 464')
                ->with('error', null);
        }



        if (!$contasPagar) {
            dd('Conta a pagar não encontrada!', 'ID: ' . $id, 'ContasPagarController@update');
        }

        /////////////////////////////////////////////////////////////////////////////////////////////// feriado e dia da semana
        $DataContabilidade = $request->input('DataProgramacao');
        if ($DataContabilidade) {
            $carbonData = Carbon::createFromFormat('Y-m-d', $DataContabilidade);
            $dataContabilidade = $carbonData->format('d/m/Y');
        } else {
            $dataContabilidade = null;
        }

        $feriado = Feriado::where('data', $carbonData)->first();
        while ($feriado) {
            $carbonData->addDay(1);
            $feriado = Feriado::where('data', $carbonData->format('Y-m-d'))->first();
        }

        $diasemana = date('l', strtotime($DataContabilidade));

        if ($diasemana == 'Saturday') {
            $carbonData->addDay(2);
        }
        if ($diasemana == 'Sunday') {
            $carbonData->addDay(1);
        }
        $DataContabilidade = $carbonData->format('Y-m-d');
        $request['DataProgramacao'] = $DataContabilidade;

        ///////////////////////////////////////////////////////////////////////////////////////////////



        $LancamentoID = $contasPagar->LancamentoID;

        if($LancamentoID>0)
        {
           $Lancamento = Lancamento::find($LancamentoID);
        }
       else
       {
           $LancamentoID = $request->LancamentoID;
           $Lancamento = Lancamento::find($LancamentoID);
        }


        // dd($LancamentoID);
        if ($Lancamento) {
            $DataContabilidade = $request->input('DataProgramacao');
            if ($DataContabilidade) {
                $carbonData = Carbon::createFromFormat('Y-m-d', $DataContabilidade);
                $dataContabilidade = $carbonData->format('d/m/Y');
            } else {
                $dataContabilidade = null; // Define $dataContabilidade como nulo se a data da solicitação for nula
            }


            $data_lancamento_bloqueio_debito = $contasPagar->ContaDebito->Bloqueiodataanterior;


            if ($data_lancamento_bloqueio_debito !== null && $data_lancamento_bloqueio_debito->greaterThanOrEqualTo($DataContabilidade)) {
                // O código aqui será executado se $data_lancamento_bloqueio_debito não for nulo e for maior ou igual a $DataContabilidade
                session([
                    'Lancamento' =>
                    'Conta DÉBITO: ' .
                        $contasPagar->ContaDebito->PlanoConta->Descricao .
                        ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                     da conta para seguir este procedimento. Bloqueada para até ' .
                        $data_lancamento_bloqueio_debito->format('d/m/Y') .  '  - CÓDIGO L492'
                ]);
                return redirect()->route('ContasPagar.edit', $id);
            }

            $data_lancamento_bloqueio_credito = $contasPagar->ContaCredito->Bloqueiodataanterior;
            if ($data_lancamento_bloqueio_credito !== null && $data_lancamento_bloqueio_credito->greaterThanOrEqualTo($DataContabilidade)) {
                // O código aqui será executado se $data_lancamento_bloqueio_debito não for nulo e for maior ou igual a $DataContabilidade
                session([
                    'Lancamento' =>
                    'Conta CRÉDITO: ' .
                        $contasPagar->ContaCredito->PlanoConta->Descricao .
                        ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                     da conta para seguir este procedimento. Bloqueada para até ' .
                        $data_lancamento_bloqueio_credito->format('d/m/Y') .  '  - CÓDIGO L506'
                ]);
                return redirect()->route('ContasPagar.edit', $id);
            }


            $EmpresaBloqueada = Empresa::where('ID', '=', $Lancamento->EmpresaID)->first();

            $data_lancamento_bloqueio_empresa = $EmpresaBloqueada->Bloqueiodataanterior;
            $dataLimite = $data_lancamento_bloqueio_empresa;

            if ($DataContabilidade <= $dataLimite) {
                // A data de lançamento é maior do que a data limite permitida
                session([
                    'Lancamento' =>
                    'A data de lançamento não pode ser maior do que ' . $data_lancamento_bloqueio_empresa->format('d/m/Y') . ' que é a data limite do bloqueio. - CÓDIGO L521'
                ]);
                return redirect()->route('ContasPagar.edit', $id);
            }

            if ($data_lancamento_bloqueio_empresa->greaterThanOrEqualTo($DataContabilidade)) {
                // Data da empresa bloqueada
                session([
                    'Lancamento' =>
                    'EMPRESA BLOQUEADA: ' .
                        $EmpresaBloqueada->Descricao .
                        ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
             da empresa para seguir este procedimento. Bloqueada para até ' .
                        $EmpresaBloqueada .  '  - CÓDIGO L534'
                ]);
                return redirect()->route('ContasPagar.edit', $id);
            }



            $Valor = $request->input('Valor');
// Remove pontos de milhar e substitui vírgula por ponto decimal
             $Valor = str_replace(".", "", $Valor);
             $Valor = str_replace(",", ".", $Valor);
             $ValorFloat = (float) $Valor; // Converte a string em um número de ponto flutuante
             $ValorDecimal = number_format($ValorFloat, 2, '.', ''); // Formata com duas casas decimais




            $Lancamento->update([
                'Descricao' => $request->input('Descricao'),
                'Valor' => $ValorDecimal,
                'DataContabilidade' =>  $DataContabilidade,
                'NumTitulo' => $request->input('NumTitulo') ?? null,
                'ContaDebitoID' => $request->input('ContaFornecedorID') ?? null,
                'ContaCreditoID' => $request->input('ContaPagamentoID') ?? null,
                'Usuarios_id' => $auth = Auth::user()->id,
                'LancamentoID' => $contasPagar->IDDocumentoEmpresa,
                'Created' => $now = Carbon::now()->format('Y-m-d'),
            ]);
            $Lancamento->save();


            // dd($Lancamento);
        } else {;
            session(['contabilidade' => 'Lançamento não encontrado na contabilidade!']);
        };

        $request['Valor'] = str_replace(",", ".", str_replace('.', '', $request['Valor']));




        $contasPagar->update([
            'Descricao' => $request->input('Descricao'),
            'Valor' => $request->input('Valor'),
            'DataProgramacao' => $request->input('DataProgramacao') ?? null,
            'DataVencimento' => $request->input('DataVencimento')   ?? null,
            'DataDocumento' =>  $request->input('DataDocumento') ?? null,
            'NumTitulo' => $request->input('NumTitulo') ?? null,
            'ContaFornecedorID' => $request->input('ContaFornecedorID') ?? null,
            'LancamentoID' => $LancamentoID,
            'ContaPagamentoID' => $request->input('ContaPagamentoID') ?? null,
        ]);

        $contasPagar->save();

        return redirect()->route('ContasPagar.edit', $id)
            ->with('success', 'Conta a pagar atualizada com sucesso!')
            ->with('error', null);
    }

    public function destroy($id)
    {
        // Lógica para excluir um registro específico
    }

    public function IncluirLancamentoContasPagar($id)
    {

        $contasPagar = ContasPagar::find($id);

        if ($contasPagar == null) {

            session(['error' => 'ID não localizado. VERIFIQUE! CÓDIGO L587']);

            return redirect()->route('ContasPagar.index');
        }



        $LancamentoID = $contasPagar->LancamentoID;

        $Lancamento = Lancamento::find($LancamentoID);
        if (!$Lancamento) {
            $DataContabilidade = $contasPagar->DataProgramacao;
            if ($DataContabilidade) {
                $carbonData = Carbon::createFromFormat('Y-m-d', $DataContabilidade);
                $dataContabilidade = $carbonData->format('d/m/Y');
            } else {
                $dataContabilidade = null; // Define $dataContabilidade como nulo se a data da solicitação for nula
            }




            $data_lancamento_bloqueio_debito = $contasPagar->ContaDebito->Bloqueiodataanterior;


            if ($data_lancamento_bloqueio_debito !== null && $data_lancamento_bloqueio_debito->greaterThanOrEqualTo($DataContabilidade)) {
                // O código aqui será executado se $data_lancamento_bloqueio_debito não for nulo e for maior ou igual a $DataContabilidade
                session([
                    'Lancamento' =>
                    'Conta DÉBITO: ' .
                        $contasPagar->ContaDebito->PlanoConta->Descricao .
                        ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                     da conta para seguir este procedimento. Bloqueada para até ' .
                        $data_lancamento_bloqueio_debito->format('d/m/Y') .  '  - CÓDIGO L618'
                ]);
                return redirect()->route('ContasPagar.edit', $id);
            }

            $data_lancamento_bloqueio_credito = $contasPagar->ContaCredito->Bloqueiodataanterior;
            if ($data_lancamento_bloqueio_credito !== null && $data_lancamento_bloqueio_credito->greaterThanOrEqualTo($DataContabilidade)) {
                // O código aqui será executado se $data_lancamento_bloqueio_debito não for nulo e for maior ou igual a $DataContabilidade
                session([
                    'Lancamento' =>
                    'Conta CRÉDITO: ' .
                        $contasPagar->ContaCredito->PlanoConta->Descricao .
                        ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                     da conta para seguir este procedimento. Bloqueada para até ' .
                        $data_lancamento_bloqueio_credito->format('d/m/Y') .  '  - CÓDIGO L597'
                ]);
                return redirect()->route('ContasPagar.edit', $id);
            }


            $EmpresaBloqueada = Empresa::where('ID', '=', $contasPagar->EmpresaID)->first();

            $data_lancamento_bloqueio_empresa = $EmpresaBloqueada->Bloqueiodataanterior;
            $dataLimite = $data_lancamento_bloqueio_empresa;

            if ($DataContabilidade < $dataLimite) {
                // A data de lançamento é maior do que a data limite permitida
                session([
                    'Lancamento' =>
                    'A data de lançamento não pode ser menor do que ' . $data_lancamento_bloqueio_empresa->format('d/m/Y') . ' que é a data limite do bloqueio. - CÓDIGO L647'
                ]);
                return redirect()->route('ContasPagar.edit', $id);
            }

            if ($data_lancamento_bloqueio_empresa->greaterThanOrEqualTo($DataContabilidade)) {
                // Data da empresa bloqueada
                session([
                    'Lancamento' =>
                    'EMPRESA BLOQUEADA: ' .
                        $EmpresaBloqueada->Descricao .
                        ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
             da empresa para seguir este procedimento. Bloqueada para até ' .
                        $EmpresaBloqueada .  '  - CÓDIGO L660'
                ]);
                return redirect()->route('ContasPagar.edit', $id);
            }


            $Lancamento = [
                'EmpresaID' => $contasPagar->EmpresaID,
                'Descricao' => $contasPagar->Descricao,
                'Valor' => $contasPagar->Valor,
                'DataContabilidade' =>  $DataContabilidade,
                'NumTitulo' => $contasPagar->NumTitulo,
                'ContaDebitoID' => $contasPagar->ContaFornecedorID,
                'ContaCreditoID' => $contasPagar->ContaPagamentoID,
                'LancamentoID' => $contasPagar->LancamentoID,
                'Created' => $contasPagar->Created,
                'Usuarios_id' => Auth::user()->id,
                'Created' => Carbon::now()->format('Y-m-d'),
            ];




            // Atenção: para SQL Server, evitar comparar datas no formato 'd/m/Y'.
            // Use sempre 'Y-m-d' (ISO) ou objeto Carbon. Aqui usamos $DataContabilidade (Y-m-d).
            $duplicadoconsulta = Lancamento::where([
                'EmpresaID' => $contasPagar->EmpresaID,
                'Descricao' => $contasPagar->Descricao,
                'Valor' => $contasPagar->Valor,
                'DataContabilidade' => $DataContabilidade,
                'ContaDebitoID' => $contasPagar->ContaFornecedorID,
                'ContaCreditoID' => $contasPagar->ContaPagamentoID,
            ])->first();




            if ($duplicadoconsulta) {

                $contasPagar = ContasPagar::find($id);
                $contasPagar->update([
                    'LancamentoID' => $duplicadoconsulta->ID,
                ]);
                session(['contabilidade' => 'Lançamento já inserido na contabilidade! Atualizei o Contas a Pagar neste registro!']);
            } else {
                Lancamento::create($Lancamento);
                Lancamento::saved($Lancamento);

                // Repetir a consulta de duplicidade usando a data em formato ISO (Y-m-d)
                $duplicadoconsulta = Lancamento::where([
                    'EmpresaID' => $contasPagar->EmpresaID,
                    'Descricao' => $contasPagar->Descricao,
                    'Valor' => $contasPagar->Valor,
                    'DataContabilidade' => $DataContabilidade,
                    'ContaDebitoID' => $contasPagar->ContaFornecedorID,
                    'ContaCreditoID' => $contasPagar->ContaPagamentoID,
                ])->first();


                $contasPagar = ContasPagar::find($id);
                $contasPagar->update([
                    'LancamentoID' => $duplicadoconsulta->ID,
                ]);


                session(['success' => 'Lançamento incluído na contabilidade!']);
            }
        };


        return redirect()->route('ContasPagar.edit', $id);
    }

    public function CreateArquivoContasPagar(ArquivoContasPagarCreateRequest $request)
    {


        $id = $request->contaspagar_id;

        $arquivo_id = $request->arquivo_id;


        $Existe = ContasPagarArquivo::where('arquivo_id',$arquivo_id)
        ->where('contaspagar_id',$id)
        ->first();

        if($Existe){
            session(['error' => "ARQUIVO EXISTE:  "
            . $Existe->MostraLancamentoDocumento->Rotulo.  ' do tipo de arquivo: '
            . $Existe->MostraLancamentoDocumento->TipoArquivoNome->nome
            .",  já existe para este registro!"]);
            return redirect(route('ContasPagar.edit', $id));
        }

        $request['user_created'] = Auth ::user()->email;

        // dd($request->all());
        $model = $request->all();
        ContasPagarArquivo::create($model);
        return redirect(route('ContasPagar.edit', $id));
    }
}
