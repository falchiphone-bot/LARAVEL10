<?php

namespace App\Http\Controllers;
use app\Helpers;
use App\Helpers\FinancaHelper;
use App\Http\Requests\LancamentoResquest;
use App\Models\Empresa;
use App\Models\Historicos;
use App\Models\Lancamento;
use App\Model\Model;
use App\Models\Conta;
use App\Models\LancamentoDocumento;
use App\Models\PlanoConta;
use App\Models\Feriado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Days;
use Carbon\Carbon;

class LancamentosController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function __construct()
     {
         $this->middleware('auth');
        //  $this->middleware(['permission:LEITURA DE ARQUIVO - LISTAR'])->only('index');
         // $this->middleware(['permission:PLANO DE CONTAS - INCLUIR'])->only(['create', 'store']);
         // $this->middleware(['permission:PLANO DE CONTAS - EDITAR'])->only(['edit', 'update']);
         // $this->middleware(['permission:PLANO DE CONTAS - EXCLUIR'])->only('destroy');
         $this->middleware(['permission:LEITURA DE ARQUIVO - LISTAR'])->only('lancamentotabelaprice');
         $this->middleware(['permission:LEITURA DE ARQUIVO - LISTAR'])->only('lancamentoinformaprice');
        //  $this->middleware(['permission:LEITURA DE ARQUIVO - ENVIAR ARQUIVO PARA VISUALIZAR'])->only('SelecionaLinha');
     }


    public function Informaprice()
    {
        return view('Lancamentos.informaprice');
    }

    public function tabelaprice(Request $Request)
    {
        $valorTotal = $Request->TotalFinanciado;
        $taxaJuros = $Request->TaxaJurosMensal;
        $parcelas = $Request->Parcelas;

        $valor = str_replace(',', '', $valorTotal);

        if ($parcelas <= '0') {
            session(['Lancamento' => 'Campo de quantidade de parcelas foi preenchida erradamente!']);
            return view('Lancamentos.informaprice', ['Retorno' => $parcelas]);
        }

        if ($Request->VerVariaveis) {
            dd($valor, $taxaJuros, $parcelas);
        }

        $valorParcela = FinancaHelper::calcularTabelaPrice($valor, $taxaJuros, $parcelas);

        $saldoDevedor = $valor;

        for ($i = 1; $i <= $parcelas; $i++) {
            $juros = ($saldoDevedor * $taxaJuros) / 100;
            $amortizacao = $valorParcela - $juros;
            $saldoDevedor -= $amortizacao;

            $valorParcelaFormatado = number_format($valorParcela, 2, ',', '.');
            $jurosFormatado = number_format($juros, 2, ',', '.');
            $amortizacaoFormatada = number_format($amortizacao, 2, ',', '.');
            $saldoDevedorFormatado = number_format($saldoDevedor, 2, ',', '.');

            $tabelaParcelas[] = [
                'Parcela' => $i,
                'Amortização' => $amortizacao,
                'Juros' => $juros,
                'Total' => $valorParcela,
                'taxaJuros' => $taxaJuros,
                'parcelas' => $parcelas,
                'valorTotalFinanciado' => $valorTotal,
            ];

            // echo $i . "\tR$ " . $amortizacaoFormatada . "\t\tR$ " . $jurosFormatado . "\tR$ " . $valorParcelaFormatado . PHP_EOL;
        }

        // dd($tabelaParcelas);
        return view('lancamentos.tabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);

    }


    public function lancamentoinformaprice()
    {
        $retorno['EmpresaSelecionada'] = null;

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

            $debito = PlanoConta::where('descricao', 'LIKE', '%EMPRESTIMOS BANCARIOS A PAGAR%')
    ->orderBy('descricao', 'asc')
    ->get();


                $credito = PlanoConta::
                where('Grau',5)
                ->orderBy('descricao', 'asc')
                ->get();


        return view('Lancamentos.lancamentosInformaPrice', compact('Empresas', 'retorno','debito', 'credito'));
    }


        public function lancamentotabelaprice(Request $Request)
    {
        $valorTotal = $Request->TotalFinanciado;
        $taxaJuros = $Request->TaxaJurosMensal;
        $parcelas = $Request->Parcelas;
        $ContaDebito = $Request->ContaDebito;
        $ContaCredito = $Request->ContaCredito;
        $DataInicio = $Request->DataInicio;
        $Empresa = $Request->EmpresaSelecionada;

        $Mesmodia = $Request->Mesmodia;
        $Lancar = $Request->Lancar;
        $tabelaParcelas  = [];

        $Empresas = Empresa::
        where('Empresas.ID', $Empresa)
        ->orderBy('Empresas.Descricao', 'asc')
        ->select(['Empresas.ID', 'Empresas.Descricao'])
        ->get();
// dd($Empresas);
            $NomeEmpresa =  $Empresas[0]->Descricao;

        $valor = str_replace(',', '', $valorTotal);

        if ($parcelas <= '0') {
            session(['Lancamento' => 'Campo de quantidade de parcelas foi preenchida erradamente!']);
            return view('Lancamentos.informaprice', ['Retorno' => $parcelas]);
        }

        if ($Request->VerVariaveis) {
            dd($valor, $taxaJuros, $parcelas, $DataInicio, $Empresa, $ContaDebito,$ContaCredito,$Mesmodia);
        }


      $debito = Conta::where('EmpresaID', '=', $Empresa)     ->where('PlanoContas_id', $ContaDebito)     ->first();
      if($debito ==null){
        session(['Lancamento' => "CONTA DÉBITO NÃO PERTENCE A EMPRESA SELECIONADA! ATENÇÃO!"]);
        return view('lancamentos.lancamentotabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);
      }
      $credito = Conta::where('EmpresaID', '=', $Empresa)     ->where('PlanoContas_id', $ContaCredito)     ->first();
      if($credito == null){
        session(['Lancamento' => "CONTA CRÉDITO NÃO PERTENCE A EMPRESA SELECIONADA! ATENÇÃO"]);
        return view('lancamentos.lancamentotabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);
      }

        $valorParcela = FinancaHelper::calcularTabelaPrice($valor, $taxaJuros, $parcelas);

        $saldoDevedor = $valor;
        $DataInicial = $DataInicio;
        $DataInicialSomada = $DataInicio;
        $dia = substr($DataInicialSomada, 8, 2);


        for ($i = 1; $i <= $parcelas; $i++) {
            $juros = ($saldoDevedor * $taxaJuros) / 100;
            $amortizacao = $valorParcela - $juros;
            $saldoDevedor -= $amortizacao;

            $valorParcelaFormatado = number_format($valorParcela, 2, ',', '.');
            $jurosFormatado = number_format($juros, 2, ',', '.');
            $amortizacaoFormatada = number_format($amortizacao, 2, ',', '.');
            $saldoDevedorFormatado = number_format($saldoDevedor, 2, ',', '.');


            if($Mesmodia)
            {
                 $ano = substr($DataInicialSomada, 0, 4);
                 $mes = substr($DataInicialSomada, 5, 2);
                 $DataInicialSomada = ($ano.'-'. $mes.'-'.$dia);
                //  $DataInicialSomada = Carbon::createFromFormat('Y-m-d', $DataInicialSomada);
                if ($Request->VerVariaveis) {
                  dd($dia, $mes, $ano);
                }
            }
            $DataInicialSomada = Carbon::createFromFormat('Y-m-d', $DataInicialSomada);


            $feriado = Feriado::where('data', $DataInicialSomada->format('Y-m-d'))->first();
            while ($feriado ) {
                $DataInicialSomada->addDay(1);
                $feriado = Feriado::where('data', $DataInicialSomada->format('Y-m-d'))->first();
            }


            $diasemana = date('l', strtotime($DataInicialSomada));


            if($diasemana == 'Saturday'){
                $DataInicialSomada->addDay(2);
            }
            if($diasemana == 'Sunday'){
                $DataInicialSomada->addDay(1);
            }


            // if ($DataInicialSomada->weekDay() == 6) {
            //     $DataInicialSomada->addDay(2);
            // }

            // if($DataInicialSomada->weekday() == 7) {
            //     $DataInicialSomada->addDay(1);
            // }

            $Juros = number_format($juros, 2, '.', '');


            $ValorParcelas = number_format($valorParcela, 2, '.', '');

            $tabelaParcelas[] = [
                'Parcela' => $i,
                'Amortização' => $amortizacao,
                'Juros' => $Juros,
                'Total' => $ValorParcelas,
                'taxaJuros' => $taxaJuros,
                'parcelas' => $parcelas,
                'valorTotalFinanciado' => $valorTotal,
                'datainicial'=> $DataInicial,
                'empresa' => $Empresa,
                'nomeempresa' => $NomeEmpresa,
                'debito' => $debito->ID,
                'debitodescricao' => $debito->PlanoConta->Descricao
                    .' referente a '.$parcelas.' parcelas de '
                    .$ValorParcelas
                    .' com taxa de juros de: '.$taxaJuros
                    .' e total financiado de '.$valorTotal,
                'credito' => $credito->ID,
                'creditodescricao' => $credito->PlanoConta->Descricao,
                'datasomada' => $DataInicialSomada,
            ];

                $DataInicial = strtotime($DataInicialSomada);
                $DataInicialc = strtotime('+30 days', $DataInicial);
                $DataIniciald = date('Y-m-d', $DataInicialc);
                $DataInicialSomada = $DataIniciald;

                // echo $i . "\tR$ " . $amortizacaoFormatada . "\t\tR$ " . $jurosFormatado . "\tR$ " . $valorParcelaFormatado . PHP_EOL;
        }


        if($Lancar){
            session(['LancamentoDebito' => "NADA LANÇAMENTO A DÉBITO!"]);
            foreach($tabelaParcelas as $EfetuarLancamento){
                // dd($EfetuarLancamento);

                $data = $EfetuarLancamento['datasomada'];

                $dataString = date('d-m-Y', strtotime($data));

                $datacontabil = $EfetuarLancamento['datasomada'];
                $valorString = $EfetuarLancamento['Total'];


                $lancamentoLocalizado = Lancamento::where('DataContabilidade', $dataString)
                ->where('Valor', $EfetuarLancamento['Total'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaDebitoID', $EfetuarLancamento['debito'])
                ->First();


                if($lancamentoLocalizado){
                    // dd( $datacontabil,$EfetuarLancamento['Total'], $Empresa, $EfetuarLancamento['debito'] );
                    session(['LancamentoDebito' => "NADA LANÇAMENTO A DÉBITO!"]);

                    continue;
                }
                else{
                    // dd("Lancando ", $datacontabil,$EfetuarLancamento['Total'], $Empresa, $EfetuarLancamento['debito'] );

                    $dataSalvar = Carbon::createFromDate($EfetuarLancamento['datainicial']);

                    $LancamentoParcela[] = Lancamento::create([
                    'Valor' => ($valorString = $EfetuarLancamento['Total']),
                    'EmpresaID' => $EfetuarLancamento['empresa'],
                    'ContaDebitoID' => $EfetuarLancamento['debito'],
                    'ContaCreditoID' => $EfetuarLancamento['credito'],
                    'Descricao' => $EfetuarLancamento['debitodescricao'],
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' => $dataString ,
                    'Conferido' => false,
                    'HistoricoID' => null,
                    ]);


                    session(['LancamentoDebito' => "LANÇAMENTO A DÉBITO CRIADO!"]);
                    // return view('lancamentos.lancamentotabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);
                }


            }


        }


        // dd($tabelaParcelas);
        return view('lancamentos.lancamentotabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);
    }



    public function index()
    {
        // $ultimos_lancamentos = [];
        // if (session('Empresa')) {
        //     $ultimos_lancamentos = Lancamento::where('EmpresaID',session('Empresa')->ID)->limit(10)->orderBy('ID','DESC')->get();
        // }

        // return view('Lancamentos.index',compact('ultimos_lancamentos'));
        return redirect(route('planocontas.pesquisaavancada'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        $historicos = Historicos::orderBy('Descricao')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->select(['Historicos.ID', 'Historicos.Descricao']);
        //  ->pluck('Descricao','ID');

        return view('Lancamentos.create', compact('Empresas', 'historicos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LancamentoResquest $request)
    {
        $lancamento = $request->all();
        Lancamento::created($lancamento);
        return redirect(route('Lancamentos.index'))->with('success', 'Lançamento Criado.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $lancamento = Lancamento::find($id);
        if (empty($lancamento)) {
            return redirect(route('Lancamentos.index'))->with('error', 'Lançamento não encontrado');
        }
        return view('Lancamentos.show', compact('lancamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $lancamento = Lancamento::find($id);
        if (empty($lancamento)) {
            return redirect(route('Lancamentos.index'))->with('error', 'Lançamento não encontrado');
        }
        return view('Lancamentos.edit', compact('lancamento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LancamentoResquest $request, $id)
    {
        $lancamento = Lancamento::find($id);
        if (empty($lancamento)) {
            return redirect(route('Lancamentos.index'))->with('error', 'Lançamento não encontrado');
        }
        $lancamento->fill($request->all());
        $lancamento->save();
        return redirect(route('Lancamentos.index'))->with('success', 'Lançamento atualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $lancamento = Lancamento::find($id);
        if (empty($lancamento)) {
            return redirect(route('Lancamentos.index'))->with('error', 'Lançamento não encontrado');
        }
        $lancamento->destroy();
        return redirect(route('Lancamentos.index'));
    }

    public function baixarArquivo($id)
    {
        $download = LancamentoDocumento::find($id);
        if ($download) {
            return Storage::disk('google')->download($download->Nome . '.' . $download->Ext);
        } else {
            $this->addError('download', 'Arquivo não localizado para baixar.');
        }
    }
}
