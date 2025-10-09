<?php

namespace App\Http\Controllers;
use app\Helpers;
use App\Helpers\FinancaHelper;
use App\Http\Requests\LancamentoRequest;
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
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LancamentoExport;
use App\Exports\PreviewDespesasExport;
use App\Models\MoedasValores;
use App\Models\SolicitacaoExclusao;
use App\Models\ContasPagar;
use Exception;
class LancamentosController extends Controller
{

     public function DadosMes()


     {
        $DadosMes = session('DadosMes');
        $nome = session('nome');

       // // dd( $dados);
        // $Débito = 0;
        // $Crédito = 0;
        // $Saldo = 0;
        // foreach ($DadosMes as $item) {
        //     $Débito += $item['Débito'];
        //     $Crédito += $item['Crédito'];
        //     $Saldo += $item['Saldo'];
        // }


        return view('Lancamentos.DadosMes', compact('DadosMes','nome'));
        //  return view('Lancamentos.DadosMes', compact('DadosMes', 'Débito', 'Crédito', 'Saldo'));
     }


     public function exibirDadosGabrielMagossiFalchi()
     {
        $dados = session('dados');
        // dd( $dados);
        $Débito = 0;
        $Crédito = 0;
        $Saldo = 0;
        foreach ($dados as $item) {
            $Débito += $item['Débito'];
            $Crédito += $item['Crédito'];
            $Saldo += $item['Saldo'];
        }
         return view('Lancamentos.DadosGabrielMagossiFalchi', compact('dados', 'Débito', 'Crédito', 'Saldo'));
     }

     public function EntradasSaidasCalculos()
     {
        $payload = session('EntradasSaidasSoma');
        if (!$payload) {
            return redirect()->back()->with('error', 'Não há dados para exibir. Execute a soma novamente.');
        }
        // Evita erro se vier parcialmente
        $entradas = (float)($payload['entradas'] ?? 0);
        $saidas = (float)($payload['saidas'] ?? 0);
        $resultado = (float)($payload['resultado'] ?? ($entradas - $saidas));
        $de = $payload['de'] ?? null;
        $ate = $payload['ate'] ?? null;

        return view('Lancamentos.DadosEntradasSaidas', compact('entradas','saidas','resultado','de','ate'));
     }

     public function exibirDadosAvenuePoupanca()
       {
        $EmpresaID = 11;
        $dados = session('dados');
        // dd( $dados);
        $Débito = 0;
        $Crédito = 0;
        $Saldo = 0;
        foreach ($dados as $item) {
            $Débito += $item['Débito'];
            $Crédito += $item['Crédito'];
            $Saldo += $item['Saldo'];
        }

        //$somaDolar = Lancamento::somaValorQuantidadeDolar();
        $somaDolar = Lancamento::somaValorQuantidadeDolar(['EmpresaID' => $EmpresaID]);




        $valordolar = MoedasValores::where('idmoeda', 1)->orderBy('Data', 'desc')->first();

        $valordolarhoje = $valordolar->valor;
        $datadolarhoje = $valordolar->data;

        if (!$valordolarhoje) {
            throw new Exception('Não foi possível recuperar o valor do dólar hoje. VERIFIQUE!');
        }


        // dd($valordolarhoje);
        $somaDolarReal = $somaDolar *  $valordolarhoje ;


         return view('Lancamentos.DadosAvenuePoupanca', compact('dados', 'Débito', 'Crédito', 'Saldo', 'somaDolar', 'somaDolarReal', 'valordolarhoje', 'datadolarhoje'));
     }


     public function ExportarSkala()
     {
        $retorno['DataInicial'] = date('Y-m-d');
        $retorno['DataFinal'] = date('Y-m-d');
        $retorno['EmpresaSelecionada'] = null;

         $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
             ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
             ->OrderBy('Descricao')
             ->select(['Empresas.ID', 'Empresas.Descricao'])
             ->get();

             return view('lancamentos.ExportarSkala', compact('retorno','Empresas'));
        }


        public function ExportarSkalaExcel()
        {
           $retorno['DataInicial'] = date('Y-m-d');
           $retorno['DataFinal'] = date('Y-m-d');
           $retorno['EmpresaSelecionada'] = null;
           $retorno['ContaSelecionada'] = null;

            $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
                ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
                ->OrderBy('Descricao')
                ->select(['Empresas.ID', 'Empresas.Descricao'])
                ->get();

                $Contas = PlanoConta::get();

                return view('lancamentos.ExportarSkalaExcel', compact('retorno','Empresas','Contas'));
           }

        public function ExportarSkalaExcelPost(request $request)
        {

           $EmpresaID = $request->EmpresaSelecionada;
           $ContaID = $request->ContaSelecionada;
           $Verificanulo = $request->Verificanulo;


           $ContaPequisada = Conta::Where("EmpresaID",'=',"$EmpresaID")
           ->Where("Planocontas_id",'=',"$ContaID")
           ->First();

           $ContaGerar =  $ContaPequisada->ID;


           //////////////  converter em data e depois em string data
           $DataInicialCarbon = Carbon::parse($request->input('DataInicial')) ;
           $DataFinalCarbon = Carbon::parse($request->input('DataFinal'));
           $DataInicial = $DataInicialCarbon->format('d/m/Y');
           $DataFinal = $DataFinalCarbon->format('d/m/Y');


               $retorno['EmpresaSelecionada'] = $EmpresaID;
               $retorno['DataInicial'] = $DataInicialCarbon->format('Y-m-d');
               $retorno['DataFinal'] = $DataFinalCarbon->format('Y-m-d');
               $retorno['ContaSelecionada'] = $ContaID;

                $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
                ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
                ->OrderBy('Descricao')
                ->select(['Empresas.ID', 'Empresas.Descricao'])
                ->get();

                $Contas = PlanoConta::get();


                $EmpresasSelecionada = Empresa::find($EmpresaID);
           if($DataInicialCarbon > $DataFinalCarbon)
           {
               session(['error' => 'Data inicial maior que a data final']);
               return view('Lancamentos.ExportarSkalaExcel', compact('retorno', 'Empresas','Contas'));
           }



           $lancamento = Lancamento::where('EmpresaID', '=', $EmpresaID)
           ->where(function ($query) use ($ContaGerar) {
               $query->where('ContaDebitoID', '=', $ContaGerar)
                     ->orWhere('ContaCreditoID', '=', $ContaGerar);
           })
           ->where('DataContabilidade', '>=', $DataInicial)
           ->where('DataContabilidade', '<=', $DataFinal)
           ->select('DataContabilidade', 'ContaDebitoID', 'ContaCreditoID', 'Valor', 'HistoricoID', 'Descricao')
           ->orderBy('DataContabilidade', 'ASC')
           ->get();

                $numeroRegistros = $lancamento->count();
                if($numeroRegistros == 0)
                {
                    // dd('IGUAL A 0');
                    session(['error' => 'Sem lançamentos no período selecionado para a empresa selecionada']);
                    return view('Lancamentos.ExportarSkalaExcel', compact('retorno', 'Empresas','Contas'));
                }


                $ExportarLinha = [];
                $ExportarUnir = [];

                foreach ($lancamento as $item) {
                    $exportarItem = [
                        'DataContabilidade' => $item->DataContabilidade->format('d/m/Y'),
                        'ContaDebitoID' => $item->ContaDebito->PlanoConta->CodigoSkala,
                        'ContaCreditoID' => $item->ContaCredito->PlanoConta->CodigoSkala,
                        'Valor' => $item->Valor,
                        'Historico' => $item->Historico->Descricao??null,
                        'Descricao' => $item->Descricao,
                    ];

                    if($Verificanulo)
                    {
                       if( $item->ContaDebito->PlanoConta->CodigoSkala == null)
                        {
                            dd($exportarItem);
                        } else
                        {
                            dd("NADA LOCALIZADO. DESMARCAR OPÇÃO PARA SEGUIR!");
                        }
                    }


                    $ExportarLinha[] = $exportarItem;
                }

                $exportarUnir = collect($ExportarLinha);


                // dd($ExportarUnir);

                // Caminho do arquivo .csv que você deseja criar na pasta "storage"
                $Arquivo = $EmpresasSelecionada->Descricao . '-' .str_replace('/', '', $DataInicial). '-a-'.str_replace('/', '', $DataFinal).'.xlsx';



                return Excel::download(new LancamentoExport($exportarUnir), "$Arquivo");






        }


        public function ExportarExtratoExcel()
        {
           $retorno['DataInicial'] = date('Y-m-d');
           $retorno['DataFinal'] = date('Y-m-d');
           $retorno['EmpresaSelecionada'] = null;
           $retorno['ContaSelecionada'] = null;

            $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
                ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
                ->OrderBy('Descricao')
                ->select(['Empresas.ID', 'Empresas.Descricao'])
                ->get();

                $Contas = PlanoConta::get();

                return view('lancamentos.ExportarExtratoExcel', compact('retorno','Empresas','Contas'));
           }
        public function ExportarExtratoExcelPost(request $request)
        {
           $EmpresaID = $request->EmpresaSelecionada;
           $ContaID = $request->ContaSelecionada;


           $ContaPequisada = Conta::Where("EmpresaID",'=',"$EmpresaID")
           ->Where("Planocontas_id",'=',"$ContaID")
           ->First();

           $ContaGerar =  $ContaPequisada->ID;


           //////////////  converter em data e depois em string data
           $DataInicialCarbon = Carbon::parse($request->input('DataInicial')) ;
           $DataFinalCarbon = Carbon::parse($request->input('DataFinal'));
           $DataInicial = $DataInicialCarbon->format('d/m/Y');
           $DataFinal = $DataFinalCarbon->format('d/m/Y');


               $retorno['EmpresaSelecionada'] = $EmpresaID;
               $retorno['DataInicial'] = $DataInicialCarbon->format('Y-m-d');
               $retorno['DataFinal'] = $DataFinalCarbon->format('Y-m-d');
               $retorno['ContaSelecionada'] = $ContaID;

                $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
                ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
                ->OrderBy('Descricao')
                ->select(['Empresas.ID', 'Empresas.Descricao'])
                ->get();

                $Contas = PlanoConta::get();


                $EmpresasSelecionada = Empresa::find($EmpresaID);
           if($DataInicialCarbon > $DataFinalCarbon)
           {
               session(['error' => 'Data inicial maior que a data final']);
               return view('Lancamentos.ExportarExtratoExcel', compact('retorno', 'Empresas','Contas'));
           }



           $lancamento = Lancamento::where('EmpresaID', '=', $EmpresaID)
           ->where(function ($query) use ($ContaGerar) {
               $query->where('ContaDebitoID', '=', $ContaGerar)
                     ->orWhere('ContaCreditoID', '=', $ContaGerar);
           })
           ->where('DataContabilidade', '>=', $DataInicial)
           ->where('DataContabilidade', '<=', $DataFinal)
           ->select('DataContabilidade', 'ContaDebitoID', 'ContaCreditoID', 'Valor', 'HistoricoID', 'Descricao')
           ->orderBy('DataContabilidade', 'ASC')
           ->get();

                $numeroRegistros = $lancamento->count();
                if($numeroRegistros == 0)
                {
                    // dd('IGUAL A 0');
                    session(['error' => 'Sem lançamentos no período selecionado para a empresa selecionada']);
                    return view('Lancamentos.ExportarExtratoExcel', compact('retorno', 'Empresas','Contas'));
                }


                $ExportarLinha = [];
                $ExportarUnir = [];

                foreach ($lancamento as $item) {
                    $exportarItem = [
                        'DataContabilidade' => $item->DataContabilidade->format('d/m/Y'),
                        'ContaDebitoID' => $item->ContaDebito->PlanoConta->Descricao,
                        'ContaCreditoID' => $item->ContaCredito->PlanoConta->Descricao,
                        'Valor' => $item->Valor,
                        'Historico' => $item->Historico->Descricao??null,
                        'Descricao' => $item->Descricao,
                    ];


                    $ExportarLinha[] = $exportarItem;
                }

                $exportarUnir = collect($ExportarLinha);

                // Caminho do arquivo .csv que você deseja criar na pasta "storage"
                $Arquivo = $EmpresasSelecionada->Descricao . '-' .str_replace('/', '', $DataInicial). '-a-'.str_replace('/', '', $DataFinal).'.xlsx';

                return Excel::download(new LancamentoExport($exportarUnir), "$Arquivo");
        }



