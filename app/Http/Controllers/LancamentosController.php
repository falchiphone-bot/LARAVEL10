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
use App\Exports\LancamentoExport;
use App\Models\ContasPagar;
use App\Models\MoedasValores;
use App\Models\SolicitacaoExclusao;
use Exception;
use Illuminate\Support\Facades\Lang;
use LancamentoExport as GlobalLancamentoExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Cache;



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



    public function AtualizarSaldoPoupanca(Request $request)
    {

        $saldo = session('Saldo');
        $EmpresaID = session('EmpresaID');
        $dataCalcular = session('dataCalcular');
        $descricao = session('Descricao');
        $proximaData = session('ProximaData');
        $debito = session('Debito');
        $credito = session('Credito');
        $novaDescricao = session('NovaDescricao');
        $jurosArredondado = session('jurosArredondado');

        // dd( $EmpresaID);

        return view('Lancamentos.AtualizarPoupanca', compact('saldo', 'EmpresaID' ,'dataCalcular', 'descricao', 'proximaData', 'debito', 'credito', 'novaDescricao', 'jurosArredondado'));
    }


    public function AtualizarDadosPoupanca(Request $request)
    {

        // dd($request->all());
        $data = $request->proximaData;
        $descricao = (string) $request->novaDescricao;
        $empresaID = $request->EmpresaID;

        $debito = $request->debito;
        $credito = $request->credito;
        $valor = $request->jurosArredondado;

        $lancamento = new Lancamento();
        $lancamento->DataContabilidade = $data;
        $lancamento->Descricao = $descricao;
        $lancamento->ContaDebitoID = $debito;
        $lancamento->ContaCreditoID = $credito;
        $lancamento->Valor = $valor;
        $lancamento->EmpresaID = $empresaID;
        $lancamento->Usuarios_id = 70;

        // $lancamento->save();

        $ConsultaLancamento = lancamento::where('DataContabilidade', $data)->
        where('Descricao', $descricao)->
        where('ContaDebitoID', $debito)->
        where('ContaCreditoID', $credito)->
        where('Valor', $valor)->
        where('EmpresaID', $empresaID)->first();

        if ($ConsultaLancamento) {
            session(['error' => 'Lançamento já existente!']);
            return redirect()->route('planocontas.pesquisaavancada');
        }
        else {
            $lancamento->save();
            session(['success' => 'Lançamento efetuado com sucesso!']);
            // return redirect()->back();
            return redirect()->route('planocontas.pesquisaavancada');
        }

// dd($request->all(), $data, $descricao, $debito, $credito, $valor, $lancamento);

    }

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
        // Empresas disponíveis para o usuário (mesma lógica usada em outros pontos)
        $empresasLista = Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->id())
            ->orderBy('Empresas.Descricao')
            ->get(['Empresas.ID','Empresas.Descricao']);
        $erro = null;
        $oldRows = [];
        $selectedEmpresaId = null;
        if(!$forceRefresh && Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $headers = $cached['headers'] ?? [];
            $rows = $cached['rows'] ?? [];
            $selectedEmpresaId = $cached['selected_empresa_id'] ?? null;
        } elseif(!$exists){
            $erro = "Arquivo não encontrado em imports: $file. Copie ou faça upload.";
        } else {
            try {
                if($forceRefresh && Cache::has($cacheKey)) {
                    $oldRows = Cache::get($cacheKey)['rows'] ?? [];
                    $selectedEmpresaId = Cache::get($cacheKey)['selected_empresa_id'] ?? null;
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
                    // Campos de classificação (empresa/conta) default null até usuário selecionar (empresa única posteriormente)
                    $linha['_class_empresa_id'] = $selectedEmpresaId; // se já havia empresa selecionada global
                    // Não inicializamos _class_conta_id aqui para permitir restauração posterior; manter null se novo
                    $linha['_class_conta_id'] = $linha['_class_conta_id'] ?? null;
                    $rows[] = $linha;
                }
                // Mescla ajustes antigos se usuário havia modificado (_hist_ajustado diferente do original)
                if($oldRows){
                    foreach($rows as $i => &$linhaNova){
                        if(!isset($oldRows[$i])) continue;
                        $old = $oldRows[$i];
                        if(isset($old['_hist_ajustado'])){
                            $origColOld = $old['_hist_original_col'] ?? null;
                            $origValorOld = ($origColOld && isset($old[$origColOld])) ? $old[$origColOld] : null;
                            if($old['_hist_ajustado'] !== null && $old['_hist_ajustado'] !== $origValorOld){
                                // usuário alterou -> preservar
                                $linhaNova['_hist_ajustado'] = $old['_hist_ajustado'];
                            }
                        }
                        // Preserva classificação já feita
                        if(isset($old['_class_empresa_id'])){
                            $linhaNova['_class_empresa_id'] = $old['_class_empresa_id'];
                        }
                        if(isset($old['_class_conta_id'])){
                            $linhaNova['_class_conta_id'] = $old['_class_conta_id'];
                        }
                    }
                    unset($linhaNova);
                }
                Cache::put($cacheKey, [
                    'headers'=>$headers,
                    'rows'=>$rows,
                    'file'=>$file,
                    'generated_at'=>now()->toDateTimeString(),
                    'selected_empresa_id'=>$selectedEmpresaId,
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
        $rows = $payload['rows'] ?? [];
        foreach($rows as &$r){
            $r['_class_empresa_id'] = $empresaId;
            // Ao trocar empresa, reseta a conta para evitar inconsistência
            $r['_class_conta_id'] = null;
        }
        unset($r);
        $payload['rows'] = $rows;
        $payload['selected_empresa_id'] = $empresaId;
        Cache::put($cacheKey,$payload, now()->addHour());
        return response()->json(['ok'=>true]);
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



}
