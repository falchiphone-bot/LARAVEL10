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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
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
    /**
     * Grava o cache da pré-visualização respeitando config/preview.php
     * - Se preview.cache_forever=true, usa Cache::forever()
     * - Caso contrário, usa TTL em segundos (preview.cache_ttl_seconds)
     * Fallback: 1h se ocorrer falha ao ler config
     */
    protected function previewCachePut(string $key, array $payload): void
    {
        try {
            $forever = (bool) config('preview.cache_forever', false);
            if ($forever) {
                \Illuminate\Support\Facades\Cache::forever($key, $payload);
                return;
            }
            $ttl = (int) config('preview.cache_ttl_seconds', 3600);
            if ($ttl <= 0) {
                \Illuminate\Support\Facades\Cache::forever($key, $payload);
                return;
            }
            \Illuminate\Support\Facades\Cache::put($key, $payload, now()->addSeconds($ttl));
        } catch (\Throwable $e) {
            // fallback seguro: 1 hora
            \Illuminate\Support\Facades\Cache::put($key, $payload, now()->addHour());
        }
    }

     public function DadosMes()
     {
        $DadosMes = session('DadosMes');
        $nome = session('nome');
        return view('Lancamentos.DadosMes', compact('DadosMes','nome'));
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

            // dd($tabelaParcelas, $debito->ID, $credito->ID, $Empresa, $DataInicialSomada);
            foreach($tabelaParcelas as $EfetuarLancamento){




                $data = $EfetuarLancamento['datasomada'];
                // Garante instância de Carbon e formata ISO compatível com SQL Server (YYYY-MM-DD)
                $dataCarbon = ($data instanceof \Carbon\Carbon) ? $data : \Carbon\Carbon::parse($data);
                $dataIso = $dataCarbon->format('Y-m-d');

                $datacontabil = $EfetuarLancamento['datasomada'];
                $valorString = $EfetuarLancamento['Total'];

                $lancamentoLocalizado = Lancamento::whereDate('DataContabilidade', '=', $dataIso)
                ->where('Valor', $EfetuarLancamento['Total'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaDebitoID', $EfetuarLancamento['debito'])
                ->First();

                if($lancamentoLocalizado){
                    // Já temos $dataCarbon acima
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

                if ($data_conta_debito_bloqueio->greaterThanOrEqualTo($dataCarbon)) {
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

                if ($data_conta_credito_bloqueio->greaterThanOrEqualTo($dataCarbon)) {
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
                // Criar lançamento somente se não existir um com mesmos dados (não duplicar)
                if(!$lancamentoLocalizado){
                    // dd("Lancando ", $datacontabil,$EfetuarLancamento['Total'], $Empresa, $EfetuarLancamento['debito'] );

                    $dataSalvar = Carbon::createFromDate($EfetuarLancamento['datainicial']);

                    $LancamentoParcela[] = Lancamento::create([
                    'Valor' => ($valorString = $EfetuarLancamento['Total']),
                    'EmpresaID' => $EfetuarLancamento['empresa'],
                    'ContaDebitoID' => $EfetuarLancamento['debito'],
                    'ContaCreditoID' => $EfetuarLancamento['credito'],
                    'Descricao' => $EfetuarLancamento['debitodescricao'],
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' => $dataIso ,
                    'Conferido' => false,
                    'HistoricoID' => null,
                    ]);


                    session(['LancamentoDebito' => "LANÇAMENTO A DÉBITO CRIADO!"]);
                    // return view('lancamentos.lancamentotabelapriceresultado', ['tabelaParcelas' => $tabelaParcelas]);
                }

                ////////// JUROS
                $lancamentoLocalizadoJuros = Lancamento::whereDate('DataContabilidade', '=', $dataIso)
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

                    if ($data_conta_juros_bloqueio->greaterThanOrEqualTo($dataCarbon)) {
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
                    'DataContabilidade' => $dataIso ,
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
        // Guarda empresa original para detectar mudança
        $empresaAntes = (int) $lancamento->EmpresaID;
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

        // Sincroniza sempre a Empresa do Contas a Pagar vinculado ao mesmo LancamentoID
        $empresaDepois = (int) $lancamento->EmpresaID;
        ContasPagar::where('LancamentoID', $lancamento->ID)
            ->update(['EmpresaID' => $empresaDepois]);
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
        // Overrides opcionais vindos do Extrato (empresa e conta crédito global)
        $overrideEmpresaId = $request->input('empresa_id');
        $overrideGlobalCreditId = $request->input('conta_credito_global_id');
        // Modo especial de extrato (ex.: 'santander') que altera regras de sinal/débito/crédito
    $extratoMode = $request->input('extrato_mode');
    $isSantanderMode = ($extratoMode === 'santander');
    $isItauMode = ($extratoMode === 'itau');
        // Aplica filtro de DÉBITO automaticamente quando vier do Extrato (override presente)
        // No modo Santander não aplicar filtro de DÉBITO (iremos lançar entradas e saídas)
    $applyDebitoFilter = !($isSantanderMode || $isItauMode) && (!empty($overrideEmpresaId) || !empty($overrideGlobalCreditId));
        // Função para normalizar string (remover acentos e uppercase)
        $upperNoAccents = function($s){
            $s = (string)$s;
            $t = @iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);
            if($t === false || $t === null) $t = $s;
            return mb_strtoupper($t,'UTF-8');
        };
        // Parser monetário robusto (aceita parênteses, sufixo '-', milhar, vírgula/ponto; retorna float positivo/negativo)
        $parseMoney = function($raw){
            if($raw === null) return null;
            $v = trim((string)$raw);
            if($v==='') return null;
            $negative = false;
            if(preg_match('/^\(.*\)$/',$v)){ $negative = true; $v = preg_replace('/^\(|\)$/','',$v); }
            if(preg_match('/-$/',$v)){ $negative = true; $v = preg_replace('/-$/','',$v); }
            $v = preg_replace('/R\$|BRL|USD|\+/i','',$v);
            $v = preg_replace('/\s+/','',$v);
            $v = preg_replace('/[^0-9\.,-]/','',$v);
            $hasDot = strpos($v,'.')!==false; $hasComma = strpos($v,',')!==false;
            if($hasDot && $hasComma){ $v = str_replace('.','',$v); $v = str_replace(',', '.', $v); }
            elseif($hasComma && !$hasDot){ $v = str_replace(',', '.', $v); }
            $dots = substr_count($v,'.');
            if($dots>1){ $parts = explode('.',$v); $dec = array_pop($parts); $v = implode('',$parts).'.'.$dec; }
            $num = (float)$v; if(!is_finite($num)) return null; return $negative ? -$num : $num;
        };
        // Normaliza data flexível para 'd/m/Y' (retorna null se não conseguir)
        $parseDateToBR = function($raw){
            if($raw === null) return null;
            $v = trim((string)$raw);
            if($v==='') return null;
            // Remove hora caso venha junto
            $v = preg_replace('/[T ]\d{1,2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?$/','',$v);
            // yyyy-mm-dd ou yyyy/mm/dd
            if(preg_match('/^(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})$/',$v,$m)){
                try{ $dt = \Carbon\Carbon::create((int)$m[1],(int)$m[2],(int)$m[3]); return $dt? $dt->format('d/m/Y') : null; }catch(\Throwable $e){ return null; }
            }
            // dd/mm/yyyy
            if(preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2,4})$/',$v,$m)){
                $y = strlen($m[3])===2 ? (int)('20'.$m[3]) : (int)$m[3];
                try{ $dt = \Carbon\Carbon::create($y,(int)$m[2],(int)$m[1]); return $dt? $dt->format('d/m/Y') : null; }catch(\Throwable $e){ return null; }
            }
            // Excel serial
            if(preg_match('/^\d{2,6}$/',$v)){
                $n = (int)$v; if($n>59 && $n<60000){
                    try{ $base = \Carbon\Carbon::create(1899,12,30); $dt = $base->copy()->addDays($n); return $dt->format('d/m/Y'); }catch(\Throwable $e){ return null; }
                }
            }
            // contíguo yyyymmdd
            if(preg_match('/^(\d{4})(\d{2})(\d{2})$/',$v,$m)){
                try{ $dt = \Carbon\Carbon::create((int)$m[1],(int)$m[2],(int)$m[3]); return $dt? $dt->format('d/m/Y') : null; }catch(\Throwable $e){ return null; }
            }
            // contíguo ddmmyyyy
            if(preg_match('/^(\d{2})(\d{2})(\d{4})$/',$v,$m)){
                try{ $dt = \Carbon\Carbon::create((int)$m[3],(int)$m[2],(int)$m[1]); return $dt? $dt->format('d/m/Y') : null; }catch(\Throwable $e){ return null; }
            }
            return null;
        };
        // Marca linhas que já existam no banco a princípio (Empresa + Data + Valor iguais)
        $markExisting = function(array $headers, array $rows, $fallbackEmpresaId = null) use ($parseMoney, $parseDateToBR){
            // Localiza cabeçalhos de DATA e VALOR
            $dataKey = null; $valorKey = null;
            foreach($headers as $h){ $hu = mb_strtoupper($h,'UTF-8'); if($hu==='DATA' || (!$dataKey && strpos($hu,'DATA')!==false)) $dataKey = $h; if($hu==='VALOR' || (!$valorKey && strpos($hu,'VALOR')!==false)) $valorKey = $h; }
            if(!$dataKey || !$valorKey) return $rows; // sem colunas mínimas
            $existsCache = [];
            foreach($rows as &$r){
                $eid = $r['_class_empresa_id'] ?? $fallbackEmpresaId;
                if(!$eid){ $r['_exists'] = false; continue; }
                $rawData = $r[$dataKey] ?? null;
                if($rawData instanceof \DateTimeInterface){ $dataBR = $rawData->format('d/m/Y'); }
                else { $dataBR = $parseDateToBR($rawData); }
                if(!$dataBR){ $r['_exists'] = false; continue; }
                $val = $parseMoney($r[$valorKey] ?? null);
                if($val === null){ $r['_exists'] = false; continue; }
                $val = abs($val); // valores já tratados como absolutos
                $key = $eid.'|'.$dataBR.'|'.number_format($val,2,'.','');
                if(!array_key_exists($key,$existsCache)){
                    try{
                        $dt = \Carbon\Carbon::createFromFormat('d/m/Y',$dataBR);
                        $exists = \App\Models\Lancamento::where('EmpresaID',(int)$eid)
                            ->whereDate('DataContabilidade','=',$dt->format('Y-m-d'))
                            ->where('Valor',$val)
                            ->exists();
                        $existsCache[$key] = $exists;
                    }catch(\Throwable $e){ $existsCache[$key] = false; }
                }
                $r['_exists'] = (bool)$existsCache[$key];
            }
            unset($r);
            return $rows;
        };
        // Força valores absolutos na coluna VALOR (sem sinal negativo ou parênteses)
        $makeValorAbsolute = function(array $headers, array $rows){
            // Detecta a coluna exacta 'VALOR' (case-insensitive via uppercase de comparação)
            $valorKey = null;
            foreach($headers as $h){ if(mb_strtoupper($h,'UTF-8') === 'VALOR'){ $valorKey = $h; break; } }
            if(!$valorKey) return $rows;
            foreach($rows as &$r){
                if(!is_array($r)) continue;
                if(!array_key_exists($valorKey, $r)) continue;
                $v = $r[$valorKey];
                if($v === null || $v === '') continue;
                // Trata como string para preservar formatação local (vírgula/ponto), só removendo sinal
                $s = (string)$v;
                $s = trim($s);
                // Remove parênteses que indicam negativo: (123,45) => 123,45
                if(strlen($s) >= 2 && $s[0] === '(' && substr($s,-1) === ')'){
                    $s = substr($s,1,strlen($s)-2);
                    $s = trim($s);
                }
                // Remove quaisquer sinais de menos na borda
                $s = ltrim($s, "-\xE2\x88\x92"); // inclui sinal unicode de menos
                $s = rtrim($s, "-\xE2\x88\x92");
                // Alguns bancos usam sufixo '-' (ex.: 123,45-) já coberto acima; mantém demais caracteres
                $r[$valorKey] = $s;
            }
            unset($r);
            return $rows;
        };
        $filterDebito = function(array $headers, array $rows) use ($upperNoAccents) {
            // 1) Identifica colunas candidatas: TIPO > DESCRIÇÃO > HISTÓRICO
            $tipoKey = null; $descKeys = []; $histKeys = [];
            foreach($headers as $h){
                $hu = $upperNoAccents($h);
                if($tipoKey === null && strpos($hu,'TIPO') !== false){ $tipoKey = $h; continue; }
                if(strpos($hu,'DESCRICAO') !== false){ $descKeys[] = $h; continue; }
                if(strpos($hu,'HIST') !== false){ $histKeys[] = $h; continue; }
            }
            if($tipoKey === null && !empty($rows) && is_array($rows[0])){
                foreach(array_keys($rows[0]) as $h){
                    $hu = $upperNoAccents($h);
                    if(strpos($hu,'TIPO') !== false){ $tipoKey = $h; break; }
                }
            }
            if(empty($descKeys) && !empty($rows) && is_array($rows[0])){
                foreach(array_keys($rows[0]) as $h){
                    $hu = $upperNoAccents($h);
                    if(strpos($hu,'DESCRICAO') !== false){ $descKeys[] = $h; }
                }
            }
            if(empty($histKeys) && !empty($rows) && is_array($rows[0])){
                foreach(array_keys($rows[0]) as $h){
                    $hu = $upperNoAccents($h);
                    if(strpos($hu,'HIST') !== false){ $histKeys[] = $h; }
                }
            }

            // 2) Classificador por linha usando TIPO quando disponível; senão usa Descrição/Histórico
            $isDebitoRow = function(array $row) use ($tipoKey, $descKeys, $histKeys, $upperNoAccents){
                $hasDeb = false; $hasCred = false;
                if($tipoKey && isset($row[$tipoKey]) && $row[$tipoKey] !== ''){
                    $t = $upperNoAccents((string)$row[$tipoKey]);
                    $t = trim($t);
                    if($t !== ''){
                        // Valores comuns: D, C, DEB, CRED, DEBITO, CREDITO
                        if(strpos($t,'DEB') !== false || preg_match('/^D(\b|[^A-Z]|$)/',$t)){ $hasDeb = true; }
                        if(strpos($t,'CRED') !== false || preg_match('/^C(\b|[^A-Z]|$)/',$t)){ $hasCred = true; }
                    }
                } else {
                    // Usa descrição/histórico para inferir
                    $txt = '';
                    foreach($descKeys as $k){ if(isset($row[$k]) && $row[$k] !== ''){ $txt = (string)$row[$k]; break; } }
                    if($txt === '' && !empty($histKeys)){
                        foreach($histKeys as $k){ if(isset($row[$k]) && $row[$k] !== ''){ $txt = (string)$row[$k]; break; } }
                    }
                    if($txt === '' && isset($row['_hist_ajustado']) && $row['_hist_ajustado'] !== ''){ $txt = (string)$row['_hist_ajustado']; }
                    if($txt === '' && isset($row['_hist_original_col']) && isset($row[$row['_hist_original_col']])){ $txt = (string)$row[$row['_hist_original_col']]; }
                    if($txt !== ''){
                        $vu = $upperNoAccents($txt);
                        // Padrões robustos: DEBITO, DÉB, DEB., DB; CREDITO, CRÉD, CRED., CR
                        if(strpos($vu,'DEBITO') !== false || preg_match('/\bDEB\b|^DEB\b|\bDB\b/i',$vu)){ $hasDeb = true; }
                        if(strpos($vu,'CREDITO') !== false || preg_match('/\bCRED\b|^CR\b/i',$vu)){ $hasCred = true; }
                    } else {
                        // Fallback final: varre todos os campos string buscando tokens
                        foreach($row as $kk=>$vv){
                            if(!is_string($vv) || $vv==='') continue;
                            $vvU = $upperNoAccents($vv);
                            if(!$hasDeb && (strpos($vvU,'DEBITO') !== false || preg_match('/\bDEB\b|^DEB\b|\bDB\b/i',$vvU))){ $hasDeb = true; }
                            if(!$hasCred && (strpos($vvU,'CREDITO') !== false || preg_match('/\bCRED\b|^CR\b/i',$vvU))){ $hasCred = true; }
                            if($hasDeb || $hasCred) break;
                        }
                    }
                }
                return ($hasDeb && !$hasCred);
            };

            // 3) Aplica filtro por linha
            $out = [];
            foreach($rows as $r){ if(!is_array($r)) continue; if($isDebitoRow($r)) $out[] = $r; }
            return array_values($out);
        };
        // Upload direto (opcional)
        if($request->hasFile('arquivo_excel')){
            // Limpa caches antes de iniciar a importação para evitar erro de preg_replace em reimportações
            try {
                Artisan::call('optimize:clear');
            } catch (\Throwable $e) {
                try { Artisan::call('view:clear'); } catch (\Throwable $e2) { /* ignora */ }
            }
            // Aceita CSV com variações de MIME (alguns navegadores/bancos enviam como text/plain ou application/vnd.ms-excel)
            $request->validate([
                'arquivo_excel' => [
                    'required',
                    'file',
                    'max:10240',
                    // Aceita variações comuns que alguns browsers/servidores reportam para XLSX/CSV/TXT
                    'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,application/zip,application/x-zip-compressed'
                ]
            ]);
            $uploaded = $request->file('arquivo_excel');
            $storedName = $uploaded->getClientOriginalName();
            $uploaded->storeAs('imports', $storedName);
            // Após upload, força reconstrução da visualização e destrava seleções iniciais
            return redirect()->route('lancamentos.preview.despesas',[
                'file'=>$storedName,
                'refresh'=>1,
                'unlock'=>1,
                // Propaga overrides de contexto do Extrato, quando presentes
                'empresa_id' => $overrideEmpresaId,
                'conta_credito_global_id' => $overrideGlobalCreditId,
                'extrato_mode' => $extratoMode,
            ]);
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
                // Aplicador seguro de regex para evitar quebra por padrões inválidos
                $safeApplyRegex = function($text) use ($regexSubs){
                    if(!$regexSubs) return $text;
                    $out = $text;
                    foreach($regexSubs as $pattern=>$rep){
                        if(!is_string($pattern) || $pattern==='') continue;
                        // Valida compilação sem disparar warnings
                        if(@preg_match($pattern, '') === false){ continue; }
                        try {
                            $tmp = @preg_replace($pattern, $rep, $out);
                            if($tmp !== null) { $out = $tmp; }
                        } catch(\Throwable $e) {
                            // ignora padrão inválido
                            continue;
                        }
                    }
                    return $out;
                };

        $basePath = storage_path('app/imports');
        $fullPath = $basePath.DIRECTORY_SEPARATOR.$file;
        $exists = file_exists($fullPath);
        $cacheKey = 'preview_despesas:'.auth()->id().':'.md5(json_encode([$file,$limite,$flagUpper,$flagTrimMulti,$subsRaw,$regexRaw]));
    $forceRefresh = (bool)$request->query('refresh');
    $forceUnlock = (bool)$request->query('unlock');
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
            // Resgata modo extrato do cache quando presente
            $cachedExtratoMode = $cached['extrato_mode'] ?? null;
            if(!$extratoMode && $cachedExtratoMode){ $extratoMode = $cachedExtratoMode; $isSantanderMode = ($extratoMode==='santander'); $isItauMode = ($extratoMode==='itau'); }
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
            // Aplica overrides vindos do request (empresa e conta crédito global)
            if($overrideEmpresaId){
                $eid = (int)$overrideEmpresaId;
                $allowed = Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
                    ->where('EmpresasUsuarios.UsuarioID', auth()->id())
                    ->where('Empresas.ID',$eid)
                    ->exists();
                if($allowed){
                    $already = isset($payload['selected_empresa_id']) && (int)$payload['selected_empresa_id'] === $eid;
                    $payload['selected_empresa_id'] = $eid;
                    $payload['empresa_locked'] = false;
                    // Atualiza linhas e reseta contas se mudou
                    if(!empty($payload['rows'])){
                        foreach($payload['rows'] as &$r){
                            $r['_class_empresa_id'] = $eid;
                            if(!$already){ $r['_class_conta_id'] = null; $r['_class_conta_label'] = null; }
                        }
                        unset($r);
                    }
                    $selectedEmpresaId = $eid; $empresaLocked = false; $touched = true;
                }
            }
            if($overrideGlobalCreditId){
                $gcid = (int)$overrideGlobalCreditId;
                // Se houver empresa definida (via cache ou override), valida pertencimento
                $empresaForConta = $payload['selected_empresa_id'] ?? $selectedEmpresaId;
                $contaOk = $empresaForConta ?
                    \App\Models\Conta::where('ID',$gcid)->where('EmpresaID',$empresaForConta)->exists() : false;
                if($contaOk){
                    $label = \App\Models\Conta::where('Contas.ID',$gcid)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->value('PlanoContas.Descricao');
                    $payload['global_credit_conta_id'] = $gcid;
                    $payload['global_credit_conta_label'] = $label;
                    $payload['global_credit_conta_locked'] = false;
                    $globalCreditContaId = $gcid; $globalCreditContaLabel = $label; $globalCreditContaLocked = false; $touched = true;
                }
            }
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
                // Tenta resolver label imediatamente quando possível (evita mostrar apenas o ID no select global)
                $empForConta = $payload['selected_empresa_id'] ?? $selectedEmpresaId ?? $empresaIdFromFile;
                if($empForConta && \App\Models\Conta::where('Contas.ID',$contaCreditoIdFromFile)->where('Contas.EmpresaID',$empForConta)->exists()){
                    $lbl = \App\Models\Conta::where('Contas.ID',$contaCreditoIdFromFile)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->value('PlanoContas.Descricao');
                    $payload['global_credit_conta_label'] = $lbl ?: null;
                    $globalCreditContaLabel = $lbl ?: null;
                } else {
                    $payload['global_credit_conta_label'] = $payload['global_credit_conta_label'] ?? null; // label opcional
                }
                $payload['global_credit_conta_locked'] = false; // destrava para permitir edição
                $globalCreditContaId = $contaCreditoIdFromFile;
                $globalCreditContaLocked = false;
                $touched = true;
            }
            // Se ?unlock=1 foi solicitado, destrava explicitamente ambos os controles
            if($forceUnlock){
                $payload['empresa_locked'] = false;
                $payload['global_credit_conta_locked'] = false;
                $empresaLocked = false;
                $globalCreditContaLocked = false;
                $touched = true;
            }
            // Filtro DÉBITO (somente no contexto do Extrato)
            if($applyDebitoFilter){
                $payload['rows'] = $filterDebito($headers, $payload['rows'] ?? []);
                $rows = $payload['rows'];
                $touched = true;
            }
            // Auto-classificação: no modo Santander
            if($isSantanderMode){
                $empresaForRule = $payload['selected_empresa_id'] ?? $selectedEmpresaId;
                if($empresaForRule){
                    // Recebimentos (PIX RECEBIDO) => RECEBIMENTO DE VENDAS
                    $contaRecebId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','RECEBIMENTO DE VENDAS')
                        ->value('Contas.ID');
                    if($contaRecebId){ $contaRecebLabel = \App\Models\Conta::where('Contas.ID',$contaRecebId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // Recebimentos (plural) => RECEBIMENTOS DE VENDAS (preferir esta conta quando o histórico indicar depósito em dinheiro)
                    $contaRecebPluralId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','RECEBIMENTOS DE VENDAS')
                        ->value('Contas.ID');
                    if($contaRecebPluralId){ $contaRecebPluralLabel = \App\Models\Conta::where('Contas.ID',$contaRecebPluralId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // Recebimentos específicos: ANTECIPAÇÃO GETNET => RECEBIMENTO DE VENDAS - ANTECIPACAO DE CARTAO CREDITO
                    $contaAntGetnetId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','RECEBIMENTO DE VENDAS - ANTECIPACAO DE CARTAO CREDITO')
                        ->value('Contas.ID');
                    if($contaAntGetnetId){ $contaAntGetnetLabel = \App\Models\Conta::where('Contas.ID',$contaAntGetnetId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // TED RECEBIDA => VENDA DE MERCADORIAS (preferir singular), fallback VENDAS DE MERCADORIAS
                    $contaVendasMercId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','VENDA DE MERCADORIAS')
                        ->value('Contas.ID');
                    if(!$contaVendasMercId){
                        $contaVendasMercId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','VENDAS DE MERCADORIAS')
                            ->value('Contas.ID');
                    }
                    if($contaVendasMercId){ $contaVendasMercLabel = \App\Models\Conta::where('Contas.ID',$contaVendasMercId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // Compras
                    // Prioriza "COMPRA DE MERCADORIAS"; se não existir, usa "COMPRAS DE MERCADORIAS"
                    $contaComprasId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','COMPRA DE MERCADORIAS')
                        ->value('Contas.ID');
                    if(!$contaComprasId){
                        $contaComprasId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','COMPRAS DE MERCADORIAS')
                            ->value('Contas.ID');
                    }
                    if($contaComprasId){ $contaComprasLabel = \App\Models\Conta::where('Contas.ID',$contaComprasId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // Tarifas Pix => TARIFAS SOBRE PIX
                    $contaTarifaPixId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','TARIFAS SOBRE PIX')
                        ->value('Contas.ID');
                    if($contaTarifaPixId){ $contaTarifaPixLabel = \App\Models\Conta::where('Contas.ID',$contaTarifaPixId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // Juros de utilização de cheque especial => JUROS UTILIZ.CH.ESPECIAL
                    $contaJurosCheqEspId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','JUROS UTILIZ.CH.ESPECIAL')
                        ->value('Contas.ID');
                    if($contaJurosCheqEspId){ $contaJurosCheqEspLabel = \App\Models\Conta::where('Contas.ID',$contaJurosCheqEspId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
            // CONTRATACAO EMPRESTIMOS FINANCIAMENTO (tenta nova descrição primeiro), fallback antigo e LIKE amplo
                    $contaContrEmpFinId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','CONTRATACAO EMPRESTIMOS FINANCIAMENTO')
                        ->value('Contas.ID');
                    if(!$contaContrEmpFinId){
                        $contaContrEmpFinId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','CONTRATACAO EMPREST/FINANCIAMENTO')
                            ->value('Contas.ID');
                    }
                    if(!$contaContrEmpFinId){
                        // Fallback amplo: variações de nome
                        $contaContrEmpFinId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where(function($q){
                                $q->where('PlanoContas.Descricao','like','%CONTRAT%');
                            })
                            ->where(function($q){
                                $q->where('PlanoContas.Descricao','like','%EMPREST%')
                                  ->orWhere('PlanoContas.Descricao','like','%FINANC%');
                            })
                            ->value('Contas.ID');
                    }
            if($contaContrEmpFinId){ $contaContrEmpFinLabel = \App\Models\Conta::where('Contas.ID',$contaContrEmpFinId)
                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                ->value('PlanoContas.Descricao'); }
                    // TARIFA CONTRATACAO / ADITAMENTO
                    $contaTarifaContrAditId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','TARIFA CONTRATACAO / ADITAMENTO')
                        ->value('Contas.ID');
                    if($contaTarifaContrAditId){ $contaTarifaContrAditLabel = \App\Models\Conta::where('Contas.ID',$contaTarifaContrAditId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // IOF IMPOSTO OPERACOES FINANCEIRAS
                    $contaIofImpOpFinId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','IOF IMPOSTO OPERACOES FINANCEIRAS')
                        ->value('Contas.ID');
                    if($contaIofImpOpFinId){ $contaIofImpOpFinLabel = \App\Models\Conta::where('Contas.ID',$contaIofImpOpFinId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // SANASA - AGUA E ESGOTO MERCADO VILA PROGRESSO
                    $contaSanasaAguaEsgotoId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','SANASA - AGUA E ESGOTO MERCADO VILA PROGRESSO')
                        ->value('Contas.ID');
                    if($contaSanasaAguaEsgotoId){ $contaSanasaAguaEsgotoLabel = \App\Models\Conta::where('Contas.ID',$contaSanasaAguaEsgotoId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // TARIFA MENSALIDADE PACOTE SERVICOS
                    $contaTarifaMensalPacoteId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','TARIFA MENSALIDADE PACOTE SERVICOS')
                        ->value('Contas.ID');
                    if($contaTarifaMensalPacoteId){ $contaTarifaMensalPacoteLabel = \App\Models\Conta::where('Contas.ID',$contaTarifaMensalPacoteId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // Conta de Aplicação Santander (agência/conta específica) para "Aplicacao contamax"
                    $contaAplicSantId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                        ->where('PlanoContas.Descricao','SANTANDER APLICACAO AGENCIA 178 CONTA 130092148 - MERCADO VILA PROGRESSO')
                        ->value('Contas.ID');
                    if(!$contaAplicSantId){
                        // Fallback robusto por partes do nome
                        $contaAplicSantId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where(function($q){
                                $q->where('PlanoContas.Descricao','like','%SANTANDER%')
                                  ->where('PlanoContas.Descricao','like','%APLICA%')
                                  ->where('PlanoContas.Descricao','like','%178%')
                                  ->where('PlanoContas.Descricao','like','%130092148%');
                            })
                            ->value('Contas.ID');
                    }
                    if($contaAplicSantId){ $contaAplicSantLabel = \App\Models\Conta::where('Contas.ID',$contaAplicSantId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }
                    // CONSORCIOS (prioriza ID 10843 se da mesma empresa, fallback por descrição)
                    $contaConsorciosId = null;
                    // Primeiro: ID específico informado (10843), mas só se a conta for da mesma empresa
                    $empCons10843 = \App\Models\Conta::where('Contas.ID',10843)->value('Contas.EmpresaID');
                    if($empCons10843 && (int)$empCons10843 === (int)$empresaForRule){
                        $contaConsorciosId = 10843;
                    }
                    // Fallback por descrição
                    if(!$contaConsorciosId){
                        $contaConsorciosId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','CONSORCIOS')
                            ->value('Contas.ID');
                    }
                    if($contaConsorciosId){ $contaConsorciosLabel = \App\Models\Conta::where('Contas.ID',$contaConsorciosId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao'); }

                    foreach($payload['rows'] as &$rA){
                        if(!empty($rA['_class_conta_id'])) continue; // já classificado
                        $txt = $rA['_hist_ajustado'] ?? (($rA['_hist_original_col'] ?? null) ? ($rA[$rA['_hist_original_col']] ?? null) : null);
                        if(!is_string($txt) || $txt==='') continue;
                        $tu = $upperNoAccents($txt);
                        // Aplicação Contamax => Conta de Aplicação Santander específica
                        if(isset($contaAplicSantId) && $contaAplicSantId && strpos($tu,'APLICACAO') !== false && strpos($tu,'CONTAMAX') !== false){
                            $rA['_class_conta_id'] = (string)$contaAplicSantId;
                            $rA['_class_conta_label'] = $contaAplicSantLabel ?: 'SANTANDER APLICACAO AGENCIA 178 CONTA 130092148 - MERCADO VILA PROGRESSO';
                            continue;
                        }
                        // ANTECIPACAO GETNET => RECEBIMENTO DE VENDAS - ANTECIPACAO DE CARTAO CREDITO
                        if(isset($contaAntGetnetId) && $contaAntGetnetId && (strpos($tu,'ANTECIP') !== false && strpos($tu,'GETNET') !== false)){
                            $rA['_class_conta_id'] = (string)$contaAntGetnetId;
                            $rA['_class_conta_label'] = $contaAntGetnetLabel ?: 'RECEBIMENTO DE VENDAS - ANTECIPACAO DE CARTAO CREDITO';
                            continue;
                        }
                        // TED RECEBIDA => RECEBIMENTO DE VENDAS
                        if(isset($contaRecebId) && $contaRecebId && strpos($tu,'TED') !== false && (strpos($tu,'RECEB') !== false || strpos($tu,'RECEBID') !== false)){
                            $rA['_class_conta_id'] = (string)$contaRecebId;
                            $rA['_class_conta_label'] = $contaRecebLabel ?: 'RECEBIMENTO DE VENDAS';
                            continue;
                        }
                        // DEP DINHEIRO CAIXA AG => RECEBIMENTO DE VENDAS
                        if(isset($contaRecebId) && $contaRecebId){
                            $hasDep = (strpos($tu,'DEP')!==false || strpos($tu,'DEPOSITO')!==false);
                            $hasDinheiro = (strpos($tu,'DINHE')!==false || strpos($tu,'ESPECIE')!==false);
                            $hasCaixa = strpos($tu,'CAIXA')!==false;
                            if($hasDep && $hasDinheiro && $hasCaixa){
                                $rA['_class_conta_id'] = (string)$contaRecebId;
                                $rA['_class_conta_label'] = $contaRecebLabel ?: 'RECEBIMENTO DE VENDAS';
                                continue;
                            }
                        }
                        // PIX RECEBIDO => RECEBIMENTO DE VENDAS
                        if(isset($contaRecebId) && $contaRecebId && strpos($tu,'PIX RECEB') !== false){
                            $rA['_class_conta_id'] = (string)$contaRecebId;
                            $rA['_class_conta_label'] = $contaRecebLabel ?: 'RECEBIMENTO DE VENDAS';
                            continue;
                        }
                        // PIX ENVIADO => COMPR(A|AS) DE MERCADORIAS
                        if(isset($contaComprasId) && $contaComprasId && strpos($tu,'PIX ENVI') !== false){
                            $rA['_class_conta_id'] = (string)$contaComprasId;
                            $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRAS DE MERCADORIAS';
                            continue;
                        }
                        // PAGAMENTO DE BOLETO OUTROS BANCOS => COMPR(A|AS) DE MERCADORIAS
                        if(isset($contaComprasId) && $contaComprasId){
                            $hasPagamento = (strpos($tu,'PAGAMENTO')!==false || strpos($tu,'PAGTO')!==false || strpos($tu,'PGTO')!==false);
                            if($hasPagamento && strpos($tu,'BOLETO')!==false && strpos($tu,'OUTR')!==false && strpos($tu,'BANC')!==false){
                                $rA['_class_conta_id'] = (string)$contaComprasId;
                                $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRAS DE MERCADORIAS';
                                continue;
                            }
                        }
                        // COMPRA CARTAO DEB/CRED => COMPRAS DE MERCADORIAS
                        if(isset($contaComprasId) && $contaComprasId && strpos($tu,'COMPRA') !== false && strpos($tu,'CARTAO') !== false && (strpos($tu,'DEB') !== false || strpos($tu,'DEBIT') !== false || strpos($tu,'CRED') !== false)){
                            $rA['_class_conta_id'] = (string)$contaComprasId;
                            $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRAS DE MERCADORIAS';
                            continue;
                        }
                        // PAGAMENTO CARTAO DE DEBITO/CREDITO => COMPRAS DE MERCADORIAS
                        if(isset($contaComprasId) && $contaComprasId && strpos($tu,'PAGAMENTO') !== false && strpos($tu,'CARTAO') !== false && (strpos($tu,'DEBIT') !== false || strpos($tu,'CRED') !== false)){
                            $rA['_class_conta_id'] = (string)$contaComprasId;
                            $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRAS DE MERCADORIAS';
                            continue;
                        }
                        // PAGAMENTO DE BOLETO (genérico) => COMPRAS DE MERCADORIAS
                        if(isset($contaComprasId) && $contaComprasId){
                            $hasPagamento = (strpos($tu,'PAGAMENTO')!==false || strpos($tu,'PAGTO')!==false || strpos($tu,'PGTO')!==false);
                            if($hasPagamento && strpos($tu,'BOLETO')!==false){
                                $rA['_class_conta_id'] = (string)$contaComprasId;
                                $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRAS DE MERCADORIAS';
                                continue;
                            }
                        }
                        // JUROS saldo utiliz(ação) até limite => JUROS UTILIZ.CH.ESPECIAL
                        if(isset($contaJurosCheqEspId) && $contaJurosCheqEspId){
                            $hasJuros = strpos($tu,'JUROS') !== false;
                            $hasUtiliz = (strpos($tu,'UTILIZ') !== false || strpos($tu,'UTILIZA') !== false);
                            $hasSaldoOuLimite = (strpos($tu,'SALDO') !== false || strpos($tu,'LIMITE') !== false);
                            if($hasJuros && $hasUtiliz && $hasSaldoOuLimite){
                                $rA['_class_conta_id'] = (string)$contaJurosCheqEspId;
                                $rA['_class_conta_label'] = $contaJurosCheqEspLabel ?: 'JUROS UTILIZ.CH.ESPECIAL';
                                continue;
                            }
                        }
                        // TARIFA ... PIX => TARIFAS SOBRE PIX
                        if(isset($contaTarifaPixId) && $contaTarifaPixId && strpos($tu,'TARIFA') !== false && strpos($tu,'PIX') !== false){
                            $rA['_class_conta_id'] = (string)$contaTarifaPixId;
                            $rA['_class_conta_label'] = $contaTarifaPixLabel ?: 'TARIFAS SOBRE PIX';
                            continue;
                        }
                        // CONTRATACAO EMPREST/FINANCIAMENTO
                        if(isset($contaContrEmpFinId) && $contaContrEmpFinId){
                            $hasContrat = (strpos($tu,'CONTRAT') !== false || strpos($tu,'CONTRATACAO') !== false);
                            $hasEmpFin = (strpos($tu,'EMPREST') !== false || strpos($tu,'FINANCI') !== false);
                            if($hasContrat && $hasEmpFin){
                                $rA['_class_conta_id'] = (string)$contaContrEmpFinId;
                                $rA['_class_conta_label'] = $contaContrEmpFinLabel ?: 'CONTRATACAO EMPRESTIMOS FINANCIAMENTO';
                                continue;
                            }
                        }
                        // PRESTACAO DE EMPRESTIMO/FINANCIAMENTO => CONTRATACAO EMPRESTIMOS FINANCIAMENTO
                        if(isset($contaContrEmpFinId) && $contaContrEmpFinId){
                            $hasPrest = (strpos($tu,'PREST') !== false || strpos($tu,'PRESTA') !== false);
                            $hasEmpFin = (strpos($tu,'EMPREST') !== false || strpos($tu,'FINANCI') !== false);
                            if($hasPrest && $hasEmpFin){
                                $rA['_class_conta_id'] = (string)$contaContrEmpFinId;
                                $rA['_class_conta_label'] = $contaContrEmpFinLabel ?: 'CONTRATACAO EMPRESTIMOS FINANCIAMENTO';
                                continue;
                            }
                        }
                        // TARIFA CONTRATACAO / ADITAMENTO
                        if(isset($contaTarifaContrAditId) && $contaTarifaContrAditId){
                            $hasTarifa = strpos($tu,'TARIFA') !== false;
                            $hasContr = strpos($tu,'CONTRAT') !== false;
                            $hasAdit = (strpos($tu,'ADIT') !== false || strpos($tu,'ADITAMENTO') !== false);
                            if($hasTarifa && $hasContr && $hasAdit){
                                $rA['_class_conta_id'] = (string)$contaTarifaContrAditId;
                                $rA['_class_conta_label'] = $contaTarifaContrAditLabel ?: 'TARIFA CONTRATACAO / ADITAMENTO';
                                continue;
                            }
                        }
                        // IOF IMPOSTO OPERACOES FINANCEIRAS (inclui IOF adicional - automatico)
                        if(isset($contaIofImpOpFinId) && $contaIofImpOpFinId){
                            $hasIof = strpos($tu,'IOF') !== false;
                            $hasImpOuOper = (strpos($tu,'IMPOST') !== false || strpos($tu,'OPER') !== false || strpos($tu,'FINANC') !== false);
                            $hasAdicional = (strpos($tu,'ADICION') !== false || strpos($tu,'AUTOMAT') !== false);
                            if($hasIof && ($hasImpOuOper || $hasAdicional)){
                                $rA['_class_conta_id'] = (string)$contaIofImpOpFinId;
                                $rA['_class_conta_label'] = $contaIofImpOpFinLabel ?: 'IOF IMPOSTO OPERACOES FINANCEIRAS';
                                continue;
                            }
                        }
                        // PRESTACAO CONSORCIO => CONSORCIOS
                        if(isset($contaConsorciosId) && $contaConsorciosId){
                            $hasPrest = (strpos($tu,'PREST') !== false || strpos($tu,'PRESTA') !== false || strpos($tu,'PARCEL') !== false);
                            $hasCons = (strpos($tu,'CONSORC') !== false);
                            if($hasPrest && $hasCons){
                                $rA['_class_conta_id'] = (string)$contaConsorciosId;
                                $rA['_class_conta_label'] = $contaConsorciosLabel ?: 'CONSORCIOS';
                                continue;
                            }
                        }
                        // PAGAMENTO A FORNECEDORES => COMPRAS DE MERCADORIAS
                        if(isset($contaComprasId) && $contaComprasId){
                            $hasPag = (strpos($tu,'PAGAMENTO')!==false || strpos($tu,'PAGTO')!==false || strpos($tu,'PGTO')!==false);
                            $hasForn = (strpos($tu,'FORNEC')!==false || strpos($tu,'FORNECEDOR')!==false || strpos($tu,'FORNECEDORES')!==false || strpos($tu,'FORN')!==false);
                            if($hasPag && $hasForn){
                                $rA['_class_conta_id'] = (string)$contaComprasId;
                                $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRA DE MERCADORIAS';
                                continue;
                            }
                        }
                        // CONTA DE AGUA/ESGOTO (SANASA) => SANASA - AGUA E ESGOTO MERCADO VILA PROGRESSO
                        if(isset($contaSanasaAguaEsgotoId) && $contaSanasaAguaEsgotoId){
                            $hasAgua = strpos($tu,'AGUA') !== false;
                            $hasEsgoto = strpos($tu,'ESGOTO') !== false;
                            $hasConta = strpos($tu,'CONTA') !== false;
                            $hasCanais = strpos($tu,'CANAIS') !== false;
                            if($hasAgua && $hasEsgoto && ($hasConta || $hasCanais)){
                                $rA['_class_conta_id'] = (string)$contaSanasaAguaEsgotoId;
                                $rA['_class_conta_label'] = $contaSanasaAguaEsgotoLabel ?: 'SANASA - AGUA E ESGOTO MERCADO VILA PROGRESSO';
                                continue;
                            }
                        }
                        // TARIFA MENSALIDADE PACOTE SERVICOS
                        if(isset($contaTarifaMensalPacoteId) && $contaTarifaMensalPacoteId){
                            $hasTarifa = strpos($tu,'TARIFA') !== false;
                            $hasMensal = (strpos($tu,'MENSAL') !== false || strpos($tu,'MENSALIDADE') !== false);
                            $hasPacote = strpos($tu,'PACOTE') !== false;
                            $hasServ = strpos($tu,'SERV') !== false; // cobre SERVICO/SERVICOS
                            if($hasTarifa && $hasMensal && $hasPacote && $hasServ){
                                $rA['_class_conta_id'] = (string)$contaTarifaMensalPacoteId;
                                $rA['_class_conta_label'] = $contaTarifaMensalPacoteLabel ?: 'TARIFA MENSALIDADE PACOTE SERVICOS';
                                continue;
                            }
                        }
                    }
                    unset($rA);
                    $touched = true;
                }
            }
            // Garante valores absolutos na coluna VALOR (sempre na prévia)
            if(!$isSantanderMode && !$isItauMode){
                $payload['rows'] = $makeValorAbsolute($headers, $payload['rows'] ?? []);
                $rows = $payload['rows'];
            }
            // Marca duplicidades a princípio (Empresa+Data+Valor)
            $payload['rows'] = $markExisting($headers, $payload['rows'] ?? [], $selectedEmpresaId);
            // Preserva marcações manuais de "Já existe" feitas antes do refresh
            if(isset($oldRows) && is_array($oldRows) && !empty($oldRows)){
                foreach($payload['rows'] as $idx=>&$rNew){
                    $manual = $rNew['_exists_manual'] ?? ($oldRows[$idx]['_exists_manual'] ?? null);
                    if($manual !== null){
                        $rNew['_exists_manual'] = (bool)$manual;
                        // Consolida _exists para refletir na UI (auto OR manual)
                        $auto = !empty($rNew['_exists']);
                        $rNew['_exists'] = ($auto || $rNew['_exists_manual']) ? true : false;
                    }
                }
                unset($rNew);
            }
            $rows = $payload['rows'];
            $touched = true;
            if($touched){
                // Persiste também o modo de extrato
                $payload['extrato_mode'] = $extratoMode;
                $this->previewCachePut($cacheKey, $payload);
            }
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
                    // Em refresh após upload com ?unlock=1, destrava explicitamente
                    if($forceUnlock){ $empresaLocked=false; $globalCreditContaLocked=false; }
                }
                // Leitura robusta: se for CSV, usa leitor CSV com delimitador ';' e encoding UTF-8
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                if($ext === 'csv'){
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                    // Detecta delimitador por amostra simples
                    $sample = @file_get_contents($fullPath, false, null, 0, 2048);
                    $hasSemicolon = is_string($sample) && substr_count($sample, ';') > substr_count($sample, ',');
                    $reader->setDelimiter($hasSemicolon ? ';' : ',');
                    $reader->setEnclosure('"');
                    // Heurística de encoding: se aparecer caractere de substituição (�), tenta ISO-8859-1
                    $encoding = 'UTF-8';
                    if(is_string($sample)){
                        if(strpos($sample, "\xEF\xBB\xBF") === 0){ $encoding = 'UTF-8'; }
                        elseif(strpos($sample, "�") !== false){ $encoding = 'ISO-8859-1'; }
                    }
                    if(method_exists($reader,'setInputEncoding')){ $reader->setInputEncoding($encoding); }
                    $spreadsheet = $reader->load($fullPath);
                } else {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
                }
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestDataRow();
                $highestCol = $sheet->getHighestDataColumn();
                $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
                // Detecta linha de cabeçalho: varre até 10 linhas procurando por tokens DATA/HIST/VALOR
                $headerRowIndex = 1; $bestScore = -1;
                $maxScan = min($highestRow, 10);
                for($r=1; $r <= $maxScan; $r++){
                    $tokens = 0; $nonEmpty = 0; $metaScore = 0;
                    for($c=1; $c <= $highestColIndex; $c++){
                        $cell = trim((string)$sheet->getCellByColumnAndRow($c,$r)->getValue());
                        if($cell !== '') $nonEmpty++;
                        $cellU = mb_strtoupper($cell,'UTF-8');
                        if(strpos($cellU,'DATA') !== false) $tokens++;
                        if(strpos($cellU,'HIST') !== false) $tokens++;
                        if(strpos($cellU,'VALOR') !== false) $tokens++;
                        if(strpos($cellU,'SALDO') !== false) $tokens++;
                        if(strpos($cellU,'AGENCIA') !== false || strpos($cellU,'CONTA') !== false) $metaScore++;
                    }
                    // ignora linhas vazias
                    if($nonEmpty === 0) continue;
                    // Evita pegar linha metadado AGENCIA/CONTA como cabeçalho
                    if($metaScore > 0 && $tokens === 0) continue;
                    // Preferir linhas com mais tokens reconhecidos
                    if($tokens > $bestScore){ $bestScore = $tokens; $headerRowIndex = $r; }
                }
                $removeCols = ['COL_4','COL_5','COL_6','COL_7'];
                $headerMap = [];
                for($col=1; $col <= $highestColIndex; $col++){
                    $val = trim((string)$sheet->getCellByColumnAndRow($col,$headerRowIndex)->getValue());
                    if($val === '') $val = 'COL_'.$col; // fallback genérico
                    // Renomeações solicitadas: COL_2 -> HISTORICO, COL_3 -> VALOR, DESPESAS -> DATA
                    if($val === 'COL_2') { $val = 'HISTORICO'; }
                    if($val === 'COL_3') { $val = 'VALOR'; }
                    if($val === 'DESPESAS') { $val = 'DATA'; }
                    // Alias para CONTA_DEBITO_ID: detecta colunas como "Conta Débito ID", "Conta Debito Cod", etc.
                    $valNorm = mb_strtoupper($val, 'UTF-8');
                    $isConta = strpos($valNorm, 'CONTA') !== false;
                    $hasDeb = (strpos($valNorm, 'DEBIT') !== false) || (strpos($valNorm, 'DÉBITO') !== false) || (strpos($valNorm, 'DEBITO') !== false);
                    $hasId = (strpos($valNorm, 'ID') !== false) || (strpos($valNorm, 'COD') !== false) || (strpos($valNorm, 'CÓD') !== false);
                    if($isConta && $hasDeb && $hasId){ $val = 'CONTA_DEBITO_ID'; }
                    if(in_array($val,$removeCols,true)) continue;
                    $headers[] = $val;
                    $headerMap[$col] = $val;
                }
                // Localiza coluna de histórico procurando por 'HIST' (case-insensitive)
                $histKey = null;
                foreach($headers as $h){ if(stripos($h,'HIST') !== false){ $histKey = $h; break; } }
                for($row=$headerRowIndex+1; $row <= $highestRow && count($rows) < $limite; $row++){
                    $linha = [];
                    $linhaVazia = true;
                    foreach($headerMap as $colIndex=>$hName){
                        $val = $sheet->getCellByColumnAndRow($colIndex,$row)->getFormattedValue();
                        if($val !== null && $val !== '') $linhaVazia = false;
                        if(is_string($val)){
                            if($flagTrimMulti){ $val = preg_replace('/\s+/u',' ',trim($val)); }
                            if($flagUpper){ $val = mb_strtoupper($val,'UTF-8'); }
                            if($subs){ foreach($subs as $find=>$rep){ if($find!=='') $val = str_replace($find,$rep,$val); } }
                            // Aplica regex do usuário com proteção
                            $val = $safeApplyRegex($val);
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
                    // Prefill da conta débito quando a planilha já traz CONTA_DEBITO_ID na linha
                    if(isset($linha['CONTA_DEBITO_ID']) && $linha['CONTA_DEBITO_ID'] !== '' && $linha['CONTA_DEBITO_ID'] !== null){
                        $linha['_class_conta_id'] = (string)$linha['CONTA_DEBITO_ID'];
                        $linha['_class_conta_from_file'] = true;
                    } else {
                        // Não inicializamos _class_conta_id aqui para permitir restauração posterior; manter null se novo
                        $linha['_class_conta_id'] = $linha['_class_conta_id'] ?? null;
                    }
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
                // Se foi solicitado unlock e conseguimos inferir a empresa, já define como selecionada para habilitar Conta Crédito
                if($forceUnlock && $empresaIdFromFile && !$selectedEmpresaId){
                    $selectedEmpresaId = $empresaIdFromFile;
                    $empresaLocked = false;
                }
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
                // Preenche labels das contas quando houver CONTA_DEBITO_ID no arquivo original
                if(in_array('CONTA_DEBITO_ID',$headers,true)){
                    $idsMap = [];
                    foreach($rows as $r){
                        $cid = null;
                        if(!empty($r['_class_conta_id'])){ $cid = (string)$r['_class_conta_id']; }
                        elseif(!empty($r['CONTA_DEBITO_ID'])){ $cid = (string)$r['CONTA_DEBITO_ID']; }
                        if($cid !== null && $cid !== ''){ $idsMap[$cid] = true; }
                    }
                    if(!empty($idsMap)){
                        $labels = Conta::whereIn('Contas.ID', array_keys($idsMap))
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->pluck('PlanoContas.Descricao','Contas.ID');
                        foreach($rows as &$rL){
                            if(isset($rL['CONTA_DEBITO_ID']) && $rL['CONTA_DEBITO_ID'] !== '' && empty($rL['_class_conta_id'])){
                                $rL['_class_conta_id'] = (string)$rL['CONTA_DEBITO_ID'];
                                $rL['_class_conta_from_file'] = true;
                            }
                            if(!empty($rL['_class_conta_id']) && empty($rL['_class_conta_label'])){
                                $cid = (string)$rL['_class_conta_id'];
                                if(isset($labels[$cid])){ $rL['_class_conta_label'] = $labels[$cid]; }
                            }
                        }
                        unset($rL);
                    }
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
                        $norm = preg_replace('/[\\.,;:!\-\(\)\[\]\/]+/u',' ', $norm); // limpeza básica de pontuação comum
                        $norm = preg_replace('/\\\\+/u',' ', $norm); // remove barras invertidas restantes
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
                // Aplica overrides vindos do request (empresa e conta crédito global) antes de salvar o payload
                if($overrideEmpresaId){
                    $eid = (int)$overrideEmpresaId;
                    $allowed = Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
                        ->where('EmpresasUsuarios.UsuarioID', auth()->id())
                        ->where('Empresas.ID',$eid)
                        ->exists();
                    if($allowed){
                        $already = isset($selectedEmpresaId) && $selectedEmpresaId && (int)$selectedEmpresaId === $eid;
                        $selectedEmpresaId = $eid; $empresaLocked = false;
                        foreach($rows as &$r){ $r['_class_empresa_id'] = $eid; if(!$already){ $r['_class_conta_id']=null; $r['_class_conta_label']=null; } }
                        unset($r);
                    }
                }
                // Se inferimos conta crédito global do arquivo e ainda não há no contexto, define e resolve label
                if($contaCreditoIdFromFile && !$globalCreditContaId){
                    $globalCreditContaId = $contaCreditoIdFromFile;
                    $empForConta = $selectedEmpresaId ?? $empresaIdFromFile;
                    if($empForConta && \App\Models\Conta::where('Contas.ID',$globalCreditContaId)->where('Contas.EmpresaID',$empForConta)->exists()){
                        $globalCreditContaLabel = \App\Models\Conta::where('Contas.ID',$globalCreditContaId)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao');
                    }
                    $globalCreditContaLocked = false;
                }
                if($overrideGlobalCreditId && $selectedEmpresaId){
                    $gcid = (int)$overrideGlobalCreditId;
                    $contaOk = \App\Models\Conta::where('ID',$gcid)->where('EmpresaID',(int)$selectedEmpresaId)->exists();
                    if($contaOk){
                        $globalCreditContaId = $gcid;
                        $globalCreditContaLabel = \App\Models\Conta::where('Contas.ID',$gcid)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->value('PlanoContas.Descricao');
                        $globalCreditContaLocked = false;
                    }
                }
                // Aplica filtro DÉBITO antes de salvar (somente no contexto do Extrato)
                if($applyDebitoFilter){
                    $rows = $filterDebito($headers, $rows);
                }
                // Auto-classificação: no modo Santander (exige empresa selecionada)
                if($isSantanderMode){
                    $empresaForRule = $selectedEmpresaId ?? $empresaIdFromFile;
                    if($empresaForRule){
                        // Recebimentos (PIX RECEBIDO) => RECEBIMENTO DE VENDAS
                        $contaRecebId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','RECEBIMENTO DE VENDAS')
                            ->value('Contas.ID');
                        if($contaRecebId){ $contaRecebLabel = \App\Models\Conta::where('Contas.ID',$contaRecebId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // Recebimentos (plural) => RECEBIMENTOS DE VENDAS
                        $contaRecebPluralId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','RECEBIMENTOS DE VENDAS')
                            ->value('Contas.ID');
                        if($contaRecebPluralId){ $contaRecebPluralLabel = \App\Models\Conta::where('Contas.ID',$contaRecebPluralId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // Recebimentos específicos: ANTECIPACAO GETNET => RECEBIMENTO DE VENDAS - ANTECIPACAO DE CARTAO CREDITO
                        $contaAntGetnetId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','RECEBIMENTO DE VENDAS - ANTECIPACAO DE CARTAO CREDITO')
                            ->value('Contas.ID');
                        if($contaAntGetnetId){ $contaAntGetnetLabel = \App\Models\Conta::where('Contas.ID',$contaAntGetnetId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // TED RECEBIDA => VENDA DE MERCADORIAS (preferir singular), fallback VENDAS DE MERCADORIAS
                        $contaVendasMercId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','VENDA DE MERCADORIAS')
                            ->value('Contas.ID');
                        if(!$contaVendasMercId){
                            $contaVendasMercId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->where('PlanoContas.Descricao','VENDAS DE MERCADORIAS')
                                ->value('Contas.ID');
                        }
                        if($contaVendasMercId){ $contaVendasMercLabel = \App\Models\Conta::where('Contas.ID',$contaVendasMercId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // Compras: prioriza "COMPRA DE MERCADORIAS", fallback "COMPRAS DE MERCADORIAS"
                        $contaComprasId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','COMPRA DE MERCADORIAS')
                            ->value('Contas.ID');
                        if(!$contaComprasId){
                            $contaComprasId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->where('PlanoContas.Descricao','COMPRAS DE MERCADORIAS')
                                ->value('Contas.ID');
                        }
                        if($contaComprasId){ $contaComprasLabel = \App\Models\Conta::where('Contas.ID',$contaComprasId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // Tarifas Pix => TARIFAS SOBRE PIX
                        $contaTarifaPixId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','TARIFAS SOBRE PIX')
                            ->value('Contas.ID');
                        if($contaTarifaPixId){ $contaTarifaPixLabel = \App\Models\Conta::where('Contas.ID',$contaTarifaPixId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // Juros de utilização de cheque especial => JUROS UTILIZ.CH.ESPECIAL
                        $contaJurosCheqEspId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','JUROS UTILIZ.CH.ESPECIAL')
                            ->value('Contas.ID');
                        if($contaJurosCheqEspId){ $contaJurosCheqEspLabel = \App\Models\Conta::where('Contas.ID',$contaJurosCheqEspId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // CONTRATACAO EMPRESTIMOS FINANCIAMENTO (tenta nova descrição primeiro), fallback antigo e LIKE amplo
                        $contaContrEmpFinId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','CONTRATACAO EMPRESTIMOS FINANCIAMENTO')
                            ->value('Contas.ID');
                        if(!$contaContrEmpFinId){
                            $contaContrEmpFinId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->where('PlanoContas.Descricao','CONTRATACAO EMPREST/FINANCIAMENTO')
                                ->value('Contas.ID');
                        }
                        if(!$contaContrEmpFinId){
                            // Fallback amplo: variações de nome
                            $contaContrEmpFinId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->where(function($q){
                                    $q->where('PlanoContas.Descricao','like','%CONTRAT%');
                                })
                                ->where(function($q){
                                    $q->where('PlanoContas.Descricao','like','%EMPREST%')
                                      ->orWhere('PlanoContas.Descricao','like','%FINANC%');
                                })
                                ->value('Contas.ID');
                        }
                        if($contaContrEmpFinId){ $contaContrEmpFinLabel = \App\Models\Conta::where('Contas.ID',$contaContrEmpFinId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // TARIFA CONTRATACAO / ADITAMENTO
                        $contaTarifaContrAditId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','TARIFA CONTRATACAO / ADITAMENTO')
                            ->value('Contas.ID');
                        if($contaTarifaContrAditId){ $contaTarifaContrAditLabel = \App\Models\Conta::where('Contas.ID',$contaTarifaContrAditId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // IOF IMPOSTO OPERACOES FINANCEIRAS
                        $contaIofImpOpFinId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','IOF IMPOSTO OPERACOES FINANCEIRAS')
                            ->value('Contas.ID');
                        if($contaIofImpOpFinId){ $contaIofImpOpFinLabel = \App\Models\Conta::where('Contas.ID',$contaIofImpOpFinId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // SANASA - AGUA E ESGOTO MERCADO VILA PROGRESSO
                        $contaSanasaAguaEsgotoId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','SANASA - AGUA E ESGOTO MERCADO VILA PROGRESSO')
                            ->value('Contas.ID');
                        if($contaSanasaAguaEsgotoId){ $contaSanasaAguaEsgotoLabel = \App\Models\Conta::where('Contas.ID',$contaSanasaAguaEsgotoId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }
                        // TARIFA MENSALIDADE PACOTE SERVICOS
                        $contaTarifaMensalPacoteId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','TARIFA MENSALIDADE PACOTE SERVICOS')
                            ->value('Contas.ID');
                        if($contaTarifaMensalPacoteId){ $contaTarifaMensalPacoteLabel = \App\Models\Conta::where('Contas.ID',$contaTarifaMensalPacoteId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }

                        // Conta de Aplicação Santander (agência/conta específica) para "Aplicacao contamax"
                        $contaAplicSantId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                            ->where('PlanoContas.Descricao','SANTANDER APLICACAO AGENCIA 178 CONTA 130092148 - MERCADO VILA PROGRESSO')
                            ->value('Contas.ID');
                        if(!$contaAplicSantId){
                            // Fallback robusto por partes do nome
                            $contaAplicSantId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->where(function($q){
                                    $q->where('PlanoContas.Descricao','like','%SANTANDER%')
                                      ->where('PlanoContas.Descricao','like','%APLICA%')
                                      ->where('PlanoContas.Descricao','like','%178%')
                                      ->where('PlanoContas.Descricao','like','%130092148%');
                                })
                                ->value('Contas.ID');
                        }
                        if($contaAplicSantId){ $contaAplicSantLabel = \App\Models\Conta::where('Contas.ID',$contaAplicSantId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }

                        // CONSORCIOS (prioriza ID 10843 se da mesma empresa, fallback por descrição)
                        $contaConsorciosId = null;
                        $empCons10843 = \App\Models\Conta::where('Contas.ID',10843)->value('Contas.EmpresaID');
                        if($empCons10843 && (int)$empCons10843 === (int)$empresaForRule){
                            $contaConsorciosId = 10843;
                        }
                        if(!$contaConsorciosId){
                            $contaConsorciosId = \App\Models\Conta::where('Contas.EmpresaID', (int)$empresaForRule)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->where('PlanoContas.Descricao','CONSORCIOS')
                                ->value('Contas.ID');
                        }
                        if($contaConsorciosId){ $contaConsorciosLabel = \App\Models\Conta::where('Contas.ID',$contaConsorciosId)
                                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                                ->value('PlanoContas.Descricao'); }

                        foreach($rows as &$rA){
                            if(!empty($rA['_class_conta_id'])) continue;
                            $txt = $rA['_hist_ajustado'] ?? (($rA['_hist_original_col'] ?? null) ? ($rA[$rA['_hist_original_col']] ?? null) : null);
                            if(!is_string($txt) || $txt==='') continue;
                            $tu = $upperNoAccents($txt);
                            // Aplicação Contamax => Conta de Aplicação Santander específica
                            if(isset($contaAplicSantId) && $contaAplicSantId && strpos($tu,'APLICACAO') !== false && strpos($tu,'CONTAMAX') !== false){
                                $rA['_class_conta_id'] = (string)$contaAplicSantId;
                                $rA['_class_conta_label'] = $contaAplicSantLabel ?: 'SANTANDER APLICACAO AGENCIA 178 CONTA 130092148 - MERCADO VILA PROGRESSO';
                                continue;
                            }
                            // DEP DINHEIRO CAIXA AG => RECEBIMENTO DE VENDAS
                            if(isset($contaRecebId) && $contaRecebId){
                                $hasDep = (strpos($tu,'DEP')!==false || strpos($tu,'DEPOSITO')!==false);
                                $hasDinheiro = (strpos($tu,'DINHE')!==false || strpos($tu,'ESPECIE')!==false);
                                $hasCaixa = strpos($tu,'CAIXA')!==false;
                                if($hasDep && $hasDinheiro && $hasCaixa){
                                    $rA['_class_conta_id'] = (string)$contaRecebId;
                                    $rA['_class_conta_label'] = $contaRecebLabel ?: 'RECEBIMENTO DE VENDAS';
                                    continue;
                                }
                            }
                            // ANTECIPACAO GETNET => RECEBIMENTO DE VENDAS - ANTECIPACAO DE CARTAO CREDITO
                            if(isset($contaAntGetnetId) && $contaAntGetnetId && (strpos($tu,'ANTECIP') !== false && strpos($tu,'GETNET') !== false)){
                                $rA['_class_conta_id'] = (string)$contaAntGetnetId;
                                $rA['_class_conta_label'] = $contaAntGetnetLabel ?: 'RECEBIMENTO DE VENDAS - ANTECIPACAO DE CARTAO CREDITO';
                                continue;
                            }
                            // TED RECEBIDA => RECEBIMENTO DE VENDAS
                            if(isset($contaRecebId) && $contaRecebId && strpos($tu,'TED') !== false && (strpos($tu,'RECEB') !== false || strpos($tu,'RECEBID') !== false)){
                                $rA['_class_conta_id'] = (string)$contaRecebId;
                                $rA['_class_conta_label'] = $contaRecebLabel ?: 'RECEBIMENTO DE VENDAS';
                                continue;
                            }
                            if(isset($contaRecebId) && $contaRecebId && strpos($tu,'PIX RECEB') !== false){
                                $rA['_class_conta_id'] = (string)$contaRecebId;
                                $rA['_class_conta_label'] = $contaRecebLabel ?: 'RECEBIMENTO DE VENDAS';
                                continue;
                            }
                            // COMPRA CARTAO DEB/CRED => COMPRAS DE MERCADORIAS
                            if(isset($contaComprasId) && $contaComprasId && strpos($tu,'COMPRA') !== false && strpos($tu,'CARTAO') !== false && (strpos($tu,'DEB') !== false || strpos($tu,'DEBIT') !== false || strpos($tu,'CRED') !== false)){
                                $rA['_class_conta_id'] = (string)$contaComprasId;
                                $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRAS DE MERCADORIAS';
                                continue;
                            }
                            if(isset($contaComprasId) && $contaComprasId && (strpos($tu,'PIX ENVI') !== false || (strpos($tu,'PAGAMENTO') !== false && strpos($tu,'CARTAO') !== false && (strpos($tu,'DEBIT') !== false || strpos($tu,'CRED') !== false)))){
                                $rA['_class_conta_id'] = (string)$contaComprasId;
                                $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRAS DE MERCADORIAS';
                                continue;
                            }
                            // PAGAMENTO DE BOLETO (genérico) => COMPRAS DE MERCADORIAS
                            if(isset($contaComprasId) && $contaComprasId){
                                $hasPagamento = (strpos($tu,'PAGAMENTO')!==false || strpos($tu,'PAGTO')!==false || strpos($tu,'PGTO')!==false);
                                if($hasPagamento && strpos($tu,'BOLETO')!==false){
                                    $rA['_class_conta_id'] = (string)$contaComprasId;
                                    $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRAS DE MERCADORIAS';
                                    continue;
                                }
                            }
                            // PAGAMENTO DE BOLETO OUTROS BANCOS => COMPR(A|AS) DE MERCADORIAS
                            if(isset($contaComprasId) && $contaComprasId){
                                $hasPagamento = (strpos($tu,'PAGAMENTO')!==false || strpos($tu,'PAGTO')!==false || strpos($tu,'PGTO')!==false);
                                if($hasPagamento && strpos($tu,'BOLETO')!==false && strpos($tu,'OUTR')!==false && strpos($tu,'BANC')!==false){
                                    $rA['_class_conta_id'] = (string)$contaComprasId;
                                    $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRAS DE MERCADORIAS';
                                    continue;
                                }
                            }
                            // JUROS saldo utiliz(ação) até limite => JUROS UTILIZ.CH.ESPECIAL
                            if(isset($contaJurosCheqEspId) && $contaJurosCheqEspId){
                                $hasJuros = strpos($tu,'JUROS') !== false;
                                $hasUtiliz = (strpos($tu,'UTILIZ') !== false || strpos($tu,'UTILIZA') !== false);
                                $hasSaldoOuLimite = (strpos($tu,'SALDO') !== false || strpos($tu,'LIMITE') !== false);
                                if($hasJuros && $hasUtiliz && $hasSaldoOuLimite){
                                    $rA['_class_conta_id'] = (string)$contaJurosCheqEspId;
                                    $rA['_class_conta_label'] = $contaJurosCheqEspLabel ?: 'JUROS UTILIZ.CH.ESPECIAL';
                                    continue;
                                }
                            }
                            // CONTA DE AGUA/ESGOTO (SANASA) => SANASA - AGUA E ESGOTO MERCADO VILA PROGRESSO
                            if(isset($contaSanasaAguaEsgotoId) && $contaSanasaAguaEsgotoId){
                                $hasAgua = strpos($tu,'AGUA') !== false;
                                $hasEsgoto = strpos($tu,'ESGOTO') !== false;
                                $hasConta = strpos($tu,'CONTA') !== false;
                                $hasCanais = strpos($tu,'CANAIS') !== false;
                                if($hasAgua && $hasEsgoto && ($hasConta || $hasCanais)){
                                    $rA['_class_conta_id'] = (string)$contaSanasaAguaEsgotoId;
                                    $rA['_class_conta_label'] = $contaSanasaAguaEsgotoLabel ?: 'SANASA - AGUA E ESGOTO MERCADO VILA PROGRESSO';
                                    continue;
                                }
                            }
                            // TARIFA MENSALIDADE PACOTE SERVICOS
                            if(isset($contaTarifaMensalPacoteId) && $contaTarifaMensalPacoteId){
                                $hasTarifa = strpos($tu,'TARIFA') !== false;
                                $hasMensal = (strpos($tu,'MENSAL') !== false || strpos($tu,'MENSALIDADE') !== false);
                                $hasPacote = strpos($tu,'PACOTE') !== false;
                                $hasServ = strpos($tu,'SERV') !== false; // cobre SERVICO/SERVICOS
                                if($hasTarifa && $hasMensal && $hasPacote && $hasServ){
                                    $rA['_class_conta_id'] = (string)$contaTarifaMensalPacoteId;
                                    $rA['_class_conta_label'] = $contaTarifaMensalPacoteLabel ?: 'TARIFA MENSALIDADE PACOTE SERVICOS';
                                    continue;
                                }
                            }
                            // CONTRATACAO EMPRESTIMOS FINANCIAMENTO
                            if(isset($contaContrEmpFinId) && $contaContrEmpFinId){
                                $hasContrat = (strpos($tu,'CONTRAT') !== false || strpos($tu,'CONTRATACAO') !== false);
                                $hasEmpFin = (strpos($tu,'EMPREST') !== false || strpos($tu,'FINANCI') !== false);
                                if($hasContrat && $hasEmpFin){
                                    $rA['_class_conta_id'] = (string)$contaContrEmpFinId;
                                    $rA['_class_conta_label'] = $contaContrEmpFinLabel ?: 'CONTRATACAO EMPRESTIMOS FINANCIAMENTO';
                                    continue;
                                }
                            }
                            // PRESTACAO DE EMPRESTIMO/FINANCIAMENTO => CONTRATACAO EMPRESTIMOS FINANCIAMENTO
                            if(isset($contaContrEmpFinId) && $contaContrEmpFinId){
                                $hasPrest = (strpos($tu,'PREST') !== false || strpos($tu,'PRESTA') !== false);
                                $hasEmpFin = (strpos($tu,'EMPREST') !== false || strpos($tu,'FINANCI') !== false);
                                if($hasPrest && $hasEmpFin){
                                    $rA['_class_conta_id'] = (string)$contaContrEmpFinId;
                                    $rA['_class_conta_label'] = $contaContrEmpFinLabel ?: 'CONTRATACAO EMPRESTIMOS FINANCIAMENTO';
                                    continue;
                                }
                            }
                            // TARIFA CONTRATACAO / ADITAMENTO
                            if(isset($contaTarifaContrAditId) && $contaTarifaContrAditId){
                                $hasTarifa = strpos($tu,'TARIFA') !== false;
                                $hasContr = strpos($tu,'CONTRAT') !== false;
                                $hasAdit = (strpos($tu,'ADIT') !== false || strpos($tu,'ADITAMENTO') !== false);
                                if($hasTarifa && $hasContr && $hasAdit){
                                    $rA['_class_conta_id'] = (string)$contaTarifaContrAditId;
                                    $rA['_class_conta_label'] = $contaTarifaContrAditLabel ?: 'TARIFA CONTRATACAO / ADITAMENTO';
                                    continue;
                                }
                            }
                            // IOF IMPOSTO OPERACOES FINANCEIRAS (inclui IOF adicional - automatico)
                            if(isset($contaIofImpOpFinId) && $contaIofImpOpFinId){
                                $hasIof = strpos($tu,'IOF') !== false;
                                $hasImpOuOper = (strpos($tu,'IMPOST') !== false || strpos($tu,'OPER') !== false || strpos($tu,'FINANC') !== false);
                                $hasAdicional = (strpos($tu,'ADICION') !== false || strpos($tu,'AUTOMAT') !== false);
                                if($hasIof && ($hasImpOuOper || $hasAdicional)){
                                    $rA['_class_conta_id'] = (string)$contaIofImpOpFinId;
                                    $rA['_class_conta_label'] = $contaIofImpOpFinLabel ?: 'IOF IMPOSTO OPERACOES FINANCEIRAS';
                                    continue;
                                }
                            }
                            // PRESTACAO CONSORCIO => CONSORCIOS
                            if(isset($contaConsorciosId) && $contaConsorciosId){
                                $hasPrest = (strpos($tu,'PREST') !== false || strpos($tu,'PRESTA') !== false || strpos($tu,'PARCEL') !== false);
                                $hasCons = (strpos($tu,'CONSORC') !== false);
                                if($hasPrest && $hasCons){
                                    $rA['_class_conta_id'] = (string)$contaConsorciosId;
                                    $rA['_class_conta_label'] = $contaConsorciosLabel ?: 'CONSORCIOS';
                                    continue;
                                }
                            }
                            // PAGAMENTO A FORNECEDORES => COMPRAS DE MERCADORIAS
                            if(isset($contaComprasId) && $contaComprasId){
                                $hasPag = (strpos($tu,'PAGAMENTO')!==false || strpos($tu,'PAGTO')!==false || strpos($tu,'PGTO')!==false);
                                $hasForn = (strpos($tu,'FORNEC')!==false || strpos($tu,'FORNECEDOR')!==false || strpos($tu,'FORNECEDORES')!==false || strpos($tu,'FORN')!==false);
                                if($hasPag && $hasForn){
                                    $rA['_class_conta_id'] = (string)$contaComprasId;
                                    $rA['_class_conta_label'] = $contaComprasLabel ?: 'COMPRA DE MERCADORIAS';
                                    continue;
                                }
                            }
                            if(isset($contaTarifaPixId) && $contaTarifaPixId && strpos($tu,'TARIFA') !== false && strpos($tu,'PIX') !== false){
                                $rA['_class_conta_id'] = (string)$contaTarifaPixId;
                                $rA['_class_conta_label'] = $contaTarifaPixLabel ?: 'TARIFAS SOBRE PIX';
                                continue;
                            }
                        }
                        unset($rA);
                    }
                }
                // Garante valores absolutos na coluna VALOR (exceto modos que preservam sinal: Santander/Itaú)
                if(!$isSantanderMode && !$isItauMode){ $rows = $makeValorAbsolute($headers, $rows); }
                // Marca duplicidades a princípio (Empresa+Data+Valor)
                $rows = $markExisting($headers, $rows, $selectedEmpresaId ?? $empresaIdFromFile);
                // Se havia snapshot anterior (F5 com refresh=1), preserva marcações manuais de "Já existe"
                if(isset($oldRows) && is_array($oldRows) && !empty($oldRows)){
                    foreach($rows as $idx=>&$r){
                        if(isset($oldRows[$idx]['_exists_manual'])){
                            $r['_exists_manual'] = (bool)$oldRows[$idx]['_exists_manual'];
                            $auto = !empty($r['_exists']);
                            $r['_exists'] = ($auto || $r['_exists_manual']) ? true : false;
                        }
                    }
                    unset($r);
                }
                // Ao salvar payload, respeita flags (podem ter sido forçadas para desbloqueadas via ?unlock=1)
                $payloadToPut = [
                    'headers'=>$headers,
                    'rows'=>$rows,
                    'file'=>$file,
                    'generated_at'=>now()->toDateTimeString(),
                    'selected_empresa_id'=>$selectedEmpresaId,
                    'empresa_locked'=>$empresaLocked,
                    'global_credit_conta_id'=>$globalCreditContaId,
                    'global_credit_conta_label'=>$globalCreditContaLabel,
                    'global_credit_conta_locked'=>$globalCreditContaLocked,
                    'extrato_mode'=>$extratoMode,
                ];
                $this->previewCachePut($cacheKey, $payloadToPut);
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
            'extratoMode' => $extratoMode,
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
        // Se o usuário alterou a conta manualmente, limpa o flag de origem do arquivo
        if(!empty($rows[$i]['_class_conta_from_file'])){
            if($contaId && isset($rows[$i]['CONTA_DEBITO_ID']) && (string)$rows[$i]['CONTA_DEBITO_ID'] !== (string)$contaId){
                $rows[$i]['_class_conta_from_file'] = false;
            }
            if(!$contaId){ $rows[$i]['_class_conta_from_file'] = false; }
        }
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
    $this->previewCachePut($cacheKey, $payload);
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
    $this->previewCachePut($cacheKey, $payload);
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
    $this->previewCachePut($cacheKey, $payload);
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
    $this->previewCachePut($data['cache_key'], $payload);
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
            ,'rows.*.exists_manual' => 'nullable|boolean'
            ,'global_credit_conta_id' => 'nullable|integer'
            ,'global_credit_conta_label' => 'nullable|string'
        ]);
        if(!Cache::has($data['cache_key'])){
            return response()->json(['ok'=>false,'message'=>'Cache expirado'],410);
        }
        $payload = Cache::get($data['cache_key']);
        $rows = $payload['rows'] ?? [];
        $headersLocal = $payload['headers'] ?? [];
        $ensureDataHeader = false; // sinaliza que devemos incluir 'DATA' no headers
        // helper interno para detectar se um valor parece data (formatos comuns)
        $isDateLike = function($v){
            if($v instanceof \DateTimeInterface) return true;
            if(!is_string($v)) return false;
            $s = trim($v);
            if($s==='') return false;
            if(preg_match('/^\d{2,6}$/',$s)) return true; // possível serial Excel
            if(preg_match('/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/',$s)) return true; // yyyy-mm-dd
            if(preg_match('/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/',$s)) return true; // mm/dd[/yy]
            if(preg_match('/^\d{8}$/',$s)) return true; // yyyymmdd ou ddmmyyyy
            if(preg_match('/^\d{2}[\/]\d{2}[\/]\d{4}$/',$s)) return true; // dd/mm/yyyy
            return false;
        };
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
                $newVal = $r['data'];
                $updatedCell = false;
                // Se existir a coluna DATA no conjunto de headers desta cache, atualiza-a
                if(isset($rows[$i]['DATA'])){
                    $rows[$i]['DATA'] = $newVal; $updatedCell = true;
                } else {
                    // fallback 1: tenta encontrar chave que contenha 'DATA' (caso variação de header)
                    foreach($headersLocal as $hName){
                        if(is_string($hName) && stripos($hName,'DATA') !== false && strpos($hName,'_') !== 0){
                            $rows[$i][$hName] = $newVal; $updatedCell = true; break;
                        }
                    }
                    // fallback 2: tenta identificar a coluna candidata por valor "parecido com data" na linha
                    if(!$updatedCell){
                        foreach($headersLocal as $hName){
                            if(!is_string($hName)) continue;
                            $up = mb_strtoupper($hName,'UTF-8');
                            // ignora campos conhecidos que não são datas
                            if(in_array($up, ['HISTORICO','HISTÓRICO','VALOR','EMPRESA_ID','CONTA_DEBITO_ID','CONTA_DEBITO_LABEL','CONTA_CREDITO_GLOBAL_ID'], true)) continue;
                            if(strpos($hName,'_') === 0) continue; // metas/auxiliares
                            $curr = $rows[$i][$hName] ?? null;
                            if($isDateLike($curr)){
                                $rows[$i][$hName] = $newVal; $updatedCell = true; break;
                            }
                        }
                    }
                }
                // Se ainda não atualizamos nenhuma célula, garanta um campo persistente 'DATA'
                if(!$updatedCell){
                    $rows[$i]['DATA'] = $newVal; $ensureDataHeader = true;
                } else {
                    // Ainda assim garantimos um campo persistente 'DATA' para futuras detecções na UI
                    if(!isset($rows[$i]['DATA'])){ $rows[$i]['DATA'] = $newVal; $ensureDataHeader = true; }
                }
            }
            if(array_key_exists('conta_id',$r)){
                $rows[$i]['_class_conta_id'] = $r['conta_id'];
                $rows[$i]['_class_conta_label'] = $r['conta_label'] ?? null;
                if(isset($rows[$i]['_class_conta_from_file'])){
                    $prevFromFile = $rows[$i]['_class_conta_from_file'];
                    if($prevFromFile){
                        $orig = isset($rows[$i]['CONTA_DEBITO_ID']) ? (string)$rows[$i]['CONTA_DEBITO_ID'] : null;
                        $now = $r['conta_id'] !== null ? (string)$r['conta_id'] : null;
                        if($orig !== $now){ $rows[$i]['_class_conta_from_file'] = false; }
                    }
                }
            }
            // Override manual de existência
            if(array_key_exists('exists_manual',$r)){
                $rows[$i]['_exists_manual'] = (bool)$r['exists_manual'];
                // Normaliza também o campo _exists (usa OR com o automático)
                $auto = !empty($rows[$i]['_exists']);
                $rows[$i]['_exists'] = ($auto || $rows[$i]['_exists_manual']) ? true : false;
            }
        }
        $payload['rows'] = $rows;
        // Se adicionamos DATA em alguma linha e não consta em headers, acrescenta
        if($ensureDataHeader && (!isset($payload['headers']) || !in_array('DATA', $payload['headers'], true))){
            $payload['headers'][] = 'DATA';
        }
        // Atualiza conta crédito global se enviada
        if(array_key_exists('global_credit_conta_id',$data)){
            $payload['global_credit_conta_id'] = $data['global_credit_conta_id'];
            $payload['global_credit_conta_label'] = $data['global_credit_conta_label'] ?? null;
        }
        $payload['updated_at'] = now()->toDateTimeString();
    $this->previewCachePut($data['cache_key'], $payload);
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
    $payload['rows']=$rows; $this->previewCachePut($cacheKey, $payload);
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
    $this->previewCachePut($data['cache_key'], $payload);
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
    $this->previewCachePut($data['cache_key'], $payload);
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
     * Exporta um arquivo de preparação de lançamentos a partir do cache da pré-visualização,
     * incluindo colunas canônicas e um check se já existe lançamento idêntico na base.
     * GET com ?cache_key=...
     */
    public function exportPreviewDespesasPrepareExcel(Request $request)
    {
        $cacheKey = $request->query('cache_key');
        if(!$cacheKey){ abort(422,'cache_key obrigatório'); }
        if(!Cache::has($cacheKey)){ abort(410,'Cache expirado ou inexistente. Recarregue a visualização.'); }

        $payload = Cache::get($cacheKey);
        $headersOrig = $payload['headers'] ?? [];
        $rowsCache = $payload['rows'] ?? [];
        $empresaGlobal = $payload['selected_empresa_id'] ?? null;
        $creditGlobalId = $payload['global_credit_conta_id'] ?? null;

        // Helpers
        $normalizeDate = function($raw){
            if($raw instanceof \DateTimeInterface){ return $raw->format('d/m/Y'); }
            if($raw===null) return null; $v = trim((string)$raw); if($v==='') return null;
            $v = preg_replace('/[T ]\d{1,2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?$/','',$v);
            if(preg_match('/^\d{2,6}$/',$v)){ $n=(int)$v; if($n>59 && $n<60000){ $base=new \DateTimeImmutable('1899-12-30'); $dt=$base->modify("+".((int)round($n))." days"); return $dt->format('d/m/Y'); } }
            $vSep = preg_replace('/[\.\-]/','/',$v); $vSep = preg_replace('/\s+/','/',$vSep);
            if(preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/',$vSep,$m)){ if(checkdate((int)$m[2],(int)$m[3],(int)$m[1])) return sprintf('%02d/%02d/%04d',(int)$m[3],(int)$m[2],(int)$m[1]); }
            if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/',$vSep,$m)){ $y=$m[3]; if(strlen($y)===2){ $y='20'.$y; } if(checkdate((int)$m[2],(int)$m[1],(int)$y)) return sprintf('%02d/%02d/%04d',(int)$m[1],(int)$m[2],(int)$y); }
            if(preg_match('/^(\d{4})(\d{2})(\d{2})$/',$v,$m)){ if(checkdate((int)$m[2],(int)$m[3],(int)$m[1])) return sprintf('%02d/%02d/%04d',(int)$m[3],(int)$m[2],(int)$m[1]); }
            if(preg_match('/^(\d{2})(\d{2})(\d{4})$/',$v,$m)){ if(checkdate((int)$m[2],(int)$m[1],(int)$m[3])) return sprintf('%02d/%02d/%04d',(int)$m[1],(int)$m[2],(int)$m[3]); }
            return null;
        };
        $toYmd = function(?string $br){ if(!$br) return null; try{ return \Carbon\Carbon::createFromFormat('d/m/Y',$br)->format('Y-m-d'); }catch(\Throwable $e){ return null; } };
        $parseValor = function($raw){
            if($raw===null) return null;
            $v = trim((string)$raw);
            if($v==='') return null;
            $negative = false;
            // Parênteses para negativo
            if(preg_match('/^\(.*\)$/', $v)) { $negative = true; $v = preg_replace('/^\(|\)$/','', $v); }
            // Sinal negativo ao final
            if(preg_match('/-$/', $v)) { $negative = true; $v = preg_replace('/-$/','', $v); }
            // Remove símbolos de moeda e plus
            $v = preg_replace('/R\$|BRL|USD|\+/i','', $v);
            // Remove espaços
            $v = preg_replace('/\s+/', '', $v);
            // Mantém apenas dígitos, vírgula, ponto e possível sinal negativo inicial
            $v = preg_replace('/[^0-9.,-]/','', $v);
            $hasDot = strpos($v,'.') !== false; $hasComma = strpos($v,',') !== false;
            if($hasDot && $hasComma){
                $lastDot = strrpos($v,'.');
                $lastComma = strrpos($v,',');
                if($lastDot !== false && $lastComma !== false){
                    if($lastDot > $lastComma){
                        // Ex: 1,234.56 => ponto decimal, vírgula milhar
                        $v = str_replace(',', '', $v);
                        // mantém ponto decimal
                    } else {
                        // Ex: 1.234,56 => vírgula decimal, ponto milhar
                        $v = str_replace('.', '', $v);
                        $v = str_replace(',', '.', $v);
                    }
                }
            } else if($hasComma && !$hasDot){
                // Apenas vírgula -> tratar como decimal
                $v = str_replace(',', '.', $v);
            } else {
                // Apenas ponto: já está como decimal ou inteiro com milhar sem vírgula
                // nada a fazer aqui
            }
            // Se restaram múltiplos pontos, considerar último como decimal
            if(substr_count($v, '.') > 1){
                $parts = explode('.', $v);
                $dec = array_pop($parts);
                $v = implode('', $parts).'.'.$dec;
            }
            if(!is_numeric($v)) return null;
            $num = (float)$v;
            return $negative ? -$num : $num;
        };
        $findHeader = function(array $headers, string $contains){ foreach($headers as $h){ if(stripos($h,$contains)!==false) return $h; } return null; };

        // Preferências de colunas presentes
        $dateCol = 'DATA_NORMALIZADA';
        if(!in_array($dateCol,$headersOrig,true)){
            $dateCol = null;
            foreach($headersOrig as $h){ if(mb_strtoupper($h,'UTF-8')==='DATA'){ $dateCol=$h; break; } }
            if(!$dateCol){
                foreach($headersOrig as $h){ if(stripos($h,'DATA')!==false){ $dateCol=$h; break; } }
            }
        }
        $valorCol = $findHeader($headersOrig,'VALOR');
        $descCol = $findHeader($headersOrig,'DESCRI'); // DESCRICAO / DESCRIÇÃO

        $headings = [
            'EMPRESA_ID','DATA_ORIGINAL','DATA_NORMALIZADA','CONTA_DEBITO_ID','CONTA_CREDITO_GLOBAL_ID','VALOR','HISTORICO_AJUSTADO','DESCRICAO','ROW_HASH','EXISTS','MATCH_IDS'
        ];
        $rowsOut = [];

        foreach($rowsCache as $r){
            // Coleta canônica
            $empresaId = $r['_class_empresa_id'] ?? ($r['EMPRESA_ID'] ?? $empresaGlobal);
            $contaDebId = $r['_class_conta_id'] ?? ($r['CONTA_DEBITO_ID'] ?? null);
            $contaCredId = $creditGlobalId;
            if(isset($r['CONTA_CREDITO_GLOBAL_ID']) && $r['CONTA_CREDITO_GLOBAL_ID']){ $contaCredId = $r['CONTA_CREDITO_GLOBAL_ID']; }
            $dataOrig = $dateCol ? ($r[$dateCol] ?? null) : null;
            $dataNorm = $r['DATA_NORMALIZADA'] ?? $normalizeDate($dataOrig);
            $valorRaw = $valorCol ? ($r[$valorCol] ?? null) : null;
            $valor = $parseValor($valorRaw);
            $descricao = $r['HISTORICO_AJUSTADO'] ?? ($descCol ? ($r[$descCol] ?? null) : null);
            $rowHash = $r['_row_hash'] ?? ($r['ROW_HASH'] ?? null);

            $exists = false; $matchIds = [];
            if($empresaId && $contaDebId && $contaCredId && $dataNorm && $valor!==null){
                $dataYmd = $toYmd($dataNorm);
                if($dataYmd){
                    $matches = Lancamento::query()
                        ->where('EmpresaID',(int)$empresaId)
                        ->where('ContaDebitoID',(int)$contaDebId)
                        ->where('ContaCreditoID',(int)$contaCredId)
                        ->where('DataContabilidade',$dataYmd)
                        ->where('Valor',$valor)
                        ->pluck('ID');
                    if($matches->count()>0){ $exists=true; $matchIds=$matches->all(); }
                }
            }

            $rowsOut[] = [
                'EMPRESA_ID' => $empresaId,
                'DATA_ORIGINAL' => $dataOrig,
                'DATA_NORMALIZADA' => $dataNorm,
                'CONTA_DEBITO_ID' => $contaDebId,
                'CONTA_CREDITO_GLOBAL_ID' => $contaCredId,
                'VALOR' => $valor,
                'HISTORICO_AJUSTADO' => $r['HISTORICO_AJUSTADO'] ?? null,
                'DESCRICAO' => $descricao,
                'ROW_HASH' => $rowHash,
                'EXISTS' => $exists ? 1 : 0,
                'MATCH_IDS' => $exists ? implode(',', $matchIds) : null,
            ];
        }

        $fileName = 'prepare-lancamentos-'.date('Ymd-His').'.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new PreviewDespesasExport($rowsOut,$headings), $fileName);
    }

    /**
     * Processa (simulação) os lançamentos a partir do cache, sem consolidar no BD.
     * POST JSON: { cache_key: string }
     * Retorna resumo com linhas prontas e ignoradas.
     */
    public function processPreviewDespesasLancamentos(Request $request)
    {
        $data = $request->validate([
            'cache_key' => 'required|string',
            'ignore_existing' => 'nullable'
        ]);
        if(!Cache::has($data['cache_key'])){
            return response()->json(['ok'=>false,'message'=>'Cache expirado ou inexistente'], 410);
        }
        $payload = Cache::get($data['cache_key']);
        $headersOrig = $payload['headers'] ?? [];
        $rowsCache = $payload['rows'] ?? [];
        $empresaGlobal = $payload['selected_empresa_id'] ?? null;
    $creditGlobalId = $payload['global_credit_conta_id'] ?? null; // neste modo será a conta do BANCO
    $extratoMode = $payload['extrato_mode'] ?? null;
    $isSantanderMode = ($extratoMode === 'santander');
    $isItauMode = ($extratoMode === 'itau');

        // Helpers (mesmos do prepare)
        $normalizeDate = function($raw){
            if($raw instanceof \DateTimeInterface){ return $raw->format('d/m/Y'); }
            if($raw===null) return null; $v = trim((string)$raw); if($v==='') return null;
            $v = preg_replace('/[T ]\d{1,2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?$/','',$v);
            if(preg_match('/^\d{2,6}$/',$v)){ $n=(int)$v; if($n>59 && $n<60000){ $base=new \DateTimeImmutable('1899-12-30'); $dt=$base->modify("+".((int)round($n))." days"); return $dt->format('d/m/Y'); } }
            $vSep = preg_replace('/[\.\-]/','/',$v); $vSep = preg_replace('/\s+/','/',$vSep);
            if(preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/',$vSep,$m)){ if(checkdate((int)$m[2],(int)$m[3],(int)$m[1])) return sprintf('%02d/%02d/%04d',(int)$m[3],(int)$m[2],(int)$m[1]); }
            if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/',$vSep,$m)){ $y=$m[3]; if(strlen($y)===2){ $y='20'.$y; } if(checkdate((int)$m[2],(int)$m[1],(int)$y)) return sprintf('%02d/%02d/%04d',(int)$m[1],(int)$m[2],(int)$y); }
            if(preg_match('/^(\d{4})(\d{2})(\d{2})$/',$v,$m)){ if(checkdate((int)$m[2],(int)$m[3],(int)$m[1])) return sprintf('%02d/%02d/%04d',(int)$m[3],(int)$m[2],(int)$m[1]); }
            if(preg_match('/^(\d{2})(\d{2})(\d{4})$/',$v,$m)){ if(checkdate((int)$m[2],(int)$m[1],(int)$m[3])) return sprintf('%02d/%02d/%04d',(int)$m[1],(int)$m[2],(int)$m[3]); }
            return null;
        };
        $toYmd = function(?string $br){ if(!$br) return null; try{ return \Carbon\Carbon::createFromFormat('d/m/Y',$br)->format('Y-m-d'); }catch(\Throwable $e){ return null; } };
        $parseValor = function($raw){
            if($raw===null) return null; $v = trim((string)$raw); if($v==='') return null; $negative=false;
            if(preg_match('/^\(.*\)$/',$v)){ $negative=true; $v=preg_replace('/^\(|\)$/','',$v); }
            if(preg_match('/-$/',$v)){ $negative=true; $v=preg_replace('/-$/','',$v); }
            $v = preg_replace('/R\$|BRL|USD|\+/i','',$v); $v=preg_replace('/\s+/','',$v); $v=preg_replace('/[^0-9.,-]/','',$v);
            $hasDot=strpos($v,'.')!==false; $hasComma=strpos($v,',')!==false;
            if($hasDot && $hasComma){
                $lastDot = strrpos($v,'.'); $lastComma = strrpos($v,',');
                if($lastDot > $lastComma){
                    $v = str_replace(',', '', $v); // vírgula milhar
                } else {
                    $v = str_replace('.', '', $v); // ponto milhar
                    $v = str_replace(',', '.', $v); // vírgula decimal
                }
            } else if($hasComma && !$hasDot){ $v=str_replace(',', '.', $v); }
            if(substr_count($v,'.')>1){ $parts=explode('.',$v); $dec=array_pop($parts); $v=implode('',$parts).'.'.$dec; }
            if(!is_numeric($v)) return null; $num=(float)$v; return $negative? -$num : $num;
        };
        $findHeader = function(array $headers, string $contains){ foreach($headers as $h){ if(stripos($h,$contains)!==false) return $h; } return null; };

        // Preferências de colunas
        $dateCol = in_array('DATA_NORMALIZADA',$headersOrig,true) ? 'DATA_NORMALIZADA' : null;
        if(!$dateCol){
            foreach($headersOrig as $h){ if(mb_strtoupper($h,'UTF-8')==='DATA'){ $dateCol=$h; break; } }
            if(!$dateCol){ foreach($headersOrig as $h){ if(stripos($h,'DATA')!==false){ $dateCol=$h; break; } } }
        }
        $valorCol = $findHeader($headersOrig,'VALOR');
        $descCol = $findHeader($headersOrig,'DESCRI');

        $ready=[]; $skipped=[];
        $ignoreExisting = false;
        try{
            // aceitar true/"1"/1
            $v = $data['ignore_existing'] ?? null;
            $ignoreExisting = ($v === true) || ($v === 1) || ($v === '1') || ($v === 'true');
        }catch(\Throwable $e){ $ignoreExisting = false; }
        foreach($rowsCache as $i=>$r){
            $rowNum = $i+1; if($rowNum<4) continue; // mesma regra da validação
            if($ignoreExisting && (!empty($r['_exists']) || !empty($r['_exists_manual']))){ continue; }
            $empresaId = $r['_class_empresa_id'] ?? ($r['EMPRESA_ID'] ?? $empresaGlobal);
            $contaClassificadaId = $r['_class_conta_id'] ?? ($r['CONTA_DEBITO_ID'] ?? null);
            $contaBancoId = $creditGlobalId; if(!empty($r['CONTA_CREDITO_GLOBAL_ID'])) $contaBancoId = $r['CONTA_CREDITO_GLOBAL_ID'];
            $dataOrig = $dateCol ? ($r[$dateCol] ?? null) : null; $dataNorm = $r['DATA_NORMALIZADA'] ?? $normalizeDate($dataOrig);
            $valorRaw = $valorCol ? ($r[$valorCol] ?? null) : null; $valor = $parseValor($valorRaw);
            $descricao = $r['HISTORICO_AJUSTADO'] ?? ($descCol ? ($r[$descCol] ?? null) : null);

            $faltas=[];
            if(!$empresaId) $faltas[]='EMPRESA_ID';
            if(!$dataNorm) $faltas[]='DATA';
            if($valor===null) $faltas[]='VALOR';

            if($isSantanderMode || $isItauMode){
                if(!$contaBancoId) $faltas[]='CONTA_BANCO_ID';
                if(!$contaClassificadaId) $faltas[]='CONTA_CONTRAPARTIDA_ID';
                if(!empty($faltas)){ $skipped[] = ['row'=>$rowNum, 'missing'=>$faltas]; continue; }
                $valorAbs = abs((float)$valor);
                if($valor > 0){
                    $contaDebId = (int)$contaBancoId;
                    $contaCredId = (int)$contaClassificadaId;
                } elseif($valor < 0){
                    $contaDebId = (int)$contaClassificadaId;
                    $contaCredId = (int)$contaBancoId;
                } else {
                    // ignora zeros
                    $skipped[] = ['row'=>$rowNum, 'missing'=>['VALOR_NAO_POSITIVO']];
                    continue;
                }
                $ready[] = [
                    'row'=>$rowNum,
                    'EmpresaID'=>(int)$empresaId,
                    'ContaDebitoID'=>$contaDebId,
                    'ContaCreditoID'=>$contaCredId,
                    'DataContabilidade'=>$toYmd($dataNorm),
                    'Valor'=>$valorAbs,
                    'Descricao'=>$descricao,
                ];
            } else {
                $contaDebId = $contaClassificadaId;
                $contaCredId = $contaBancoId;
                if(!$contaCredId) $faltas[]='CONTA_CREDITO_GLOBAL_ID';
                // Exigir conta débito somente se linha classificável (tem valor)
                $classificavel = ($valor!==null);
                if($classificavel && !$contaDebId) $faltas[]='CONTA_DEBITO_ID';
                if(!empty($faltas)){ $skipped[] = ['row'=>$rowNum, 'missing'=>$faltas]; continue; }
                $ready[] = [
                    'row'=>$rowNum,
                    'EmpresaID'=>(int)$empresaId,
                    'ContaDebitoID'=>(int)$contaDebId,
                    'ContaCreditoID'=>(int)$contaCredId,
                    'DataContabilidade'=>$toYmd($dataNorm),
                    'Valor'=>$valor,
                    'Descricao'=>$descricao,
                ];
            }
        }
        return response()->json([
            'ok'=>true,
            'ready_count'=>count($ready),
            'skipped_count'=>count($skipped),
            'ready'=>$ready,
            'skipped'=>$skipped,
        ]);
    }

    /**
     * Consolida os lançamentos prontos no BD (commit real).
     * POST JSON: { cache_key: string }
     * Reaproveita a mesma lógica de validação do dry-run; duplica a verificação de duplicados.
     */
    public function commitPreviewDespesasLancamentos(Request $request)
    {
        $data = $request->validate([
            'cache_key' => 'required|string',
            'ignore_existing' => 'nullable'
        ]);
        // Garante usuário autenticado (campo Usuarios_id é NOT NULL)
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['ok'=>false,'message'=>'Usuário não autenticado'], 401);
        }
        if(!Cache::has($data['cache_key'])){
            return response()->json(['ok'=>false,'message'=>'Cache expirado ou inexistente'], 410);
        }
        $payload = Cache::get($data['cache_key']);
        $headersOrig = $payload['headers'] ?? [];
        $rowsCache = $payload['rows'] ?? [];
        $empresaGlobal = $payload['selected_empresa_id'] ?? null;
    $creditGlobalId = $payload['global_credit_conta_id'] ?? null; // no modo extrato: conta Banco
    $extratoMode = $payload['extrato_mode'] ?? null;
    $isSantanderMode = ($extratoMode === 'santander');
    $isItauMode = ($extratoMode === 'itau');

        $normalizeDate = function($raw){
            if($raw instanceof \DateTimeInterface){ return $raw->format('d/m/Y'); }
            if($raw===null) return null; $v = trim((string)$raw); if($v==='') return null;
            $v = preg_replace('/[T ]\d{1,2}:\d{2}(?::\d{2})?(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?$/','',$v);
            if(preg_match('/^\d{2,6}$/',$v)){ $n=(int)$v; if($n>59 && $n<60000){ $base=new \DateTimeImmutable('1899-12-30'); $dt=$base->modify("+".((int)round($n))." days"); return $dt->format('d/m/Y'); } }
            $vSep = preg_replace('/[\.-]/','/',$v); $vSep = preg_replace('/\s+/','/',$vSep);
            if(preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/',$vSep,$m)){ if(checkdate((int)$m[2],(int)$m[3],(int)$m[1])) return sprintf('%02d/%02d/%04d',(int)$m[3],(int)$m[2],(int)$m[1]); }
            if(preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/',$vSep,$m)){ $y=$m[3]; if(strlen($y)===2){ $y='20'.$y; } if(checkdate((int)$m[2],(int)$m[1],(int)$y)) return sprintf('%02d/%02d/%04d',(int)$m[1],(int)$m[2],(int)$y); }
            if(preg_match('/^(\d{4})(\d{2})(\d{2})$/',$v,$m)){ if(checkdate((int)$m[2],(int)$m[3],(int)$m[1])) return sprintf('%02d/%02d/%04d',(int)$m[3],(int)$m[2],(int)$m[1]); }
            if(preg_match('/^(\d{2})(\d{2})(\d{4})$/',$v,$m)){ if(checkdate((int)$m[2],(int)$m[1],(int)$m[3])) return sprintf('%02d/%02d/%04d',(int)$m[1],(int)$m[2],(int)$m[3]); }
            return null;
        };
        $toYmd = function(?string $br){ if(!$br) return null; try{ return \Carbon\Carbon::createFromFormat('d/m/Y',$br)->format('Y-m-d'); }catch(\Throwable $e){ return null; } };
        $parseValor = function($raw){
            if($raw===null) return null; $v = trim((string)$raw); if($v==='') return null; $negative=false;
            if(preg_match('/^\(.*\)$/',$v)){ $negative=true; $v=preg_replace('/^\(|\)$/','',$v); }
            if(preg_match('/-$/',$v)){ $negative=true; $v=preg_replace('/-$/','',$v); }
            $v = preg_replace('/R\$|BRL|USD|\+/i','',$v); $v=preg_replace('/\s+/','',$v); $v=preg_replace('/[^0-9.,-]/','',$v);
            $hasDot=strpos($v,'.')!==false; $hasComma=strpos($v,',')!==false;
            if($hasDot && $hasComma){ $lastDot=strrpos($v,'.'); $lastComma=strrpos($v,','); if($lastDot>$lastComma){ $v=str_replace(',','',$v); } else { $v=str_replace('.','',$v); $v=str_replace(',', '.', $v); } }
            else if($hasComma && !$hasDot){ $v=str_replace(',', '.', $v); }
            if(substr_count($v,'.')>1){ $parts=explode('.',$v); $dec=array_pop($parts); $v=implode('',$parts).'.'.$dec; }
            if(!is_numeric($v)) return null; $num=(float)$v; return $negative? -$num : $num;
        };
        $findHeader = function(array $headers, string $contains){ foreach($headers as $h){ if(stripos($h,$contains)!==false) return $h; } return null; };

        $dateCol = in_array('DATA_NORMALIZADA',$headersOrig,true) ? 'DATA_NORMALIZADA' : null;
        if(!$dateCol){ foreach($headersOrig as $h){ if(mb_strtoupper($h,'UTF-8')==='DATA'){ $dateCol=$h; break; } } if(!$dateCol){ foreach($headersOrig as $h){ if(stripos($h,'DATA')!==false){ $dateCol=$h; break; } } } }
        $valorCol = $findHeader($headersOrig,'VALOR');
        $descCol = $findHeader($headersOrig,'DESCRI');

        $ready=[]; $skipped=[];
        $ignoreExisting = false;
        try{
            $v = $data['ignore_existing'] ?? null;
            $ignoreExisting = ($v === true) || ($v === 1) || ($v === '1') || ($v === 'true');
        }catch(\Throwable $e){ $ignoreExisting = false; }
        foreach($rowsCache as $i=>$r){
            $rowNum = $i+1; if($rowNum<4) continue;
            if($ignoreExisting && (!empty($r['_exists']) || !empty($r['_exists_manual']))){ continue; }
            $empresaId = $r['_class_empresa_id'] ?? ($r['EMPRESA_ID'] ?? $empresaGlobal);
            $contaClassificadaId = $r['_class_conta_id'] ?? ($r['CONTA_DEBITO_ID'] ?? null);
            $contaBancoId = $creditGlobalId; if(!empty($r['CONTA_CREDITO_GLOBAL_ID'])) $contaBancoId = $r['CONTA_CREDITO_GLOBAL_ID'];
            $dataOrig = $dateCol ? ($r[$dateCol] ?? null) : null; $dataNorm = $r['DATA_NORMALIZADA'] ?? $normalizeDate($dataOrig);
            $valorRaw = $valorCol ? ($r[$valorCol] ?? null) : null; $valor = $parseValor($valorRaw);
            $descricao = $r['HISTORICO_AJUSTADO'] ?? ($descCol ? ($r[$descCol] ?? null) : null);

            $faltas=[]; $classificavel=($valor!==null);
            if(!$empresaId) $faltas[]='EMPRESA_ID';
            if(!$dataNorm) $faltas[]='DATA';
            if($valor===null) $faltas[]='VALOR';

            if($isSantanderMode || $isItauMode){
                if(!$contaBancoId) $faltas[]='CONTA_BANCO_ID';
                if(!$contaClassificadaId) $faltas[]='CONTA_CONTRAPARTIDA_ID';
                if(!empty($faltas)){ $skipped[]=['row'=>$rowNum,'missing'=>$faltas]; continue; }
                $valorAbs = abs((float)$valor);
                if($valor > 0){
                    $contaDebId = (int)$contaBancoId; // entrada: banco em DÉBITO
                    $contaCredId = (int)$contaClassificadaId; // contrapartida em CRÉDITO
                } elseif($valor < 0){
                    $contaDebId = (int)$contaClassificadaId; // saída: contrapartida em DÉBITO
                    $contaCredId = (int)$contaBancoId; // banco em CRÉDITO
                } else {
                    $skipped[]=['row'=>$rowNum,'missing'=>['VALOR_NAO_POSITIVO']];
                    continue;
                }
                $ready[]=[
                    'row'=>$rowNum,
                    'EmpresaID'=>(int)$empresaId,
                    'ContaDebitoID'=>$contaDebId,
                    'ContaCreditoID'=>$contaCredId,
                    'DataContabilidade'=>$toYmd($dataNorm),
                    'Valor'=>$valorAbs,
                    'Descricao'=>$descricao,
                ];
            } else {
                $contaDebId = $contaClassificadaId; $contaCredId = $contaBancoId;
                if(!$contaCredId) $faltas[]='CONTA_CREDITO_GLOBAL_ID';
                if($classificavel && !$contaDebId) $faltas[]='CONTA_DEBITO_ID';
                if(!empty($faltas)){ $skipped[]=['row'=>$rowNum,'missing'=>$faltas]; continue; }
                $ready[]=[
                    'row'=>$rowNum,
                    'EmpresaID'=>(int)$empresaId,
                    'ContaDebitoID'=>(int)$contaDebId,
                    'ContaCreditoID'=>(int)$contaCredId,
                    'DataContabilidade'=>$toYmd($dataNorm),
                    'Valor'=>$valor,
                    'Descricao'=>$descricao,
                ];
            }
        }

        $committedIds=[]; $skippedExisting=[];
        \Illuminate\Support\Facades\DB::beginTransaction();
        try{
            foreach($ready as $item){
                // Evita duplicidades exatas
                $exists = Lancamento::query()
                    ->where('EmpresaID',$item['EmpresaID'])
                    ->where('ContaDebitoID',$item['ContaDebitoID'])
                    ->where('ContaCreditoID',$item['ContaCreditoID'])
                    ->where('DataContabilidade',$item['DataContabilidade'])
                    ->where('Valor',$item['Valor'])
                    ->exists();
                if($exists){ $skippedExisting[]=$item; continue; }
                $novo = new Lancamento();
                $novo->EmpresaID = $item['EmpresaID'];
                $novo->ContaDebitoID = $item['ContaDebitoID'];
                $novo->ContaCreditoID = $item['ContaCreditoID'];
                $novo->DataContabilidade = $item['DataContabilidade'];
                $novo->Valor = $item['Valor'];
                $novo->Descricao = $item['Descricao'];
                $novo->Usuarios_id = $userId;
                $novo->Conferido = false; // padrão seguro
                $novo->save();
                $committedIds[] = $novo->ID ?? $novo->getKey();
            }
            \Illuminate\Support\Facades\DB::commit();
        }catch(\Throwable $e){
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['ok'=>false,'message'=>'Falha ao consolidar: '.$e->getMessage()], 500);
        }

        // Limpa caches de views/otimizações após commit para evitar aviso
        // "preg_replace(): Compilation failed: missing terminating ] for character class ..."
        // que ocorre em reimportações até rodar manualmente optimize:clear
        try {
            Artisan::call('optimize:clear'); // inclui view:clear, route:clear, config:clear
        } catch (\Throwable $e) {
            Log::warning('optimize:clear pós-import falhou: '.$e->getMessage());
            // fallback mínimo: tenta limpar apenas views
            try { Artisan::call('view:clear'); } catch (\Throwable $e2) { /* ignora */ }
        }

        return response()->json([
            'ok'=>true,
            'ready_count'=>count($ready),
            'committed_count'=>count($committedIds),
            'skipped_existing_count'=>count($skippedExisting),
            'committed_ids'=>$committedIds,
        ]);
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
            $payloadToPut = [
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
            ];
            $this->previewCachePut($cacheKey, $payloadToPut);
            return redirect()->route('lancamentos.preview.despesas',[ 'file'=>$storedName, 'limite'=>count($rows) ])->with('status','Export importado com sucesso.');
        }catch(\Throwable $e){
            return back()->with('error','Falha ao importar: '.$e->getMessage());
        }
    }


    /**
     * Balancete por período: soma Débito, Crédito e Saldo (Débito - Crédito) por conta da empresa.
     * GET /lancamentos/balancete?empresa_id=...&de=YYYY-MM-DD&ate=YYYY-MM-DD
     */
    public function balancete(Request $request)
    {
        $showTrail = $request->boolean('trail', true);
        $showHier = $request->boolean('hier', false);
        $showPrev = $request->boolean('prev', true); // somar/exibir saldo anterior (contas 1 e 2)
        // Exclusividade: se hierárquico estiver ligado, desliga trilha; caso contrário, respeita trilha
        if ($showHier) {
            $showTrail = false;
        } elseif ($showTrail) {
            $showHier = false;
        }
        // Empresas permitidas ao usuário
        $empresas = \App\Models\Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->id())
            ->orderBy('Empresas.Descricao')
            ->get(['Empresas.ID','Empresas.Descricao']);

        $data = $request->validate([
            'empresa_id' => 'nullable|integer',
            'de' => 'nullable|date',
            'ate' => 'nullable|date',
        ]);
        $empresaId = $data['empresa_id'] ?? ($empresas->first()->ID ?? null);
        $de = isset($data['de']) ? new \DateTimeImmutable($data['de']) : null;
        $ate = isset($data['ate']) ? new \DateTimeImmutable($data['ate']) : null;
        $empresaNome = '';
        try {
            $row = $empresas->firstWhere('ID', $empresaId);
            if ($row) { $empresaNome = (string)$row->Descricao; }
        } catch (\Throwable $e) { /* noop */ }

        $linhas = [];
        $totDeb = 0.0; $totCred = 0.0; $totSaldo = 0.0;
    if ($empresaId) {
            // Débitos por conta (ContaDebitoID)
            $debPorConta = \App\Models\Lancamento::query()
                ->empresa($empresaId)
                ->naoExcluido()
                ->periodo($de, $ate)
                ->selectRaw('ContaDebitoID as conta_id, SUM(Valor) as total')
                ->groupBy('ContaDebitoID')
                ->pluck('total','conta_id')
                ->toArray();
            // Créditos por conta (ContaCreditoID)
            $credPorConta = \App\Models\Lancamento::query()
                ->empresa($empresaId)
                ->naoExcluido()
                ->periodo($de, $ate)
                ->selectRaw('ContaCreditoID as conta_id, SUM(Valor) as total')
                ->groupBy('ContaCreditoID')
                ->pluck('total','conta_id')
                ->toArray();

            $contaIds = array_values(array_unique(array_merge(array_keys($debPorConta), array_keys($credPorConta))));
            $labels = [];
            if (!empty($contaIds)) {
                $labels = \App\Models\Conta::whereIn('Contas.ID', $contaIds)
                    ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                    ->get(['Contas.ID','PlanoContas.Descricao','PlanoContas.Codigo'])
                    ->mapWithKeys(function($row){
                        return [
                            $row->ID => [
                                'descricao' => $row->Descricao,
                                'codigo' => $row->Codigo,
                            ]
                        ];
                    })
                    ->toArray();
            }

            // Saldo anterior: movimentos antes da data inicial, apenas se houver data inicial
            $priorDebPorConta = $priorCredPorConta = [];
            $deIso = $de?->format('Y-m-d');
            if ($deIso) {
                $priorDebPorConta = \App\Models\Lancamento::query()
                    ->empresa($empresaId)
                    ->naoExcluido()
                    ->whereDate('DataContabilidade', '<', $deIso)
                    ->selectRaw('ContaDebitoID as conta_id, SUM(Valor) as total')
                    ->groupBy('ContaDebitoID')
                    ->pluck('total','conta_id')
                    ->toArray();
                $priorCredPorConta = \App\Models\Lancamento::query()
                    ->empresa($empresaId)
                    ->naoExcluido()
                    ->whereDate('DataContabilidade', '<', $deIso)
                    ->selectRaw('ContaCreditoID as conta_id, SUM(Valor) as total')
                    ->groupBy('ContaCreditoID')
                    ->pluck('total','conta_id')
                    ->toArray();
            }

            foreach ($contaIds as $cid) {
                $fullCode = (string)($labels[$cid]['codigo'] ?? '');
                $codigoTop = '';
                if ($fullCode !== '') {
                    $parts = explode('.', $fullCode);
                    $codigoTop = $parts[0] ?? '';
                    if (!in_array($codigoTop, ['1','2','3','4'], true)) {
                        $codigoTop = substr($fullCode, 0, 1);
                        if (!in_array($codigoTop, ['1','2','3','4'], true)) { $codigoTop = ''; }
                    }
                }
                $deb = (float)($debPorConta[$cid] ?? 0);
                $cred = (float)($credPorConta[$cid] ?? 0);
                $saldoPeriodo = $deb - $cred;
                // Saldo anterior apenas para patrimoniais (1 e 2)
                $saldoAnterior = 0.0;
                if ($deIso && $showPrev && in_array($codigoTop, ['1','2'], true)) {
                    $opDeb = (float)($priorDebPorConta[$cid] ?? 0);
                    $opCred = (float)($priorCredPorConta[$cid] ?? 0);
                    $saldoAnterior = $opDeb - $opCred;
                }
                $saldo = ($showPrev && in_array($codigoTop, ['1','2'], true)) ? ($saldoAnterior + $saldoPeriodo) : $saldoPeriodo;
                $linhas[] = [
                    'conta_id' => $cid,
                    'conta' => $labels[$cid]['descricao'] ?? ('#'.$cid),
                    'codigo' => $fullCode,
                    'codigo_top' => $codigoTop,
                    'debito' => $deb,
                    'credito' => $cred,
                    'saldo' => $saldo,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_periodo' => $saldoPeriodo,
                ];
                $totDeb += $deb; $totCred += $cred; $totSaldo += $saldo;
            }

            // Ordena por Código (classificação), ex.: 1.01.01.01
            usort($linhas, function($a,$b){ return strnatcasecmp($a['codigo'] ?? '', $b['codigo'] ?? ''); });
        }
        // Agrupa por nível 1 (1/2/3/4) com subtotais
        $grupos = $this->groupLinhasByCodigoTop($linhas);

        // DRE (Receitas, Despesas, Resultado)
        [$dreReceitas, $dreDespesas, $dreResultado, $dreIsPrejuizo] = $this->computeDre($grupos);

        $deBr = $de?->format('d/m/Y');
        $ateBr = $ate?->format('d/m/Y');
        // Verificação: existem classificações abaixo do grau 5?
        $belowGrau5Count = 0;
        $minGrau = PHP_INT_MAX; $maxGrau = 0; $countGrau5Plus = 0;
        foreach ($linhas as &$l) {
            $code = (string)($l['codigo'] ?? '');
            $grau = 0;
            if ($code !== '') {
                $parts = array_filter(explode('.', $code), function($p){ return $p !== '' && $p !== null; });
                $grau = count($parts);
            }
            $l['codigo_grau'] = $grau;
            if ($grau > 0 && $grau < 5) { $belowGrau5Count++; }
            if ($grau > 0) {
                if ($grau < $minGrau) { $minGrau = $grau; }
                if ($grau > $maxGrau) { $maxGrau = $grau; }
            }
            if ($grau >= 5) { $countGrau5Plus++; }
        }
        unset($l);
        $hasBelowGrau5 = $belowGrau5Count > 0;
    // Coleta prefixes de Grau 1..4 presentes para buscar descrições no Plano de Contas
        $prefixSets = [1=>[],2=>[],3=>[],4=>[]];
        foreach ($linhas as $lP) {
            $code = (string)($lP['codigo'] ?? '');
            if ($code === '') continue;
            $parts = array_values(array_filter(explode('.', $code), fn($p)=>$p!=='' && $p!==null));
            $cnt = count($parts);
            if ($cnt >= 1) { $prefixSets[1][implode('.', array_slice($parts,0,1))] = true; }
            if ($cnt >= 2) { $prefixSets[2][implode('.', array_slice($parts,0,2))] = true; }
            if ($cnt >= 3) { $prefixSets[3][implode('.', array_slice($parts,0,3))] = true; }
            if ($cnt >= 4) { $prefixSets[4][implode('.', array_slice($parts,0,4))] = true; }
        }
        $grau1Labels = $grau2Labels = $grau3Labels = $grau4Labels = [];
        try {
            if (!empty($prefixSets[1])) {
                $grau1Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')
                    ->where('Grau', 1)
                    ->whereIn('Codigo', array_keys($prefixSets[1]))
                    ->pluck('Descricao', 'Codigo')
                    ->toArray();
            }
        } catch (\Throwable $e) { $grau1Labels = []; }
        try {
            if (!empty($prefixSets[2])) {
                $grau2Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')
                    ->where('Grau', 2)
                    ->whereIn('Codigo', array_keys($prefixSets[2]))
                    ->pluck('Descricao', 'Codigo')
                    ->toArray();
            }
        } catch (\Throwable $e) { $grau2Labels = []; }
        try {
            if (!empty($prefixSets[3])) {
                $grau3Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')
                    ->where('Grau', 3)
                    ->whereIn('Codigo', array_keys($prefixSets[3]))
                    ->pluck('Descricao', 'Codigo')
                    ->toArray();
            }
        } catch (\Throwable $e) { $grau3Labels = []; }
        try {
            if (!empty($prefixSets[4])) {
                $grau4Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')
                    ->where('Grau', 4)
                    ->whereIn('Codigo', array_keys($prefixSets[4]))
                    ->pluck('Descricao', 'Codigo')
                    ->toArray();
            }
        } catch (\Throwable $e) { $grau4Labels = []; }
        return view('Lancamentos.balancete', [
            'empresas' => $empresas,
            'empresaId' => $empresaId,
            'empresaNome' => $empresaNome,
            'de' => $de?->format('Y-m-d'),
            'ate' => $ate?->format('Y-m-d'),
            'deBr' => $deBr,
            'ateBr' => $ateBr,
            'linhas' => $linhas,
            'grupos' => $grupos,
            'totDeb' => $totDeb,
            'totCred' => $totCred,
            'totSaldo' => $totSaldo,
            'dreReceitas' => $dreReceitas,
            'dreDespesas' => $dreDespesas,
            'dreResultado' => $dreResultado,
            'dreIsPrejuizo' => $dreIsPrejuizo,
            'hasBelowGrau5' => $hasBelowGrau5,
            'belowGrau5Count' => $belowGrau5Count,
            'minGrau' => $minGrau === PHP_INT_MAX ? null : $minGrau,
            'maxGrau' => $maxGrau ?: null,
            'countGrau5Plus' => $countGrau5Plus,
            'showTrail' => $showTrail,
            'showHier' => $showHier,
            'showPrev' => $showPrev,
            'grau1Labels' => $grau1Labels,
            'grau2Labels' => $grau2Labels,
            'grau3Labels' => $grau3Labels,
            'grau4Labels' => $grau4Labels,
        ]);
    }

    // Exporta balancete em XLSX
    public function balanceteExportXlsx(Request $request)
    {
        // Usar payload completo para compor nome de arquivo com empresa e período
        [$empresas,$empresaId,$linhas,$totDeb,$totCred,$totSaldo,$de,$ate] = $this->buildBalancetePayload($request);
        $showTrail = $request->boolean('trail', true);
        $showHier = $request->boolean('hier', false);
        $showPrev = $request->boolean('prev', true);
        if ($showHier) { $showTrail = false; } // exclusividade
        $empresaNome = '';
        try {
            $row = $empresas->firstWhere('ID', $empresaId);
            if ($row) { $empresaNome = (string)$row->Descricao; }
        } catch (\Throwable $e) { /* noop */ }
        $empresaSlug = \Illuminate\Support\Str::slug($empresaNome ?: 'empresa', '_');
        $deIso = $de ?: 'sem-de';
        $ateIso = $ate ?: 'sem-ate';
        $grupos = $this->groupLinhasByCodigoTop($linhas);
        // Coletar labels 1..4 para trilha/hierarquia
        $prefixSets = [1=>[],2=>[],3=>[],4=>[]];
        foreach ($linhas as $lP) {
            $code = (string)($lP['codigo'] ?? '');
            if ($code === '') continue;
            $parts = array_values(array_filter(explode('.', $code), fn($p)=>$p!=='' && $p!==null));
            $cnt = count($parts);
            if ($cnt >= 1) { $prefixSets[1][implode('.', array_slice($parts,0,1))] = true; }
            if ($cnt >= 2) { $prefixSets[2][implode('.', array_slice($parts,0,2))] = true; }
            if ($cnt >= 3) { $prefixSets[3][implode('.', array_slice($parts,0,3))] = true; }
            if ($cnt >= 4) { $prefixSets[4][implode('.', array_slice($parts,0,4))] = true; }
        }
        $grau1Labels = $grau2Labels = $grau3Labels = $grau4Labels = [];
        try { if (!empty($prefixSets[1])) { $grau1Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',1)->whereIn('Codigo', array_keys($prefixSets[1]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau1Labels = []; }
        try { if (!empty($prefixSets[2])) { $grau2Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',2)->whereIn('Codigo', array_keys($prefixSets[2]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau2Labels = []; }
        try { if (!empty($prefixSets[3])) { $grau3Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',3)->whereIn('Codigo', array_keys($prefixSets[3]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau3Labels = []; }
        try { if (!empty($prefixSets[4])) { $grau4Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',4)->whereIn('Codigo', array_keys($prefixSets[4]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau4Labels = []; }
        $file = "balancete_{$empresaSlug}_{$deIso}_{$ateIso}.xlsx";
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BalanceteExport($linhas,$totDeb,$totCred,$totSaldo,$grupos,$showTrail,$grau1Labels,$grau2Labels,$grau3Labels,$grau4Labels,$showHier,$showPrev), $file);
    }

    // Exporta balancete em CSV
    public function balanceteExportCsv(Request $request)
    {
        [$empresas,$empresaId,$linhas,$totDeb,$totCred,$totSaldo,$de,$ate] = $this->buildBalancetePayload($request);
        $showTrail = $request->boolean('trail', true);
        $showHier = $request->boolean('hier', false);
        $showPrev = $request->boolean('prev', true);
        if ($showHier) { $showTrail = false; }
        $empresaNome = '';
        try {
            $row = $empresas->firstWhere('ID', $empresaId);
            if ($row) { $empresaNome = (string)$row->Descricao; }
        } catch (\Throwable $e) { /* noop */ }
        $empresaSlug = \Illuminate\Support\Str::slug($empresaNome ?: 'empresa', '_');
        $deIso = $de ?: 'sem-de';
        $ateIso = $ate ?: 'sem-ate';
        $grupos = $this->groupLinhasByCodigoTop($linhas);
        // Coletar labels 1..4 para trilha/hierarquia
        $prefixSets = [1=>[],2=>[],3=>[],4=>[]];
        foreach ($linhas as $lP) {
            $code = (string)($lP['codigo'] ?? '');
            if ($code === '') continue;
            $parts = array_values(array_filter(explode('.', $code), fn($p)=>$p!=='' && $p!==null));
            $cnt = count($parts);
            if ($cnt >= 1) { $prefixSets[1][implode('.', array_slice($parts,0,1))] = true; }
            if ($cnt >= 2) { $prefixSets[2][implode('.', array_slice($parts,0,2))] = true; }
            if ($cnt >= 3) { $prefixSets[3][implode('.', array_slice($parts,0,3))] = true; }
            if ($cnt >= 4) { $prefixSets[4][implode('.', array_slice($parts,0,4))] = true; }
        }
        $grau1Labels = $grau2Labels = $grau3Labels = $grau4Labels = [];
        try { if (!empty($prefixSets[1])) { $grau1Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',1)->whereIn('Codigo', array_keys($prefixSets[1]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau1Labels = []; }
        try { if (!empty($prefixSets[2])) { $grau2Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',2)->whereIn('Codigo', array_keys($prefixSets[2]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau2Labels = []; }
        try { if (!empty($prefixSets[3])) { $grau3Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',3)->whereIn('Codigo', array_keys($prefixSets[3]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau3Labels = []; }
        try { if (!empty($prefixSets[4])) { $grau4Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',4)->whereIn('Codigo', array_keys($prefixSets[4]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau4Labels = []; }
        $file = "balancete_{$empresaSlug}_{$deIso}_{$ateIso}.csv";
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BalanceteExport($linhas,$totDeb,$totCred,$totSaldo,$grupos,$showTrail,$grau1Labels,$grau2Labels,$grau3Labels,$grau4Labels,$showHier,$showPrev), $file, \Maatwebsite\Excel\Excel::CSV);
    }

    // Exporta balancete em PDF (renderiza a mesma view em PDF)
    public function balanceteExportPdf(Request $request)
    {
        // buildBalancetePayload retorna: [empresas, empresaId, linhas, totDeb, totCred, totSaldo, de, ate]
        [$empresas,$empresaId,$linhas,$totDeb,$totCred,$totSaldo,$de,$ate] = $this->buildBalancetePayload($request);
    $showTrail = $request->boolean('trail', true);
    $showHier = $request->boolean('hier', false);
    if ($showHier) { $showTrail = false; }
    $showPrev = $request->boolean('prev', true);
        $empresaNome = '';
        try {
            $row = $empresas->firstWhere('ID', $empresaId);
            if ($row) { $empresaNome = (string)$row->Descricao; }
        } catch (\Throwable $e) { /* noop */ }
    $grupos = $this->groupLinhasByCodigoTop($linhas);
    // $de/$ate vindos do payload são strings 'Y-m-d'; formatar com segurança para pt-BR
    $deBr = $de ? date('d/m/Y', strtotime($de)) : null;
    $ateBr = $ate ? date('d/m/Y', strtotime($ate)) : null;
        [$dreReceitas, $dreDespesas, $dreResultado, $dreIsPrejuizo] = $this->computeDre($grupos);
        // Coletar labels 1..4 para trilha/hierarquia no PDF
        $prefixSets = [1=>[],2=>[],3=>[],4=>[]];
        foreach ($linhas as $lP) {
            $code = (string)($lP['codigo'] ?? '');
            if ($code === '') continue;
            $parts = array_values(array_filter(explode('.', $code), fn($p)=>$p!=='' && $p!==null));
            $cnt = count($parts);
            if ($cnt >= 1) { $prefixSets[1][implode('.', array_slice($parts,0,1))] = true; }
            if ($cnt >= 2) { $prefixSets[2][implode('.', array_slice($parts,0,2))] = true; }
            if ($cnt >= 3) { $prefixSets[3][implode('.', array_slice($parts,0,3))] = true; }
            if ($cnt >= 4) { $prefixSets[4][implode('.', array_slice($parts,0,4))] = true; }
        }
        $grau1Labels = $grau2Labels = $grau3Labels = $grau4Labels = [];
        try { if (!empty($prefixSets[1])) { $grau1Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',1)->whereIn('Codigo', array_keys($prefixSets[1]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau1Labels = []; }
        try { if (!empty($prefixSets[2])) { $grau2Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',2)->whereIn('Codigo', array_keys($prefixSets[2]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau2Labels = []; }
        try { if (!empty($prefixSets[3])) { $grau3Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',3)->whereIn('Codigo', array_keys($prefixSets[3]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau3Labels = []; }
        try { if (!empty($prefixSets[4])) { $grau4Labels = \Illuminate\Support\Facades\DB::table('Contabilidade.PlanoContas')->where('Grau',4)->whereIn('Codigo', array_keys($prefixSets[4]))->pluck('Descricao','Codigo')->toArray(); } } catch (\Throwable $e) { $grau4Labels = []; }
    $html = view('Lancamentos.balancete_pdf', compact('empresas','empresaId','empresaNome','de','ate','deBr','ateBr','linhas','grupos','totDeb','totCred','totSaldo','dreReceitas','dreDespesas','dreResultado','dreIsPrejuizo','showTrail','showHier','showPrev','grau1Labels','grau2Labels','grau3Labels','grau4Labels'))->render();
        $empresaSlug = \Illuminate\Support\Str::slug($empresaNome ?: 'empresa', '_');
        $deIso = $de ?: 'sem-de';
        $ateIso = $ate ?: 'sem-ate';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        return $pdf->download("balancete_{$empresaSlug}_{$deIso}_{$ateIso}.pdf");
    }

    // Helper interno para montar os dados do balancete (linhas + totais)
    protected function buildBalanceteData(Request $request): array
    {
        [$linhas,$totDeb,$totCred,$totSaldo] = [[],0.0,0.0,0.0];
        $payload = $this->buildBalancetePayload($request);
        // Retorna somente o trio principal
        return [$payload[2], $payload[3], $payload[4], $payload[5]]; // linhas, totDeb, totCred, totSaldo
    }

    // Helper para montar também empresas/filtros usados na view/pdf
    protected function buildBalancetePayload(Request $request): array
    {
        $empresas = \App\Models\Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->id())
            ->orderBy('Empresas.Descricao')
            ->get(['Empresas.ID','Empresas.Descricao']);

        $data = $request->validate([
            'empresa_id' => 'nullable|integer',
            'de' => 'nullable|date',
            'ate' => 'nullable|date',
        ]);
        $empresaId = $data['empresa_id'] ?? ($empresas->first()->ID ?? null);
        $de = isset($data['de']) ? new \DateTimeImmutable($data['de']) : null;
        $ate = isset($data['ate']) ? new \DateTimeImmutable($data['ate']) : null;

        $linhas = [];
        $totDeb = 0.0; $totCred = 0.0; $totSaldo = 0.0;
        if ($empresaId) {
            $debPorConta = \App\Models\Lancamento::query()->empresa($empresaId)->naoExcluido()->periodo($de, $ate)
                ->selectRaw('ContaDebitoID as conta_id, SUM(Valor) as total')
                ->groupBy('ContaDebitoID')->pluck('total','conta_id')->toArray();
            $credPorConta = \App\Models\Lancamento::query()->empresa($empresaId)->naoExcluido()->periodo($de, $ate)
                ->selectRaw('ContaCreditoID as conta_id, SUM(Valor) as total')
                ->groupBy('ContaCreditoID')->pluck('total','conta_id')->toArray();
            $contaIds = array_values(array_unique(array_merge(array_keys($debPorConta), array_keys($credPorConta))));
            $labels = [];
            if (!empty($contaIds)) {
                $labels = \App\Models\Conta::whereIn('Contas.ID', $contaIds)
                    ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                    ->get(['Contas.ID','PlanoContas.Descricao','PlanoContas.Codigo'])
                    ->mapWithKeys(function($row){
                        return [
                            $row->ID => [
                                'descricao' => $row->Descricao,
                                'codigo' => $row->Codigo,
                            ]
                        ];
                    })
                    ->toArray();
            }
            // Saldo anterior: movimentos antes da data inicial, apenas se houver data inicial
            $priorDebPorConta = $priorCredPorConta = [];
            $deIso = $de?->format('Y-m-d');
            if ($deIso) {
                $priorDebPorConta = \App\Models\Lancamento::query()
                    ->empresa($empresaId)
                    ->naoExcluido()
                    ->whereDate('DataContabilidade', '<', $deIso)
                    ->selectRaw('ContaDebitoID as conta_id, SUM(Valor) as total')
                    ->groupBy('ContaDebitoID')
                    ->pluck('total','conta_id')
                    ->toArray();
                $priorCredPorConta = \App\Models\Lancamento::query()
                    ->empresa($empresaId)
                    ->naoExcluido()
                    ->whereDate('DataContabilidade', '<', $deIso)
                    ->selectRaw('ContaCreditoID as conta_id, SUM(Valor) as total')
                    ->groupBy('ContaCreditoID')
                    ->pluck('total','conta_id')
                    ->toArray();
            }
            foreach ($contaIds as $cid) {
                $fullCode = (string)($labels[$cid]['codigo'] ?? '');
                $codigoTop = '';
                if ($fullCode !== '') {
                    $parts = explode('.', $fullCode);
                    $codigoTop = $parts[0] ?? '';
                    if (!in_array($codigoTop, ['1','2','3','4'], true)) {
                        $codigoTop = substr($fullCode, 0, 1);
                        if (!in_array($codigoTop, ['1','2','3','4'], true)) { $codigoTop = ''; }
                    }
                }
                $deb = (float)($debPorConta[$cid] ?? 0);
                $cred = (float)($credPorConta[$cid] ?? 0);
                $saldoPeriodo = $deb - $cred;
                $saldoAnterior = 0.0;
                if ($deIso && in_array($codigoTop, ['1','2'], true)) {
                    $opDeb = (float)($priorDebPorConta[$cid] ?? 0);
                    $opCred = (float)($priorCredPorConta[$cid] ?? 0);
                    $saldoAnterior = $opDeb - $opCred;
                }
                $saldo = in_array($codigoTop, ['1','2'], true) ? ($saldoAnterior + $saldoPeriodo) : $saldoPeriodo;
                $linhas[] = [
                    'conta_id' => $cid,
                    'conta' => $labels[$cid]['descricao'] ?? ('#'.$cid),
                    'codigo' => $fullCode,
                    'codigo_top' => $codigoTop,
                    'debito' => $deb,
                    'credito' => $cred,
                    'saldo' => $saldo,
                    'saldo_anterior' => $saldoAnterior,
                ];
                $totDeb += $deb; $totCred += $cred; $totSaldo += $saldo;
            }
            // Ordena por Código (classificação)
            usort($linhas, function($a,$b){ return strnatcasecmp($a['codigo'] ?? '', $b['codigo'] ?? ''); });
        }
        return [$empresas, $empresaId, $linhas, $totDeb, $totCred, $totSaldo, $de?->format('Y-m-d'), $ate?->format('Y-m-d')];
    }

    // Agrupa linhas por código de classificação de nível 1 (1/2/3/4) com subtotais
    protected function groupLinhasByCodigoTop(array $linhas): array
    {
    $labels = [ '1' => 'ATIVO', '2' => 'PASSIVO', '3' => 'DESPESAS', '4' => 'RECEITAS', 'outros' => 'Sem classificação' ];
        $order = ['1','2','3','4','outros'];
        $grupos = [];
        foreach ($order as $k) {
            $grupos[$k] = [ 'key' => $k, 'label' => $labels[$k], 'linhas' => [], 'totDeb' => 0.0, 'totCred' => 0.0, 'totSaldo' => 0.0 ];
        }
        foreach ($linhas as $l) {
            $k = $l['codigo_top'] ?? '';
            if (!in_array($k, ['1','2','3','4'], true)) { $k = 'outros'; }
            $grupos[$k]['linhas'][] = $l;
            $grupos[$k]['totDeb'] += (float)($l['debito'] ?? 0);
            $grupos[$k]['totCred'] += (float)($l['credito'] ?? 0);
            $grupos[$k]['totSaldo'] += (float)($l['saldo'] ?? 0);
        }
        // remove grupos vazios
        foreach ($order as $k) {
            if (empty($grupos[$k]['linhas'])) { unset($grupos[$k]); }
        }
        return $grupos;
    }

    // Calcula Demonstrativo de Resultado simples a partir dos grupos (3=DESPESAS, 4=RECEITAS)
    protected function computeDre(array $grupos): array
    {
        $totDespDeb = (float)($grupos['3']['totDeb'] ?? 0);
        $totDespCred = (float)($grupos['3']['totCred'] ?? 0);
        $totRecDeb = (float)($grupos['4']['totDeb'] ?? 0);
        $totRecCred = (float)($grupos['4']['totCred'] ?? 0);

        // Despesas: débito - crédito (normalmente positivo)
        $despesas = $totDespDeb - $totDespCred;
        if ($despesas < 0) { $despesas = 0.0; }
        // Receitas: crédito - débito
        $receitas = $totRecCred - $totRecDeb;
        if ($receitas < 0) { $receitas = 0.0; }

        $resultado = $receitas - $despesas; // se negativo => prejuízo
        $isPrejuizo = $resultado < 0;
        return [$receitas, $despesas, $resultado, $isPrejuizo];
    }


// (fim dos métodos de LancamentosController)
}