    public function ExportarSkalaPost(request $request)
    {

       $EmpresaID = $request->EmpresaSelecionada;

       //////////////  converter em data e depois em string data
       $DataInicialCarbon = Carbon::parse($request->input('DataInicial')) ;
       $DataFinalCarbon = Carbon::parse($request->input('DataFinal'));
       $DataInicial = $DataInicialCarbon->format('d/m/Y');
       $DataFinal = $DataFinalCarbon->format('d/m/Y');


           $retorno['EmpresaSelecionada'] = $EmpresaID;
           $retorno['DataInicial'] = $DataInicialCarbon->format('Y-m-d');
           $retorno['DataFinal'] = $DataFinalCarbon->format('Y-m-d');

            $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

            $EmpresasSelecionada = Empresa::find($EmpresaID);
       if($DataInicialCarbon > $DataFinalCarbon)
       {
           session(['error' => 'Data inicial maior que a data final']);
           return view('Lancamentos.ExportarSkala', compact('retorno', 'Empresas'));
       }

            $lancamento = Lancamento::Where('EmpresaID','=',$EmpresaID)
            // ->take(30)
            ->where('DataContabilidade','>',$DataInicial)
            ->where('DataContabilidade','<',$DataFinal)
            ->select('DataContabilidade', 'ContaDebitoID','ContaCreditoID','Valor','Descricao')
            ->orderBy('DataContabilidade', 'ASC')
            ->get();

            $numeroRegistros = $lancamento->count();
            if($numeroRegistros == 0)
            {
                // dd('IGUAL A 0');
                session(['error' => 'Sem lançamentos no período selecionado para a empresa selecionada']);
                return view('Lancamentos.ExportarSkala', compact('retorno', 'Empresas'));
            }



            // Exemplo da coleção $Exportar[]
            $Exportar = $lancamento;

// /////////////// filtra somente o valor maior que 0
// $registros = $Exportar->toArray();

// $registrosValoresTodos = array_filter($registros, function ($registro) {
//     // return isset($registro['SaldoAtual']) && $registro['SaldoAtual'] !== 0;
//     return isset($registro['ContaDebitoID']) && empty($registro['ContaDebitoID']);
// });
// ////////////////////////////// /////////////// /////////////// /////////////// ///////////////

// $registrosValoresTodos = array_filter($registros, function ($registro) {
//     // return isset($registro['SaldoAtual']) && $registro['SaldoAtual'] !== 0;
//     return isset($registro['ContaCreditoID']) && !empty(trim($registro['ContaCreditoID']));
// });
// ////////////////////////////// /////////////// /////////////// /////////////// ///////////////
// // dd($registrosValoresTodos);
// $Exportar = $registrosValoresTodos;

            // Caminho do arquivo .csv que você deseja criar na pasta "storage"
            $Arquivo = $EmpresasSelecionada->Descricao . '-' .str_replace('/', '', $DataInicial). '-a-'.str_replace('/', '', $DataFinal).'.csv';

            $caminho_arquivo_csv = storage_path($Arquivo);

            // Abrir o arquivo .csv em modo de escrita usando a classe Storage
            $file = fopen($caminho_arquivo_csv, 'w');

            // Escrever o cabeçalho no arquivo
            $campos = ["Data", "Debito", "Credito", "Valor", "Descricao"];
            fputcsv($file, $campos);

            // Escrever os dados da coleção no arquivo
            foreach ($Exportar as $item) {
                $codigoSkalaDebito = $item->ContaDebito->PlanoConta->CodigoSkala;
                $codigoSkalaCredito = $item->ContaCredito->PlanoConta->CodigoSkala;
                $dados = [
                    $item->DataContabilidade->format('d/m/Y'),
                    $codigoSkalaDebito,
                    $codigoSkalaCredito,
                    $item->Valor,
                    $item->Descricao
                ];
                fputcsv($file, $dados);
            }

            // Fechar o arquivo
            fclose($file);


            // Definir os cabeçalhos para o download
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"$Arquivo\"");


// Ler e enviar o arquivo para o cliente
readfile($caminho_arquivo_csv);

// Após o readfile, você pode optar por excluir o arquivo temporário, se desejar.
unlink($caminho_arquivo_csv);
exit();

session(['success' => 'Arquivo gerado com sucesso!']);
session(['error' => null]);

return view('Lancamentos.ExportarSkala', compact('retorno', 'Empresas'));

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

    //         $debito = PlanoConta::where('descricao', 'LIKE', '%EMPRESTIMOS BANCARIOS A PAGAR%')
    // ->orderBy('descricao', 'asc')
    // ->get();

$debito = PlanoConta::where('Grau', 5)
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


         $valorTotalNumero = str_replace(',', '', $valorTotal);
$amortizacaofixa = (float) $valorTotalNumero / (int) $parcelas;

// dd($amortizacaofixa, $valorTotalNumero, $parcelas);


        $Mesmodia = $Request->Mesmodia;
        $Lancar = $Request->Lancar;
        $LancarParcela = $Request->LancarParcela;
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


        $contadebitoJuros = Conta::where('EmpresaID', '=', $Empresa)     ->where('PlanoContas_id', '9238')     ->first();
        // dd($contadebitoJuros);
        if($contadebitoJuros->Bloqueiodataanterior == null)
        {
            session([
                'Lancamento' =>
                    'Conta DÉBITO: ' .
                    $contadebitoJuros->PlanoConta->Descricao .
                    ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                 da conta para seguir este procedimento. Bloqueada ou  NULA  - CÓDIGO L158',
            ]);
            return view('lancamentos.lancamentotabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);
        }
        $data_conta_juros_bloqueio = $contadebitoJuros->Bloqueiodataanterior;
        if ($data_conta_juros_bloqueio->greaterThanOrEqualTo($DataInicio)) {
            session([
                'Lancamento' =>
                    'Conta DÉBITO: ' .
                    $contadebitoJuros->PlanoConta->Descricao .
                    ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                 da conta para seguir este procedimento. Bloqueada para até ' .
                    $data_conta_juros_bloqueio->format('d/m/Y'). '  - CÓDIGO L158',

            ]);
        }

        // dd($contadebitoJuros->ID);
      $debito = Conta::where('EmpresaID', '=', $Empresa)     ->where('PlanoContas_id', $ContaDebito)     ->first();
      if($debito == null){
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

            if($LancarParcela == true)
            {

                $amortizacao = number_format($amortizacaofixa, 2, '.', '');
            }


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

            if($LancarParcela == true)
            {
                $ValorParcelas = $amortizacao;
                $Juros = 0;
            }

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

            dd($tabelaParcelas, $debito->ID, $credito->ID, $Empresa, $DataInicialSomada);
            foreach($tabelaParcelas as $EfetuarLancamento){




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
                    $dataLancamento_carbon = Carbon::createFromDate($data);
                $dataLancamento = $dataLancamento_carbon->format('Y/m/d');
                $data_conta_debito_bloqueio = $lancamentoLocalizado->ContaDebito->Bloqueiodataanterior;
                $data_conta_credito_bloqueio = $lancamentoLocalizado->ContaCredito->Bloqueiodataanterior;

                if ($data_conta_debito_bloqueio == null) {
                    session([
                        'Lancamento' =>
                            'Conta DÉBITO: ' .
                            $lancamentoLocalizado->ContaDebito->PlanoConta->Descricao .
                            ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                         da conta para seguir este procedimento. Bloqueada: NULA  - CÓDIGO L291',
                    ]);
                    return view('lancamentos.lancamentotabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);
                }

                if ($data_conta_debito_bloqueio->greaterThanOrEqualTo($dataLancamento)) {
                    session([
                        'Lancamento' =>
                            'Conta DÉBITO: ' .
                            $lancamentoLocalizado->ContaDebito->PlanoConta->Descricao .
                            ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                         da conta para seguir este procedimento. Bloqueada para até ' .
                            $data_conta_debito_bloqueio->format('d/m/Y').'- CÓDIGO L333',

                    ]);
                }

                if ($data_conta_credito_bloqueio == null) {
                    session([
                        'Lancamento' =>
                            'Conta DÉBITO: ' .
                            $lancamentoLocalizado->ContaCredito->PlanoConta->Descricao .
                            ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                         da conta para seguir este procedimento. Bloqueada: NULA - CÓDIGO L314',
                    ]);
                    return view('lancamentos.lancamentotabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);
                }

                if ($data_conta_credito_bloqueio->greaterThanOrEqualTo($dataLancamento)) {
                    session([
                        'Lancamento' =>
                            'Conta DÉBITO: ' .
                            $lancamentoLocalizado->ContaCredito->PlanoConta->Descricao .
                            ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                         da conta para seguir este procedimento. Bloqueada para até ' .
                            $data_conta_debito_bloqueio->format('d/m/Y').'- CÓDIGO L356',

                    ]);
                }
                }




                if($lancamentoLocalizado){
                    // dd( $datacontabil,$EfetuarLancamento['Total'], $Empresa, $EfetuarLancamento['debito'] );
                    session(['LancamentoDebito' => "NADA LANÇAMENTO A DÉBITO OU A CRÉDITO!"]);
                }
                if($oldRows){
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

                ////////// JUROS
                $lancamentoLocalizadoJuros = Lancamento::where('DataContabilidade', $dataString)
                    ->where('Valor', $EfetuarLancamento['Juros'])
                    ->where('EmpresaID', $Empresa)
                    ->where('ContaCreditoID', $EfetuarLancamento['debito'])
                    ->First();

                if($lancamentoLocalizadoJuros){
                     $data_conta_juros_bloqueio = $lancamentoLocalizadoJuros->ContaDebito->Bloqueiodataanterior;
                    if ($data_conta_juros_bloqueio == null) {
                        session([
                            'Lancamento' =>
                                'Conta DÉBITO: ' .
                                $lancamentoLocalizadoJuros->ContaDebito->PlanoConta->Descricao .
                                ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                             da conta para seguir este procedimento. Bloqueada: NULA - CÓDIGO L375',
                        ]);

                        return view('lancamentos.lancamentotabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);
                    }

                    if ($data_conta_juros_bloqueio->greaterThanOrEqualTo($dataLancamento)) {
                        session([
                            'Lancamento' =>
                                'Conta DÉBITO: ' .
                                $lancamentoLocalizado->ContaDebito->PlanoConta->Descricao .
                                ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                             da conta para seguir este procedimento. Bloqueada para até ' .
                                $data_conta_juros_bloqueio->format('d/m/Y').'- CÓDIGO L418',

                        ]);
                    }
                }


                if($lancamentoLocalizadoJuros){
                    // dd( $datacontabil,$EfetuarLancamento['Total'], $Empresa, $EfetuarLancamento['debito'] );
                    session(['LancamentoDebito' => "NADA LANÇAMENTO A DÉBITO OU A CRÉDITO!"]);

                    continue;
                }
                else{
                    // dd("Lancando ", $datacontabil,$EfetuarLancamento['Total'], $Empresa, $EfetuarLancamento['debito'] );

                    $dataSalvar = Carbon::createFromDate($EfetuarLancamento['datainicial']);

                    if($EfetuarLancamento['Juros'] == 0)
                    {
                        session(['LancamentoDebito' => "NADA LANÇAMENTO A DÉBITO OU A CRÉDITO!"]);
                        continue;
                    }

                    if($EfetuarLancamento['Juros'] == null)
                    {
                        session(['LancamentoDebito' => "NADA LANÇAMENTO A DÉBITO OU A CRÉDITO!"]);
                        continue;
                    }

                    if($LancarParcela == null || $LancarParcela == false)
                    {
                        session(['LancamentoDebito' => "NADA LANÇAMENTO A DÉBITO OU A CRÉDITO!"]);
                        continue;
                    }

                    $LancamentoParcela[] = Lancamento::create([
                    'Valor' => ($valorString = $EfetuarLancamento['Juros']),
                    'EmpresaID' => $EfetuarLancamento['empresa'],
                    'ContaDebitoID' => $contadebitoJuros->ID,
                    'ContaCreditoID' => $EfetuarLancamento['debito'],
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
    public function store(LancamentoRequest $request)
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
     * Página alternativa simples (sem Livewire) para editar lançamento.
     */
    public function editSimple($id)
    {
        $lancamento = Lancamento::find($id);
        if (!$lancamento) {
            return redirect()->back()->with('error','Lançamento não encontrado');
        }
        // Empresas permitidas ao usuário
        $empresas = Empresa::join('Contabilidade.EmpresasUsuarios','Empresas.ID','=','EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->id())
            ->orderBy('Empresas.Descricao')
            ->pluck('Empresas.Descricao','Empresas.ID');

        // Histórico compartilhado (filtrado por usuário)
        $historicos = Historicos::join('Contabilidade.EmpresasUsuarios','Historicos.EmpresaID','=','EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->id())
            ->select(['Historicos.ID','Historicos.Descricao'])
            ->orderBy('Historicos.Descricao')
            ->get();

        // Contas da empresa atual (Grau 5) exibindo descrição do Plano de Contas
        $contasOrigem = Conta::where('Contabilidade.Contas.EmpresaID',$lancamento->EmpresaID)
            ->join('Contabilidade.PlanoContas','PlanoContas.ID','=','Contabilidade.Contas.Planocontas_id')
            ->where('PlanoContas.Grau',5)
            ->orderBy('PlanoContas.Descricao')
            ->pluck('PlanoContas.Descricao','Contabilidade.Contas.ID');

        return view('Lancamentos.edit-simple', [
            'lancamento' => $lancamento,
            'empresas' => $empresas,
            'historicos' => $historicos,
            'contasOrigem' => $contasOrigem,
        ]);
    }

    /**
     * Tela de clonagem simples: pré-preenche dados do lançamento para criar um novo registro.
     * Similar à edição simples, mas ação gera novo lançamento em vez de atualizar o existente.
     */
    public function cloneSimple($id)
    {
        $lancamento = Lancamento::find($id);
        if(!$lancamento){
            return redirect()->back()->with('error','Lançamento não encontrado');
        }
        $empresas = Empresa::join('Contabilidade.EmpresasUsuarios','Empresas.ID','=','EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->id())
            ->orderBy('Empresas.Descricao')
            ->pluck('Empresas.Descricao','Empresas.ID');

        $historicos = Historicos::join('Contabilidade.EmpresasUsuarios','Historicos.EmpresaID','=','EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->id())
            ->select(['Historicos.ID','Historicos.Descricao'])
            ->orderBy('Historicos.Descricao')
            ->get();

        $contasOrigem = Conta::where('Contabilidade.Contas.EmpresaID',$lancamento->EmpresaID)
            ->join('Contabilidade.PlanoContas','PlanoContas.ID','=','Contabilidade.Contas.Planocontas_id')
            ->where('PlanoContas.Grau',5)
            ->orderBy('PlanoContas.Descricao')
            ->pluck('PlanoContas.Descricao','Contabilidade.Contas.ID');

        return view('Lancamentos.clone-simple',[
            'lancamento'=>$lancamento,
            'empresas'=>$empresas,
            'historicos'=>$historicos,
            'contasOrigem'=>$contasOrigem,
        ]);
    }

    /**
     * Persistência do clone: cria novo registro baseado nos dados ajustados do formulário.
     */
    public function storeCloneSimple(LancamentoRequest $request, $id)
    {
        $original = Lancamento::find($id);
        if(!$original){
            return redirect()->back()->with('error','Lançamento original não encontrado');
        }
        $payload = $request->all();
        // Se empresa foi trocada deve ter escolhido contas válidas (UI garante grau 5)
        if(isset($payload['EmpresaID']) && (int)$payload['EmpresaID'] !== (int)$original->EmpresaID){
            $request->validate([
                'ContaDebitoID' => 'required|integer',
                'ContaCreditoID' => 'required|integer',
            ],[
                'ContaDebitoID.required' => 'Selecione a conta débito da nova empresa.',
                'ContaCreditoID.required' => 'Selecione a conta crédito da nova empresa.',
            ]);
        } else {
            // Mesma empresa: se usuário não trocar contas podemos reutilizar as originais
            if(empty($payload['ContaDebitoID'])) $payload['ContaDebitoID'] = $original->ContaDebitoID;
            if(empty($payload['ContaCreditoID'])) $payload['ContaCreditoID'] = $original->ContaCreditoID;
            $payload['EmpresaID'] = $original->EmpresaID; // garante consistência
        }
        // Normalização BRL
        foreach (['Valor','ValorQuantidadeDolar'] as $campo) {
            if (isset($payload[$campo]) && $payload[$campo] !== '') {
                $bruto = trim($payload[$campo]);
                if (!is_numeric($bruto)) {
                    $semMilhar = str_replace(['.',' '],'',$bruto);
                    $normalizado = str_replace(',','.', $semMilhar);
                    if (!is_numeric($normalizado)) $normalizado = 0;
                    $payload[$campo] = $normalizado;
                }
            }
        }
        // Campos fixos / herdados
        $novo = new Lancamento();
        $novo->fill([
            'DataContabilidade' => $payload['DataContabilidade'] ?? $original->DataContabilidade,
            'Descricao' => $payload['Descricao'] ?? $original->Descricao,
            'ContaDebitoID' => $payload['ContaDebitoID'],
            'ContaCreditoID' => $payload['ContaCreditoID'],
            'Valor' => $payload['Valor'] ?? $original->Valor,
            'ValorQuantidadeDolar' => $payload['ValorQuantidadeDolar'] ?? $original->ValorQuantidadeDolar,
            'EmpresaID' => $payload['EmpresaID'],
            'HistoricoID' => $payload['HistoricoID'] ?? $original->HistoricoID,
            'Usuarios_id' => auth()->id(),
            'Conferido' => false,
        ]);
        $novo->save();

        return redirect()->route('lancamentos.edit.simple',$novo->ID)->with('message','Clone criado (#'.$novo->ID.').');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LancamentoRequest $request, $id)
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
     * Update simplificado para a view alternativa.
     */
    public function updateSimple(LancamentoRequest $request, $id)
    {
        $lancamento = Lancamento::find($id);
        if(!$lancamento){
            return redirect()->back()->with('error','Lançamento não encontrado');
        }
        $payload = $request->all();
        // Se empresa foi alterada, força validação de novas contas (grau 5 já garantido pela UI via endpoint)
        if(isset($payload['EmpresaID']) && (int)$payload['EmpresaID'] !== (int)$lancamento->EmpresaID){
            $request->validate([
                'ContaDebitoID' => 'required|integer',
                'ContaCreditoID' => 'required|integer',
            ],[
                'ContaDebitoID.required' => 'Selecione a nova conta débito da empresa escolhida.',
                'ContaCreditoID.required' => 'Selecione a nova conta crédito da empresa escolhida.',
            ]);
        }
        // Normalização de valores no formato brasileiro (ex: 9.340,01 => 9340.01)
        foreach (['Valor','ValorQuantidadeDolar'] as $campo) {
            if (isset($payload[$campo]) && $payload[$campo] !== '') {
                // Remove espaços
                $bruto = trim($payload[$campo]);
                // Se vier já numérico não altera
                if (!is_numeric($bruto)) {
                    // Remove milhares
                    $semMilhar = str_replace(['.',' '],'',$bruto);
                    // Troca vírgula decimal por ponto
                    $normalizado = str_replace(',','.', $semMilhar);
                    // Se continuar não numérico, zera para evitar exception
                    if (!is_numeric($normalizado)) {
                        $normalizado = 0;
                    }
                    $payload[$campo] = $normalizado;
                }
            }
        }
        $lancamento->fill($payload);
        $lancamento->save();
        return redirect()->route('lancamentos.edit.simple',$lancamento->ID)->with('message','Alterações salvas.');
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

    public function Solicitacoes()
    {
        $solicitacoes = SolicitacaoExclusao::
       orderBy('ID', 'DESC')->get();
        // dd($solicitacoes);

        $solicitacoes = $solicitacoes->reject(function ($solicitacao) {
            return $solicitacao->ID <= 19000;
        });

        return view('contabilidade.solicitacoes', compact('solicitacoes'));
    }

    public function SolicitacoesExcluir($id)
    {
        $SolicitacaoExclusao = SolicitacaoExclusao::find($id);

        $DataContabilidade = $SolicitacaoExclusao->lancamento->DataContabilidade;

        // $conta = conta::where('ID', $SolicitacaoExclusao->ContaID)->first();

        $data_lancamento_bloqueio_debito =  $SolicitacaoExclusao ->lancamento->ContaDebito->Bloqueiodataanterior;
        $data_lancamento_bloqueio_credito =  $SolicitacaoExclusao ->lancamento->ContaCredito->Bloqueiodataanterior;

        // dd( "Exclusão: ". $SolicitacaoExclusao, "DÉBITO: ". $data_lancamento_bloqueio_debito, "CRÉDITO: ".$data_lancamento_bloqueio_credito);

            if ($data_lancamento_bloqueio_debito !== null && $data_lancamento_bloqueio_debito->greaterThanOrEqualTo($DataContabilidade)) {
                // O código aqui será executado se $data_lancamento_bloqueio_debito não for nulo e for maior ou igual a $DataContabilidade
                session([
                    'error' =>
                    'Conta DÉBITO: ' .
                    $SolicitacaoExclusao->lancamento->ContaDebito->PlanoConta->Descricao .
                        ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                     da conta para seguir este procedimento. Bloqueada para até ' .
                        $data_lancamento_bloqueio_debito->format('d/m/Y') .  '  - CÓDIGO L981'
                ]);
                return redirect(route('lancamentos.solicitacoes'));
            }


            if ($data_lancamento_bloqueio_credito !== null && $data_lancamento_bloqueio_credito->greaterThanOrEqualTo($DataContabilidade)) {
                // O código aqui será executado se $data_lancamento_bloqueio_debito não for nulo e for maior ou igual a $DataContabilidade
                session([
                    'error' =>
                    'Conta CRÉDITO: ' .
                       $SolicitacaoExclusao->lancamento->ContaCredito->PlanoConta->Descricao .
                        ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                     da conta para seguir este procedimento. Bloqueada para até ' .
                        $data_lancamento_bloqueio_credito->format('d/m/Y') .  '  - CÓDIGO L995'
                ]);
                return redirect(route('lancamentos.solicitacoes'));
            }



            $EmpresaBloqueada = Empresa::where('ID', '=', $SolicitacaoExclusao->lancamento->EmpresaID)->first();

            $data_lancamento_bloqueio_empresa = $EmpresaBloqueada->Bloqueiodataanterior;
            $dataLimite = $data_lancamento_bloqueio_empresa;

            if ($DataContabilidade <= $dataLimite) {
                // A data de lançamento é maior do que a data limite permitida
                session([
                    'error' =>
                    'EMPRESA BLOQUEADA: ' .
                    $EmpresaBloqueada->Descricao .
                    'A data de lançamento não pode ser maior do que ' . $data_lancamento_bloqueio_empresa->format('d/m/Y') . ' que é a data limite do bloqueio. - CÓDIGO L1099'
                ]);
                return redirect(route('lancamentos.solicitacoes'));
            }

            if ($data_lancamento_bloqueio_empresa->greaterThanOrEqualTo($DataContabilidade)) {
                // Data da empresa bloqueada
                session([
                    'error' =>
                    'EMPRESA BLOQUEADA: ' .
                        $EmpresaBloqueada->Descricao .
                        ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
             da empresa para seguir este procedimento. Bloqueada para até ' .
                        $EmpresaBloqueada .  '  - CÓDIGO L1022'
                ]);
                return redirect(route('lancamentos.solicitacoes'));
            }



    //  dd("SOLICITAÇÃO". $SolicitacaoExclusao,
    //   "DATA CONTABILIDADE: " . $DataContabilidade,
    //   "DATA BLOQUEIO DA EMPRESA: " .$EmpresaBloqueada,
    //   "DATA LANCAMENTO BLOQUEIO DA EMPRESA: " . $data_lancamento_bloqueio_empresa,
    //   "DATA LIMITE DA EMPRESA: " .$dataLimite);


        if ($SolicitacaoExclusao) {
            $SolicitacaoExclusao->delete();
            session()->flash('success', 'Excluído com sucesso o registro');
        } else {
            session()->flash('error', 'Registro não encontrado');
        }
        return redirect(route('lancamentos.solicitacoes'));

    }

    public function SolicitacoesTransferir($id)
    {
        $SolicitacaoTransferir = Lancamento::find($id);


        $ContasPagar = ContasPagar::where("LancamentoID",$id)->first();
        // dd($SolicitacaoTransferir, $id);

         if ($SolicitacaoTransferir) {
            $SolicitacaoTransferir->EmpresaID = 1;
            $SolicitacaoTransferir->ContaDebitoID = 16;
            $SolicitacaoTransferir->ContaCreditoID = 6011;
            $SolicitacaoTransferir->update();

            if($ContasPagar)
            {
                $ContasPagar->EmpresaID = 1;
                $ContasPagar->ContaFornecedorID = 16;
                $ContasPagar->ContaPagamentoID = 6011;
                $ContasPagar->update();
            }



            session()->flash('success', 'Transferido com sucesso o registro');
        } else {
            session()->flash('error', 'Registro não encontrado');
        }



        $SolicitacaoExclusao = SolicitacaoExclusao::where("TableID",$id)->first();



        // dd($SolicitacaoTransferir, $id,  $SolicitacaoExclusao->ID,  $SolicitacaoExclusao, $ContasPagar);

        return redirect(route('lancamentos.solicitacoesexcluir',  $SolicitacaoExclusao->ID));

    }

    /**
     * Preview de planilha de despesas (sem gravação em BD) para ajuste de textos de histórico.
     * Parâmetros opcionais: ?file=DESPESAS-08-2025-TEC.xlsx&limite=300
     * Arquivo deve estar em storage/app/imports/<arquivo>.
     */
    public function previewDespesasExcel(Request $request)
    {
        // Upload direto (opcional)
        if($request->hasFile('arquivo_excel')){
            $request->validate(['arquivo_excel' => 'file|mimes:xlsx,xls|max:5120']);
            $uploaded = $request->file('arquivo_excel');
            $storedName = $uploaded->getClientOriginalName();
            $uploaded->storeAs('imports', $storedName);
            return redirect()->route('lancamentos.preview.despesas',[ 'file'=>$storedName ]);
        }

        $file = $request->query('file', 'DESPESAS-08-2025-TEC.xlsx');
        $limite = max(1,(int)$request->query('limite', 500));
        $flagUpper = (bool)$request->query('upper');
        $flagTrimMulti = (bool)$request->query('trim_multi');
        $subsRaw = $request->query('subs'); // formato: find=>replace|find2=>replace2
        $subs = [];
        if($subsRaw){
            foreach(explode('|',$subsRaw) as $pair){
                if(strpos($pair,'=>')!==false){
                    [$a,$b] = explode('=>',$pair,2); $subs[trim($a)] = $b; }
            }
        }
        $regexRaw = $request->query('regex'); // formato: /expr/flags=>replace|/expr2/flags=>rep2
        $regexSubs = [];
        if($regexRaw){
            foreach(explode('|',$regexRaw) as $pair){
                if(strpos($pair,'=>')!==false){
                    [$a,$b] = explode('=>',$pair,2); $regexSubs[$a] = $b; }
            }
        }

        $basePath = storage_path('app/imports');
        $fullPath = $basePath.DIRECTORY_SEPARATOR.$file;
        $exists = file_exists($fullPath);
        $cacheKey = 'preview_despesas:'.auth()->id().':'.md5(json_encode([$file,$limite,$flagUpper,$flagTrimMulti,$subsRaw,$regexRaw]));
        $forceRefresh = (bool)$request->query('refresh');
    $headers = [];
    $rows = [];
    $empresaIdFromFile = null;
    $contaCreditoIdFromFile = null;
        // Empresas disponíveis para o usuário (mesma lógica usada em outros pontos)
        $empresasLista = Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->id())
            ->orderBy('Empresas.Descricao')
            ->get(['Empresas.ID','Empresas.Descricao']);
        $erro = null;
    $oldRows = [];
    $selectedEmpresaId = null;
    $empresaLocked = false; // trava de seleção de empresa
    $globalCreditContaId = null; $globalCreditContaLabel = null; $globalCreditContaLocked = false;
        if(!$forceRefresh && Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $headers = $cached['headers'] ?? [];
            $rows = $cached['rows'] ?? [];
            $selectedEmpresaId = $cached['selected_empresa_id'] ?? null;
            $empresaLocked = $cached['empresa_locked'] ?? false;
            $globalCreditContaId = $cached['global_credit_conta_id'] ?? null;
            $globalCreditContaLabel = $cached['global_credit_conta_label'] ?? null;
            $globalCreditContaLocked = $cached['global_credit_conta_locked'] ?? false;
            // Inferência também no caminho de cache
            if(in_array('EMPRESA_ID',$headers,true)){
                foreach($rows as $rX){ if(!empty($rX['EMPRESA_ID'])){ $empresaIdFromFile = (int)$rX['EMPRESA_ID']; break; } }
            }
            if(in_array('CONTA_CREDITO_GLOBAL_ID',$headers,true)){
                foreach($rows as $rX){ if(!empty($rX['CONTA_CREDITO_GLOBAL_ID'])){ $contaCreditoIdFromFile = (int)$rX['CONTA_CREDITO_GLOBAL_ID']; break; } }
            }
            // Fallbacks: se não encontrado nas linhas, usa o que já está no cache como sugestão
            if(!$empresaIdFromFile && $selectedEmpresaId){ $empresaIdFromFile = (int)$selectedEmpresaId; }
            if(!$contaCreditoIdFromFile && $globalCreditContaId){ $contaCreditoIdFromFile = (int)$globalCreditContaId; }
            // Se cache ainda não tem empresa selecionada mas inferimos do arquivo, seta e destrava
            $payload = $cached;
            $touched = false;
            if($empresaIdFromFile && empty($payload['selected_empresa_id'])){
                $payload['selected_empresa_id'] = $empresaIdFromFile;
                $payload['empresa_locked'] = false;
                $selectedEmpresaId = $empresaIdFromFile;
                $empresaLocked = false;
                $touched = true;
                // Atualiza cada linha para refletir empresa global quando antes estava vazia
                if(!empty($payload['rows'])){
                    foreach($payload['rows'] as &$r){ if(empty($r['_class_empresa_id'])) $r['_class_empresa_id'] = $empresaIdFromFile; }
                    unset($r);
                }
            }
            if($contaCreditoIdFromFile && empty($payload['global_credit_conta_id'])){
                $payload['global_credit_conta_id'] = $contaCreditoIdFromFile;
                $payload['global_credit_conta_label'] = $payload['global_credit_conta_label'] ?? null; // label opcional
                $payload['global_credit_conta_locked'] = false; // destrava para permitir edição
                $globalCreditContaId = $contaCreditoIdFromFile;
                $globalCreditContaLocked = false;
                $touched = true;
            }
            if($touched){ Cache::put($cacheKey, $payload, now()->addHour()); }
        } elseif(!$exists){
            $erro = "Arquivo não encontrado em imports: $file. Copie ou faça upload.";
        } else {
            try {
                if($forceRefresh && Cache::has($cacheKey)) {
                    $prev = Cache::get($cacheKey);
                    $oldRows = $prev['rows'] ?? [];
                    $selectedEmpresaId = $prev['selected_empresa_id'] ?? null;
                    $empresaLocked = $prev['empresa_locked'] ?? false;
                    $globalCreditContaId = $prev['global_credit_conta_id'] ?? null;
                    $globalCreditContaLabel = $prev['global_credit_conta_label'] ?? null;
                    $globalCreditContaLocked = $prev['global_credit_conta_locked'] ?? false;
                }
                $spreadsheet = IOFactory::load($fullPath);
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestDataRow();
                $highestCol = $sheet->getHighestDataColumn();
                $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
                $removeCols = ['COL_4','COL_5','COL_6','COL_7'];
                $headerMap = [];
                for($col=1; $col <= $highestColIndex; $col++){
                    $val = trim((string)$sheet->getCellByColumnAndRow($col,1)->getValue());
                    if($val === '') $val = 'COL_'.$col; // fallback genérico
                    // Renomeações solicitadas: COL_2 -> HISTORICO, COL_3 -> VALOR, DESPESAS -> DATA
                    if($val === 'COL_2') { $val = 'HISTORICO'; }
                    if($val === 'COL_3') { $val = 'VALOR'; }
                    if($val === 'DESPESAS') { $val = 'DATA'; }
                    if(in_array($val,$removeCols,true)) continue;
                    $headers[] = $val;
                    $headerMap[$col] = $val;
                }
                $histKey = collect($headers)->first(fn($h)=> stripos($h,'HIST') !== false);
                for($row=2; $row <= $highestRow && count($rows) < $limite; $row++){
                    $linha = [];
                    $linhaVazia = true;
                    foreach($headerMap as $colIndex=>$hName){
                        $val = $sheet->getCellByColumnAndRow($colIndex,$row)->getFormattedValue();
                        if($val !== null && $val !== '') $linhaVazia = false;
                        if(is_string($val)){
                            if($flagTrimMulti){ $val = preg_replace('/\s+/u',' ',trim($val)); }
                            if($flagUpper){ $val = mb_strtoupper($val,'UTF-8'); }
                            if($subs){ foreach($subs as $find=>$rep){ if($find!=='') $val = str_replace($find,$rep,$val); } }
                            if($regexSubs){ foreach($regexSubs as $pattern=>$rep){ if(@preg_match($pattern,'') !== false){ $val = preg_replace($pattern,$rep,$val); } } }
                        }
                        $linha[$hName] = $val;
                    }
                    if($linhaVazia) break;
                    $linha['_hist_original_col'] = $histKey;
                    $linha['_hist_ajustado'] = $histKey ? $linha[$histKey] : null;
                    // Hash estável da linha (antes de ajustes de histórico posteriores) para reidratar classificação após refresh
                    if(!isset($linha['_row_hash'])){
                        // Nova estratégia: somente DATA + HISTORICO + VALOR (mais estável conforme solicitação)
                        $prefer = array_values(array_filter(['DATA','HISTORICO','VALOR'], fn($c)=> in_array($c,$headers,true)));
                        $baseCols = $prefer ?: $headers; // fallback para todos se não encontrados
                        $hashBase = [];
                        foreach($baseCols as $hx){ $hashBase[] = (string)($linha[$hx] ?? ''); }
                        $linha['_row_hash'] = sha1(implode('|',$hashBase));
                        // Hash legacy completo para compatibilidade de caches antigos
                        $legacyBase = [];
                        foreach($headers as $hxAll){ $legacyBase[] = (string)($linha[$hxAll] ?? ''); }
                        $linha['_row_hash_all'] = sha1(implode('|',$legacyBase));
                    }
                    // Campos de classificação (empresa/conta) default null até usuário selecionar (empresa única posteriormente)
                    $linha['_class_empresa_id'] = $selectedEmpresaId; // se já havia empresa selecionada global
                    // Não inicializamos _class_conta_id aqui para permitir restauração posterior; manter null se novo
                    $linha['_class_conta_id'] = $linha['_class_conta_id'] ?? null;
                    $rows[] = $linha;
                }
                // Tenta inferir Empresa e Conta Crédito a partir do arquivo (se for um export com colunas adicionais)
                if(in_array('EMPRESA_ID',$headers,true)){
                    foreach($rows as $rX){ if(!empty($rX['EMPRESA_ID'])){ $empresaIdFromFile = (int)$rX['EMPRESA_ID']; break; } }
                }
                if(in_array('CONTA_CREDITO_GLOBAL_ID',$headers,true)){
                    foreach($rows as $rX){ if(!empty($rX['CONTA_CREDITO_GLOBAL_ID'])){ $contaCreditoIdFromFile = (int)$rX['CONTA_CREDITO_GLOBAL_ID']; break; } }
                }
                // Fallback: se usuário só tem 1 empresa, usar essa como sugestão
                if(!$empresaIdFromFile && $empresasLista->count() === 1){
                    $empresaIdFromFile = (int)$empresasLista->first()->ID;
                }
                // Fallbacks adicionais a partir de contexto selecionado
                if(!$empresaIdFromFile && $selectedEmpresaId){ $empresaIdFromFile = (int)$selectedEmpresaId; }
                if(!$contaCreditoIdFromFile && $globalCreditContaId){ $contaCreditoIdFromFile = (int)$globalCreditContaId; }
                // Mescla ajustes antigos se usuário havia modificado (_hist_ajustado diferente do original)
                if($oldRows){
                    // Mapa por hash antigo para restauração independente de reordenação
                    $mapOld = [];
                    foreach($oldRows as $old){
                        if(!empty($old['_row_hash'])){ $mapOld[$old['_row_hash']] = $old; }
                        if(!empty($old['_row_hash_all']) && empty($mapOld[$old['_row_hash_all']])){ $mapOld[$old['_row_hash_all']] = $old; }
                    }
                    foreach($rows as $i => &$linhaNova){
                        $hash = $linhaNova['_row_hash'] ?? null;
                        $hashLegacy = $linhaNova['_row_hash_all'] ?? null;
                        $old = null;
                        if($hash && isset($mapOld[$hash])){ $old = $mapOld[$hash]; }
                        elseif($hashLegacy && isset($mapOld[$hashLegacy])){ $old = $mapOld[$hashLegacy]; }
                        else { $old = $oldRows[$i] ?? null; }
                        if(!$old) continue;
                        if(isset($old['_hist_ajustado'])){
                            $origColOld = $old['_hist_original_col'] ?? null;
                            $origValorOld = ($origColOld && isset($old[$origColOld])) ? $old[$origColOld] : null;
                            if($old['_hist_ajustado'] !== null && $old['_hist_ajustado'] !== $origValorOld){
                                $linhaNova['_hist_ajustado'] = $old['_hist_ajustado'];
                            }
                        }
                        if(isset($old['_class_empresa_id'])){ $linhaNova['_class_empresa_id'] = $old['_class_empresa_id']; }
                        if(isset($old['_class_conta_id'])){ $linhaNova['_class_conta_id'] = $old['_class_conta_id']; }
                    }
                    unset($linhaNova);
                }
                // Auto-classificação por tokens simples (apenas em reprocessamento forçado) - primeira ocorrência manda
                if($forceRefresh){
                    $tokenConta = []; // token => conta_id (primeira linha classificada que contém) – somente linhas com VALOR numérico
                    $stop = [ 'DE','DA','DO','PARA','EM','NO','NA','A','E','O','OS','AS','UM','UMA','DEBITO','DEB','CREDITO','PAGAMENTO','PAGTO','PAG','COMPRA','TRANSACAO','LANCTO','REF','DOC','NF','NOTA','CONTA','VALOR','DATA','HISTORICO' ];
                    $minLen = 4;
                    $minTokenHits = 1; // agora basta 1 token para auto-classificar
                    // Detecta coluna VALOR
                    $valorCol = null; foreach($headers as $hX){ if(mb_strtoupper($hX,'UTF-8')==='VALOR'){ $valorCol=$hX; break; } }
                    // Função para remover acentos (normalização) e manter só A-Z0-9 e espaços
                    $removeAccents = function($str){
                        $s = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$str);
                        if($s === false) $s = $str; // fallback
                        $s = preg_replace('/[^A-Z0-9 ]/',' ', mb_strtoupper($s,'UTF-8'));
                        return $s;
                    };
                    // 1) Construir mapa de tokens a partir de linhas já classificadas manualmente (com conta)
                    // Função local para validar valor numérico em formatos comuns ("1234.56", "1.234,56", "123,45")
                    $isValorNumerico = function($v){
                        if(is_numeric($v)) return true;
                        if(!is_string($v)) return false;
                        $vTrim = trim($v);
                        if($vTrim==='') return false;
                        // Padrão brasileiro com milhares e vírgula decimal
                        if(preg_match('/^\d{1,3}(\.\d{3})*,\d{2}$/',$vTrim)) return true;
                        // Simples com vírgula decimal
                        if(preg_match('/^\d+,\d+$/',$vTrim)) return true;
                        // Simples com ponto decimal
                        if(preg_match('/^\d+\.\d+$/',$vTrim)) return true;
                        // Inteiro
                        if(preg_match('/^\d+$/',$vTrim)) return true;
                        return false;
                    };
                    foreach($rows as $r){
                        if(empty($r['_class_conta_id'])) continue;
                        if($valorCol){ $valTmp = $r[$valorCol] ?? null; if(!$isValorNumerico($valTmp)) continue; }
                        $hist = $r['_hist_ajustado'] ?? ($r['_hist_original_col'] ? ($r[$r['_hist_original_col']] ?? null) : null);
                        if(!is_string($hist) || $hist==='') continue;
                        $norm = $removeAccents($hist);
                        $norm = preg_replace('/[\.,;:!\-\(\)\[\]\/\\]+/u',' ', $norm); // redundante pós-normalização mas mantém limpeza
                        $parts = preg_split('/\s+/u',$norm,-1,PREG_SPLIT_NO_EMPTY);
                        $seen = [];
                        foreach($parts as $tok){
                            if(isset($seen[$tok])) continue; // evita duplicar no mesmo histórico
                            $seen[$tok]=true;
                            if(mb_strlen($tok,'UTF-8') < $minLen) continue;
                            if(in_array($tok,$stop,true)) continue;
                            if(preg_match('/^\d+$/',$tok)) continue;
                            if(!isset($tokenConta[$tok])){ // primeira ocorrência fixa
                                $tokenConta[$tok] = $r['_class_conta_id'];
                            }
                        }
                    }
                    if($tokenConta){
                        // 2) Percorrer linhas ainda sem conta e atribuir se encontrar qualquer token mapeado (primeiro match)
                        foreach($rows as &$r2){
                            if(!empty($r2['_class_conta_id'])) continue;
                            if($valorCol){ $valTmp2 = $r2[$valorCol] ?? null; if(!$isValorNumerico($valTmp2)) continue; }
                            $hist2 = $r2['_hist_ajustado'] ?? ($r2['_hist_original_col'] ? ($r2[$r2['_hist_original_col']] ?? null) : null);
                            if(!is_string($hist2) || $hist2==='') continue;
                            $hay = $removeAccents($hist2);
                            foreach($tokenConta as $tok=>$cid){
                                if(strpos($hay,$tok)!==false){
                                    $r2['_class_conta_id'] = $cid;
                                    $r2['_auto_classified'] = true;
                                    $r2['_auto_hits'] = 1;
                                    break; // primeira correspondência basta
                                }
                            }
                        }
                        unset($r2);
                        // 3) Resolver labels faltantes das contas atribuídas automaticamente
                        $idsNeed = [];
                        foreach($rows as $r3){
                            if(!empty($r3['_class_conta_id']) && empty($r3['_class_conta_label'])){ $idsNeed[$r3['_class_conta_id']] = true; }
                        }
                        if($idsNeed){
                            $labels = Conta::whereIn('Contas.ID', array_keys($idsNeed))
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->pluck('PlanoContas.Descricao','Contas.ID');
                            foreach($rows as &$r4){
                                if(!empty($r4['_class_conta_id']) && empty($r4['_class_conta_label'])){
                                    $cid = $r4['_class_conta_id'];
                                    if(isset($labels[$cid])) $r4['_class_conta_label'] = $labels[$cid];
                                }
                            }
                            unset($r4);
                        }
                    }
                }
                Cache::put($cacheKey, [
                    'headers'=>$headers,
                    'rows'=>$rows,
                    'file'=>$file,
                    'generated_at'=>now()->toDateTimeString(),
                    'selected_empresa_id'=>$selectedEmpresaId,
                    'empresa_locked'=>$empresaLocked,
                    'global_credit_conta_id'=>$globalCreditContaId,
                    'global_credit_conta_label'=>$globalCreditContaLabel,
                    'global_credit_conta_locked'=>$globalCreditContaLocked,
                ], now()->addHour());
            } catch(\Throwable $e){
                $erro = 'Falha ao ler planilha: '.$e->getMessage();
            }
        }
        return view('Lancamentos.preview-despesas', [
            'arquivo' => $file,
            'existe' => $exists,
            'headers' => $headers,
            'rows' => $rows,
            'erro' => $erro,
            'limite' => $limite,
            'flagUpper' => $flagUpper,
            'flagTrimMulti' => $flagTrimMulti,
            'subsRaw' => $subsRaw,
            'regexRaw' => $regexRaw,
            'cacheKey' => $cacheKey,
            'empresasLista' => $empresasLista,
            'selectedEmpresaId' => $selectedEmpresaId,
            'empresaLocked' => $empresaLocked,
            'globalCreditContaId' => $globalCreditContaId,
            'globalCreditContaLabel' => $globalCreditContaLabel,
            'globalCreditContaLocked' => $globalCreditContaLocked,
            'empresaIdFromFile' => $empresaIdFromFile,
            'contaCreditoIdFromFile' => $contaCreditoIdFromFile,
        ]);
    }

    /**
     * Atualiza classificação (empresa/conta) de uma linha na pré-visualização
     */
    public function updatePreviewDespesasClassificacao(Request $request)
    {
        $data = $request->validate([
            'cache_key' => 'required|string',
            'row' => 'required|integer',
            'empresa_id' => 'nullable|integer',
            'conta_id' => 'nullable|integer',
        ]);
        $cacheKey = $data['cache_key'];
        if(!Cache::has($cacheKey)){
            return response()->json(['ok'=>false,'message'=>'Cache expirado']);
        }
        $payload = Cache::get($cacheKey);
        $rows = $payload['rows'] ?? [];
        $i = $data['row'];
        if(!isset($rows[$i])){
            return response()->json(['ok'=>false,'message'=>'Linha inexistente']);
        }
        $empresaId = $data['empresa_id'] ?? null;
        $contaId = $data['conta_id'] ?? null;
        // Se há uma empresa global selecionada, força uso dela.
        $selectedEmpresaGlobal = $payload['selected_empresa_id'] ?? null;
        if($selectedEmpresaGlobal){
            $empresaId = $selectedEmpresaGlobal; // ignora empresa diferente recebida
        }
        // valida acesso a empresa se informada
        if($empresaId){
            $allowed = Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
                ->where('EmpresasUsuarios.UsuarioID', auth()->id())
                ->where('Empresas.ID',$empresaId)
                ->exists();
            if(!$allowed){
                return response()->json(['ok'=>false,'message'=>'Empresa não autorizada'],403);
            }
        }
        // valida conta pertence à empresa (quando ambos presentes)
        if($empresaId && $contaId){
            $contaOk = Conta::where('ID',$contaId)->where('EmpresaID',$empresaId)->exists();
            if(!$contaOk){
                return response()->json(['ok'=>false,'message'=>'Conta não pertence à empresa selecionada'],422);
            }
        }
        $rows[$i]['_class_empresa_id'] = $empresaId;
        $rows[$i]['_class_conta_id'] = $contaId;
        // Salva label da conta para reidratar sem depender de fetch imediato
        if($contaId){
            $label = Conta::where('Contas.ID',$contaId)
                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                ->value('PlanoContas.Descricao');
            if($label){ $rows[$i]['_class_conta_label'] = $label; }
        } else {
            $rows[$i]['_class_conta_label'] = null;
        }
        $payload['rows'] = $rows;
        Cache::put($cacheKey,$payload, now()->addHour());
        return response()->json(['ok'=>true]);
    }

    /**
     * Define a empresa global para a pré-visualização (resetando contas)
     */
    public function updatePreviewDespesasEmpresa(Request $request)
    {
        $data = $request->validate([
            'cache_key' => 'required|string',
            'empresa_id' => 'required|integer',
        ]);
        $cacheKey = $data['cache_key'];
        if(!Cache::has($cacheKey)){
            return response()->json(['ok'=>false,'message'=>'Cache expirado']);
        }
        $empresaId = (int)$data['empresa_id'];
        $allowed = Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->id())
            ->where('Empresas.ID',$empresaId)
            ->exists();
        if(!$allowed){
            return response()->json(['ok'=>false,'message'=>'Empresa não autorizada'],403);
        }
        $payload = Cache::get($cacheKey);
        // Bloqueia troca se travado e já havia empresa definida diferente
        if(($payload['empresa_locked'] ?? false) && isset($payload['selected_empresa_id']) && $payload['selected_empresa_id'] && $payload['selected_empresa_id'] != $empresaId){
            return response()->json(['ok'=>false,'message'=>'Empresa travada. Destrave antes de alterar.'],423);
        }
        // Se já era a mesma empresa, não resetamos contas
        $already = isset($payload['selected_empresa_id']) && (int)$payload['selected_empresa_id'] === $empresaId;
        $rows = $payload['rows'] ?? [];
        foreach($rows as &$r){
            $r['_class_empresa_id'] = $empresaId;
            if(!$already){
                // Apenas em mudança real de empresa zeramos contas
                $r['_class_conta_id'] = null;
                $r['_class_conta_label'] = null;
            }
        }
        unset($r);
        $payload['rows'] = $rows;
        $payload['selected_empresa_id'] = $empresaId;
        if(!$already){
            // Zera conta crédito global se empresa mudou
            $payload['global_credit_conta_id'] = null;
            $payload['global_credit_conta_label'] = null;
        }
        Cache::put($cacheKey,$payload, now()->addHour());
        return response()->json(['ok'=>true,'reset_contas'=> !$already]);
    }

    /**
     * Travar / destravar seleção global de empresa no preview (persistido em cache)
     */
    public function togglePreviewDespesasEmpresaLock(Request $request)
    {
        $data = $request->validate([
            'cache_key' => 'required|string',
            'locked' => 'required|boolean',
        ]);
        $cacheKey = $data['cache_key'];
        if(!Cache::has($cacheKey)){
            return response()->json(['ok'=>false,'message'=>'Cache expirado']);
        }
        $payload = Cache::get($cacheKey);
        // Se solicitou travar mas ainda não há empresa escolhida, rejeita (evita estado inválido)
        if($data['locked'] && empty($payload['selected_empresa_id'])){
            return response()->json(['ok'=>false,'message'=>'Selecione uma empresa antes de travar'],422);
        }
        $payload['empresa_locked'] = (bool)$data['locked'];
        Cache::put($cacheKey,$payload, now()->addHour());
        return response()->json(['ok'=>true,'locked'=>$payload['empresa_locked']]);
    }

    /**
     * AJAX: atualiza histórico ajustado em cache.
     */
    public function updatePreviewDespesasHistorico(Request $request)
    {
        $data = $request->validate([
            'cache_key' => 'required|string',
            'row' => 'required|integer|min:0',
            'valor' => 'nullable|string'
        ]);
    $payload = Cache::get($data['cache_key']);
        if(!$payload){
            return response()->json(['ok'=>false,'message'=>'Cache expirado. Recarregue a página.'], 410);
        }
        if(!isset($payload['rows'][$data['row']])){
            return response()->json(['ok'=>false,'message'=>'Linha inválida'], 422);
        }
        $histCol = $payload['rows'][$data['row']]['_hist_original_col'] ?? null;
        $payload['rows'][$data['row']]['_hist_ajustado'] = $data['valor'];
        if($histCol && array_key_exists($histCol,$payload['rows'][$data['row']])){
            // Atualiza também a coluna original para refletir ajuste (facilita export futuro)
            $payload['rows'][$data['row']][$histCol] = $data['valor'];
        }
        $payload['updated_at'] = now()->toDateTimeString();
    Cache::put($data['cache_key'], $payload, now()->addHour());
        return response()->json(['ok'=>true,'updated_at'=>$payload['updated_at']]);
    }

    /**
     * Snapshot manual: recebe lista de linhas com conta e histórico ajustado e grava em cache em lote.
     */
    public function updatePreviewDespesasSnapshot(Request $request)
    {
        $data = $request->validate([
            'cache_key' => 'required|string',
            'rows' => 'required|array',
            'rows.*.i' => 'required|integer|min:0',
            'rows.*.conta_id' => 'nullable|integer',
            'rows.*.conta_label' => 'nullable|string',
            'rows.*.hist_ajustado' => 'nullable|string',
            'rows.*.data' => 'nullable|string'
            ,'global_credit_conta_id' => 'nullable|integer'
            ,'global_credit_conta_label' => 'nullable|string'
        ]);
        if(!Cache::has($data['cache_key'])){
            return response()->json(['ok'=>false,'message'=>'Cache expirado'],410);
        }
        $payload = Cache::get($data['cache_key']);
        $rows = $payload['rows'] ?? [];
        foreach($data['rows'] as $r){
            $i = $r['i'];
            if(!isset($rows[$i])) continue;
            if(array_key_exists('hist_ajustado',$r)){
                $rows[$i]['_hist_ajustado'] = $r['hist_ajustado'];
                $histCol = $rows[$i]['_hist_original_col'] ?? null;
                if($histCol && isset($rows[$i][$histCol])){
                    $rows[$i][$histCol] = $r['hist_ajustado'];
                }
            }
            // Atualiza DATA se enviado
            if(array_key_exists('data',$r)){
                // Se existir a coluna DATA no conjunto de headers desta cache, atualiza-a
                if(isset($rows[$i]['DATA'])){
                    $rows[$i]['DATA'] = $r['data'];
                } else {
                    // fallback: tenta encontrar chave que contenha 'DATA' (caso variação de header)
                    foreach(array_keys($rows[$i]) as $k){
                        if(is_string($k) && stripos($k,'DATA') !== false && strpos($k,'_') !== 0){
                            $rows[$i][$k] = $r['data']; break;
                        }
                    }
                }
            }
            if(array_key_exists('conta_id',$r)){
                $rows[$i]['_class_conta_id'] = $r['conta_id'];
                $rows[$i]['_class_conta_label'] = $r['conta_label'] ?? null;
            }
        }
        $payload['rows'] = $rows;
        // Atualiza conta crédito global se enviada
        if(array_key_exists('global_credit_conta_id',$data)){
            $payload['global_credit_conta_id'] = $data['global_credit_conta_id'];
            $payload['global_credit_conta_label'] = $data['global_credit_conta_label'] ?? null;
        }
        $payload['updated_at'] = now()->toDateTimeString();
        Cache::put($data['cache_key'],$payload, now()->addHour());
        return response()->json(['ok'=>true,'updated_at'=>$payload['updated_at']]);
    }

    /**
     * Executa auto-classificação on-demand nas linhas pendentes.
     */
    public function applyPreviewDespesasAutoClass(Request $request){
        $dataReq = $request->validate([
            'cache_key' => 'required|string'
        ]);
        $cacheKey = $dataReq['cache_key'];
        $payload = Cache::get($cacheKey);
        if(!$payload || empty($payload['rows'])){
            return response()->json(['ok'=>false,'message'=>'Nenhum cache para processar']);
        }
    $rows =& $payload['rows'];
    // Detecta coluna VALOR
    $valorCol = null; $headersLocal = $payload['headers'] ?? [];
    foreach($headersLocal as $hX){ if(mb_strtoupper($hX,'UTF-8')==='VALOR'){ $valorCol=$hX; break; } }
        $tokenConta = [];
        $stop = [ 'DE','DA','DO','PARA','EM','NO','NA','A','E','O','OS','AS','UM','UMA','DEBITO','DEB','CREDITO','PAGAMENTO','PAGTO','PAG','COMPRA','TRANSACAO','LANCTO','REF','DOC','NF','NOTA','CONTA','VALOR','DATA','HISTORICO' ];
        $minLen = 4;
    $minTokenHits = 1; // modo on-demand: 1 token é suficiente
        $isValorNumerico = function($v){
            if(is_numeric($v)) return true;
            if(!is_string($v)) return false; $vTrim=trim($v); if($vTrim==='') return false;
            if(preg_match('/^\d{1,3}(\.\d{3})*,\d{2}$/',$vTrim)) return true;
            if(preg_match('/^\d+,\d+$/',$vTrim)) return true;
            if(preg_match('/^\d+\.\d+$/',$vTrim)) return true;
            if(preg_match('/^\d+$/',$vTrim)) return true;
            return false; };
        $removeAccents = function($str){ $s = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$str); if($s===false) $s=$str; return preg_replace('/[^A-Z0-9 ]/',' ', mb_strtoupper($s,'UTF-8')); };
        $n = count($rows); $applied=0;
        for($iSrc=0;$iSrc<$n;$iSrc++){
            $src =& $rows[$iSrc];
            if(empty($src['_class_conta_id'])) continue;
            if(!empty($src['_auto_classified'])) continue; // evita cascata
            if($valorCol){ $vSrc = $src[$valorCol] ?? null; if(!$isValorNumerico($vSrc)) continue; }
            $histSrc = $src['_hist_ajustado'] ?? null;
            if(!is_string($histSrc) || $histSrc==='') continue;
            $norm = $removeAccents($histSrc);
            $parts = preg_split('/\s+/u',$norm,-1,PREG_SPLIT_NO_EMPTY);
            $tokens = [];
            foreach($parts as $p){ if(mb_strlen($p,'UTF-8')>4) $tokens[$p]=true; }
            if(!$tokens) continue;
            for($j=$iSrc+1;$j<$n;$j++){
                $tgt =& $rows[$j];
                if(!empty($tgt['_class_conta_id'])) continue;
                if($valorCol){ $vT = $tgt[$valorCol] ?? null; if(!$isValorNumerico($vT)) continue; }
                $histT = $tgt['_hist_ajustado'] ?? null;
                if(!is_string($histT) || $histT==='') continue;
                $hay = $removeAccents($histT);
                $matched=false; foreach($tokens as $tk=>$_1){ if(strpos($hay,$tk)!==false){ $matched=true; break; } }
                if($matched){
                    $tgt['_class_conta_id'] = $src['_class_conta_id'];
                    $tgt['_auto_classified'] = true;
                    $tgt['_auto_hits'] = 1;
                    $applied++;
                }
            }
            unset($tgt);
        }
        unset($src);
        // Preencher labels faltantes
        $need=[]; foreach($rows as $rX){ if(!empty($rX['_class_conta_id']) && empty($rX['_class_conta_label'])) $need[$rX['_class_conta_id']]=true; }
        if($need){
            $labels = Conta::whereIn('Contas.ID', array_keys($need))
                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                ->pluck('PlanoContas.Descricao','Contas.ID');
            foreach($rows as &$rY){ if(!empty($rY['_class_conta_id']) && empty($rY['_class_conta_label']) && isset($labels[$rY['_class_conta_id']])) $rY['_class_conta_label']=$labels[$rY['_class_conta_id']]; }
            unset($rY);
        }
        $payload['rows']=$rows; Cache::put($cacheKey,$payload, now()->addHour());
        return response()->json(['ok'=>true,'applied'=>$applied]);
    }

    /**
     * Define conta crédito global usada nas classificações (linhas são débito).
     */
    public function updatePreviewDespesasContaCredito(Request $request){
        $data = $request->validate([
            'cache_key' => 'required|string',
            'conta_id' => 'nullable|integer'
        ]);
        if(!Cache::has($data['cache_key'])){
            return response()->json(['ok'=>false,'message'=>'Cache expirado'],410);
        }
        $payload = Cache::get($data['cache_key']);
        $empresaId = $payload['selected_empresa_id'] ?? null;
        $contaId = $data['conta_id'] ? (int)$data['conta_id'] : null;
        if(($payload['global_credit_conta_locked'] ?? false) && $contaId && $contaId !== ($payload['global_credit_conta_id'] ?? null)){
            return response()->json(['ok'=>false,'message'=>'Conta crédito travada. Destrave para alterar.'],423);
        }
        if($contaId){
            if(!$empresaId){
                return response()->json(['ok'=>false,'message'=>'Selecione empresa antes da conta crédito'],422);
            }
            $valida = Conta::where('Contas.ID',$contaId)->where('Contas.EmpresaID',$empresaId)->exists();
            if(!$valida){
                return response()->json(['ok'=>false,'message'=>'Conta não pertence à empresa selecionada'],422);
            }
            $label = Conta::where('Contas.ID',$contaId)
                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                ->value('PlanoContas.Descricao');
            $payload['global_credit_conta_id'] = $contaId;
            $payload['global_credit_conta_label'] = $label ?: null;
        } else {
            $payload['global_credit_conta_id'] = null;
            $payload['global_credit_conta_label'] = null;
        }
        Cache::put($data['cache_key'],$payload, now()->addHour());
        return response()->json(['ok'=>true,'conta_id'=>$payload['global_credit_conta_id'],'label'=>$payload['global_credit_conta_label']]);
    }

    /**
     * Travar / destravar conta crédito global.
     */
    public function togglePreviewDespesasContaCreditoLock(Request $request){
        $data = $request->validate([
            'cache_key' => 'required|string',
            'locked' => 'required|boolean'
        ]);
        if(!Cache::has($data['cache_key'])){
            return response()->json(['ok'=>false,'message'=>'Cache expirado'],410);
        }
        $payload = Cache::get($data['cache_key']);
        if($data['locked']){
            // travar exige conta crédito já definida
            if(empty($payload['global_credit_conta_id'])){
                return response()->json(['ok'=>false,'message'=>'Defina a conta crédito antes de travar'],422);
            }
        }
        $payload['global_credit_conta_locked'] = (bool)$data['locked'];
        Cache::put($data['cache_key'],$payload, now()->addHour());
        return response()->json(['ok'=>true,'locked'=>$payload['global_credit_conta_locked']]);
    }

    /**
     * Exporta a pré-visualização (cache) para XLSX mantendo todos os campos necessários para futura importação.
     * GET com ?cache_key=...
     */
    public function exportPreviewDespesasExcel(Request $request)
    {
        $cacheKey = $request->query('cache_key');
        if(!$cacheKey){
            abort(422,'cache_key obrigatório');
        }
        if(!Cache::has($cacheKey)){
            abort(410,'Cache expirado ou inexistente. Recarregue a visualização.');
        }
        $payload = Cache::get($cacheKey);
        $headersOrig = $payload['headers'] ?? [];
        $rowsCache = $payload['rows'] ?? [];
        $empresaIdGlobal = $payload['selected_empresa_id'] ?? null;
        $creditId = $payload['global_credit_conta_id'] ?? null;
        $creditLabel = $payload['global_credit_conta_label'] ?? null;

        // Construir conjunto de headings: colunas originais + quaisquer chaves presentes nas linhas (não internas) + adicionais padronizados no final
        $extraCols = [
            'HISTORICO_AJUSTADO',
            'DATA_NORMALIZADA',
            'EMPRESA_ID',
            'CONTA_DEBITO_ID',
            'CONTA_DEBITO_LABEL',
            'CONTA_CREDITO_GLOBAL_ID',
            'CONTA_CREDITO_GLOBAL_LABEL',
            'AUTO_CLASSIFIED',
            'AUTO_HITS',
            'ROW_HASH'
        ];
        // Coleta dinâmica de chaves presentes nas linhas (exclui internas "_...")
        $dynamicKeys = [];
        foreach($rowsCache as $r){
            foreach(array_keys($r) as $k){
                if(is_string($k) && strpos($k,'_') !== 0 && !in_array($k,$dynamicKeys,true)){
                    $dynamicKeys[] = $k;
                }
            }
        }
        // Headings: originais + dinâmicas + extras, sem duplicatas
        $headings = array_values(array_unique(array_merge($headersOrig, $dynamicKeys, $extraCols)));

        $exportRows = [];
        // Helpers de data para normalização (dd/mm/YYYY)
        $fmtBR = function($y,$m,$d){ return sprintf('%02d/%02d/%04d',(int)$d,(int)$m,(int)$y); };
        $isValid = function($y,$m,$d){ return checkdate((int)$m,(int)$d,(int)$y); };
        $fromSerial = function($n) use ($fmtBR){
            // Excel 1900 date system: base 1899-12-30
            $base = new \DateTimeImmutable('1899-12-30');
            $dt = $base->modify("+".((int)round($n))." days");
            return $fmtBR((int)$dt->format('Y'), (int)$dt->format('m'), (int)$dt->format('d'));
        };
        $normalizeDate = function($raw) use ($fmtBR,$isValid,$fromSerial){
            if($raw instanceof \DateTimeInterface){ return $raw->format('d/m/Y'); }
            if($raw===null) return null; $v = trim((string)$raw); if($v==='') return null;
            // Remove componente de hora (" 12:34", "T12:34:56Z", etc.)
            $v = preg_replace('/[T ]\d{1,2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?$/','',$v);
            // Serial Excel
            if(preg_match('/^\d{2,6}$/',$v)){
                $n = (int)$v; if($n>59 && $n<60000){ return $fromSerial($n); }
            }
            // Normaliza separadores
            $vSep = preg_replace('/[\.\-]/','/',$v); $vSep = preg_replace('/\s+/','/',$vSep);
            // yyyy/mm/dd
            if(preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/',$vSep,$m)){
                $y=(int)$m[1]; $mm=(int)$m[2]; $d=(int)$m[3]; if($isValid($y,$mm,$d)) return $fmtBR($y,$mm,$d);
            }
            // dd/mm/yyyy ou d/m/yy
            if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/',$vSep,$m)){
                $d=(int)$m[1]; $mm=(int)$m[2]; $y=$m[3]; if(strlen($y)===2){ $y = (int)('20'.$y); } else { $y=(int)$y; }
                if($isValid($y,$mm,$d)) return $fmtBR($y,$mm,$d);
            }
            // yyyymmdd
            if(preg_match('/^(\d{4})(\d{2})(\d{2})$/',$v,$m)){
                $y=(int)$m[1]; $mm=(int)$m[2]; $d=(int)$m[3]; if($isValid($y,$mm,$d)) return $fmtBR($y,$mm,$d);
            }
            // ddmmyyyy
            if(preg_match('/^(\d{2})(\d{2})(\d{4})$/',$v,$m)){
                $d=(int)$m[1]; $mm=(int)$m[2]; $y=(int)$m[3]; if($isValid($y,$mm,$d)) return $fmtBR($y,$mm,$d);
            }
            return null;
        };
        // Descobrir coluna de data preferencial ('DATA' exata ou primeira que contém 'DATA')
        $dateCol = null; foreach($headersOrig as $h){ if(mb_strtoupper($h,'UTF-8')==='DATA'){ $dateCol=$h; break; } }
        if(!$dateCol){ foreach($headersOrig as $h){ if(stripos($h,'DATA')!==false && mb_strtoupper($h,'UTF-8')!=='DATA_NORMALIZADA'){ $dateCol=$h; break; } } }

        foreach($rowsCache as $r){
            $linha = [];
            // Preenche todas as colunas de saída respeitando prioridade: valor existente no row -> derivação padrão
            foreach($headings as $h){
                $val = $r[$h] ?? null;
                if($val instanceof \DateTimeInterface){ $val = $val->format('d/m/Y'); }
                $linha[$h] = $val; // inicialmente copia se existir
            }
            // Preencher campos padronizados quando ausentes
            if(!array_key_exists('HISTORICO_AJUSTADO',$linha) || $linha['HISTORICO_AJUSTADO'] === null){
                $linha['HISTORICO_AJUSTADO'] = $r['_hist_ajustado'] ?? null;
            }
            // DATA_NORMALIZADA a partir da coluna de data preferida
            if(!array_key_exists('DATA_NORMALIZADA',$linha) || empty($linha['DATA_NORMALIZADA'])){
                $src = $dateCol ? ($r[$dateCol] ?? null) : null;
                $linha['DATA_NORMALIZADA'] = $normalizeDate($src);
            }
            if(!array_key_exists('EMPRESA_ID',$linha) || empty($linha['EMPRESA_ID'])){
                $linha['EMPRESA_ID'] = $r['_class_empresa_id'] ?? $empresaIdGlobal;
            }
            if(!array_key_exists('CONTA_DEBITO_ID',$linha) || empty($linha['CONTA_DEBITO_ID'])){
                $linha['CONTA_DEBITO_ID'] = $r['_class_conta_id'] ?? null;
            }
            if(!array_key_exists('CONTA_DEBITO_LABEL',$linha) || empty($linha['CONTA_DEBITO_LABEL'])){
                $linha['CONTA_DEBITO_LABEL'] = $r['_class_conta_label'] ?? null;
            }
            if(!array_key_exists('CONTA_CREDITO_GLOBAL_ID',$linha) || empty($linha['CONTA_CREDITO_GLOBAL_ID'])){
                $linha['CONTA_CREDITO_GLOBAL_ID'] = $creditId;
            }
            if(!array_key_exists('CONTA_CREDITO_GLOBAL_LABEL',$linha) || empty($linha['CONTA_CREDITO_GLOBAL_LABEL'])){
                $linha['CONTA_CREDITO_GLOBAL_LABEL'] = $creditLabel;
            }
            $linha['AUTO_CLASSIFIED'] = !empty($r['_auto_classified']) ? 1 : 0;
            $linha['AUTO_HITS'] = $r['_auto_hits'] ?? null;
            $linha['ROW_HASH'] = $r['_row_hash'] ?? null;
            $exportRows[] = $linha;
        }

        $fileName = 'preview-despesas-'.date('Ymd-His').'.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new PreviewDespesasExport($exportRows,$headings), $fileName);
    }

    /**
     * Importa arquivo gerado pela exportação de preview e recria um cache para continuar edição.
     */
    public function importPreviewDespesasExportado(Request $request)
    {
        $request->validate([
            'arquivo_exportado' => 'required|file|mimes:xlsx,xls|max:8192'
        ]);
        $file = $request->file('arquivo_exportado');
        $orig = $file->getClientOriginalName();
        $storedName = 'reimport-'.$orig;
        $file->storeAs('imports',$storedName);
        $path = storage_path('app/imports/'.$storedName);
        try{
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $highestCol = $sheet->getHighestDataColumn();
            $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
            $headersAll=[]; for($c=1;$c<=$highestColIndex;$c++){ $v=trim((string)$sheet->getCellByColumnAndRow($c,1)->getValue()); if($v==='') $v='COL_'.$c; $headersAll[]=$v; }
            $required=['HISTORICO_AJUSTADO','EMPRESA_ID','CONTA_DEBITO_ID','CONTA_CREDITO_GLOBAL_ID','ROW_HASH'];
            foreach($required as $req){ if(!in_array($req,$headersAll,true)){ return back()->with('error','Arquivo inválido: faltando coluna '.$req); } }
            // Manter colunas úteis do export no cabeçalho da visualização (para reimportações):
            // HISTORICO_AJUSTADO, EMPRESA_ID, CONTA_DEBITO_ID/LABEL, CONTA_CREDITO_GLOBAL_ID/LABEL
            // Remover apenas auxiliares de diagnóstico/controle
            $dropOnly = ['AUTO_CLASSIFIED','AUTO_HITS','ROW_HASH'];
            $headersOrig = array_values(array_filter($headersAll, fn($h)=> !in_array($h,$dropOnly,true)));
            // Detectar coluna original de histórico (evitar escolher HISTORICO_AJUSTADO)
            $histOrigCol = null;
            foreach($headersOrig as $h){
                $u = mb_strtoupper($h,'UTF-8');
                if($u === 'HISTORICO_AJUSTADO') continue;
                if(strpos($u,'HIST')!==false){ $histOrigCol=$h; break; }
            }
            $rows=[]; $empresaGlobal=null; $creditId=null; $creditLabel=null;
            $getVal = function($row,$colName) use ($headersAll,$sheet){ $idx=array_search($colName,$headersAll,true); if($idx===false) return null; $val=$sheet->getCellByColumnAndRow($idx+1,$row)->getValue(); if($val instanceof \DateTimeInterface){ return $val->format('d/m/Y'); } return $val; };
            for($r=2;$r<=$highestRow;$r++){
                $linha=[]; foreach($headersOrig as $h){ $idx=array_search($h,$headersAll,true); $val=$sheet->getCellByColumnAndRow($idx+1,$r)->getValue(); if($val instanceof \DateTimeInterface){ $val=$val->format('d/m/Y'); } $linha[$h]=$val; }
                $histAjust=$getVal($r,'HISTORICO_AJUSTADO');
                $emp=$getVal($r,'EMPRESA_ID');
                $contaDeb=$getVal($r,'CONTA_DEBITO_ID');
                $contaDebLabel=$getVal($r,'CONTA_DEBITO_LABEL');
                $cred=$getVal($r,'CONTA_CREDITO_GLOBAL_ID');
                $credLabel=$getVal($r,'CONTA_CREDITO_GLOBAL_LABEL');
                $autoClass=(int)$getVal($r,'AUTO_CLASSIFIED')===1; $autoHits=$getVal($r,'AUTO_HITS');
                $rowHash=$getVal($r,'ROW_HASH');
                if($emp && $empresaGlobal===null) $empresaGlobal=$emp;
                if($cred && $creditId===null){ $creditId=$cred; $creditLabel=$credLabel; }
                $linha['_hist_original_col']=$histOrigCol;
                $linha['_hist_ajustado']=$histAjust;
                $linha['_class_empresa_id']=$emp ?: $empresaGlobal;
                $linha['_class_conta_id']=$contaDeb ?: null;
                if($contaDebLabel) $linha['_class_conta_label']=$contaDebLabel;
                // Se o arquivo traz "CONTA_DEBITO_ID" e a view manterá essa coluna (agora mantemos), deixamos também espelhado no dado original para fallback no validador
                if(!empty($contaDeb)){
                    $linha['CONTA_DEBITO_ID'] = $contaDeb;
                    if($contaDebLabel) $linha['CONTA_DEBITO_LABEL'] = $contaDebLabel;
                }
                if($autoClass){ $linha['_auto_classified']=true; $linha['_auto_hits']=$autoHits ?: 1; }
                if($rowHash){ $linha['_row_hash']=$rowHash; $linha['_row_hash_all']=$rowHash; }
                $rows[]=$linha;
            }
            $cacheKey='preview_despesas:'.auth()->id().':'.md5(json_encode([$storedName,count($rows),false,false,null,null]));
            Cache::put($cacheKey,[
                'headers'=>$headersOrig,
                'rows'=>$rows,
                'file'=>$storedName,
                'generated_at'=>now()->toDateTimeString(),
                'selected_empresa_id'=>$empresaGlobal,
                'empresa_locked'=>false,
                'global_credit_conta_id'=>$creditId,
                'global_credit_conta_label'=>$creditLabel,
                'global_credit_conta_locked'=>false,
                'imported_from_export'=>true
            ], now()->addHour());
            return redirect()->route('lancamentos.preview.despesas',[ 'file'=>$storedName, 'limite'=>count($rows) ])->with('status','Export importado com sucesso.');
        }catch(\Throwable $e){
            return back()->with('error','Falha ao importar: '.$e->getMessage());
        }
    }


// (fim dos métodos de LancamentosController)
}
