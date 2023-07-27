<?php

namespace App\Http\Controllers;

use App\Helpers\SaldoLancamentoHelper;
use App\Http\Requests\CentroCustosCreateRequest;
use App\Http\Requests\ContasCentroCustosCreateRequest;
use App\Models\CentroCustos;
use App\Models\Conta;
use App\Models\ContasCentroCustos;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\PlanoConta;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
 use Dompdf\Dompdf;


class ContasCentroCustosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:CONTASCENTROCUSTOS - DASHBOARD'])->only('dashboard');
        $this->middleware(['permission:CONTASCENTROCUSTOS - LISTAR'])->only('index');
        $this->middleware(['permission:CONTASCENTROCUSTOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:CONTASCENTROCUSTOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:CONTASCENTROCUSTOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:CONTASCENTROCUSTOS - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */

     public function dashboard()
     {
         return view('ContasCentroCustos.dashboard');
     }

     public function index()
     {
        $ContasCentroCustos = ContasCentroCustos::OrderBy('ID','desc')->get();

        $UnicoContasCentroCustos = ContasCentroCustos::select('ID', 'CentroCustoID', 'ContaID')
    ->whereIn('ID', function ($query) {
        $query->selectRaw('MIN(ID)')
            ->from('Contabilidade.ContasCentroCustos')
            ->groupBy('CentroCustoID');
    })
    ->orderBy('ID','DESC')
    ->get();


         return view('ContasCentroCustos.index',compact('ContasCentroCustos', 'UnicoContasCentroCustos'));
     }

    public function CalculoContasCentroCustos(string $id)
    {
       $ContasCentroCustos = ContasCentroCustos::where('CentroCustoID' , '=', $id)->get();

    //    dd($ContasCentroCustos);


 $Resultado = array();
$ResultadoLoop = array();

       foreach($ContasCentroCustos as $TodasContas){

       $ContasCentroCustosID = $TodasContas->ID;
       $CentroCusto = $TodasContas->CentroCustoID;
       $ContaID = $TodasContas->ContaID;



       $De = Carbon::now()->format('d/m/Y');



       $EmpresaID = $TodasContas->MostraContaCentroCusto->EmpresaID;
       $NomeCentroCustos = $TodasContas->MostraCentroCusto?->Descricao;
       $NomeConta = $TodasContas->MostraContaCentroCusto->PlanoConta?->Descricao;
       $Empresa = $TodasContas->MostraContaCentroCusto->Empresa?->Descricao;

    //    $de = Carbon::createFromDate($De);
    $de = $De;
       $contaID = $ContaID;
       $totalCredito = Lancamento::where(function ($q) use ($de, $contaID,$EmpresaID) {
           return $q
               ->where('ContaCreditoID', $contaID)
               ->where('EmpresaID', $EmpresaID)
               ->where('DataContabilidade', '<', $de);
       })
           ->whereDoesntHave('SolicitacaoExclusao')
           ->sum('Lancamentos.Valor');

       $totalDebito = Lancamento::where(function ($q) use ($de, $contaID, $EmpresaID) {
           return $q
               ->where('ContaDebitoID', $contaID)
               ->where('EmpresaID', $EmpresaID)
               ->where('DataContabilidade', '<', $de);
       })
           ->whereDoesntHave('SolicitacaoExclusao')
           ->sum('Lancamentos.Valor');

       $saldoAnterior = $totalDebito - $totalCredito;



         $SaldoDia = SaldoLancamentoHelper::Dia($de, $contaID, $EmpresaID);

$SaldoAtual = $saldoAnterior + $SaldoDia;

/////////////////////// MONTA ARRAY
          $Resultado['NomeCentroCustos'] = $NomeCentroCustos;

          $Resultado['NomeConta'] = $NomeConta;


          $Resultado['Empresa'] = $Empresa;



          $Resultado['saldoAnterior'] = $saldoAnterior;


          $Resultado['totalDebito'] = $totalDebito;


          $Resultado['totalCredito'] = $totalCredito;


          $Resultado['SaldoDia'] = $SaldoDia;


          $Resultado['SaldoAtual'] = $SaldoAtual;


$ResultadoLoop[] = $Resultado;


    }

$Resultado = $ResultadoLoop;

$somaSaldoAnterior = 0;
$somaSaldoAtual = 0;
$somaSaldoDia = 0;


foreach ($ResultadoLoop as
$registro) {
    $somaSaldoAtual += $registro['SaldoAtual'];
    $somaSaldoAnterior += $registro['saldoAnterior'];
    $somaSaldoDia += $registro['SaldoDia'];
}



        return view('ContasCentroCustos.calculoscontascentrocustos',compact('Resultado','SaldoAtual', 'saldoAnterior', 'SaldoDia',
    'somaSaldoAtual', 'somaSaldoAnterior', 'somaSaldoDia'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $SeleCentroCusto = CentroCustos::orderby('Descricao')->get();

        $seleConta = Conta::
        join('Contabilidade.PlanoContas','PlanoContas.ID','=','Contas.Planocontas_id')
        ->join('Contabilidade.Empresas','Empresas.ID','=','Contas.EmpresaID')
        ->select('Contas.ID',DB::raw("CONCAT(PlanoContas.Descricao,' | ', Empresas.Descricao) as Descricao"))
        ->orderby('PlanoContas.Descricao')
        ->get();

        return view('ContasCentroCustos.create',compact('SeleCentroCusto','seleConta'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContasCentroCustosCreateRequest  $request)
    {

        $Existe = ContasCentroCustos::where('CentroCustoID','=', $request->CentroCustoID)
        ->where('ContaID', '=', $request->ContaID)
        ->first();


        if($Existe){
            session(['success' => ' Registro já inserido. '
            .$request->ContaID. ': '. $Existe->MostraContaCentroCusto->PlanoConta->Descricao
            . ' em '.$request->CentroCustoID.': '. $Existe->MostraCentroCusto->Descricao]
        );
            return redirect(route('ContasCentroCustos.create'));

        }



        $ContasCentroCustos = $request->all();


        // $ContasCentroCustos['Modified'] = Carbon::now()->format('d/m/Y H:i:s');
        $ContasCentroCustos['Created'] = Carbon::now()->format('d/m/Y H:i:s');
        $ContasCentroCustos['UsuarioID'] = auth()->user()->id;
        // $ContasCentroCustos['EmpresaID'] = 0;


        ContasCentroCustos::create($ContasCentroCustos);

        return redirect(route('ContasCentroCustos.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = ContasCentroCustos::find($id);
        return view('ContasCentroCustos.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ContasCentroCustos = ContasCentroCustos::find($id);

        $SeleCentroCusto = CentroCustos::orderby('Descricao')->get();


        // $seleConta = Conta::join('Contabilidade.PlanoContas', 'Contabilidade.Contas.Planocontas_id', '=', 'Contabilidade.PlanoContas.ID')
        //  ->select('Contabilidade.Contas.*', 'Contabilidade.PlanoContas.*')
        // ->get();


        $seleConta = Conta::
        join('Contabilidade.PlanoContas','PlanoContas.ID','=','Contas.Planocontas_id')
        ->join('Contabilidade.Empresas','Empresas.ID','=','Contas.EmpresaID')
        ->select('Contas.ID',DB::raw("CONCAT(PlanoContas.Descricao,' | ', Empresas.Descricao) as Descricao"))
        ->orderby('PlanoContas.Descricao')
        ->get();

        // $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
        // ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        // ->OrderBy('Descricao')
        // ->select(['Empresas.ID', 'Empresas.Descricao'])
        // ->get();





        return view('ContasCentroCustos.edit',compact('ContasCentroCustos','SeleCentroCusto','seleConta'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = ContasCentroCustos::find($id);

        $Existe = ContasCentroCustos::where('CentroCustoID','=', $request->CentroCustoID)
        ->where('ContaID', '=', $request->ContaID)
        ->first();


        if($Existe){
            session(['success' => ' Registro já inserido. '
            .$request->ContaID. ': '. $Existe->MostraContaCentroCusto->PlanoConta->Descricao
            . ' em '.$request->CentroCustoID.': '. $Existe->MostraCentroCusto->Descricao]
        );
            return redirect(route('ContasCentroCustos.index'));
        }

        $contaAnterior = $cadastro->ContaID;
        $cadastro->update(['ContaID' => $request->ContaID]);

        session(['success' => ' Registro alterado com sucesso: De '.$contaAnterior.' para '.$request->ContaID]);
        return redirect(route('ContasCentroCustos.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ContasCentroCustos = ContasCentroCustos::find($id);



        $contascentrocusto = ContasCentroCustos::where('CentroCustoID',$id)->first();


        if($contascentrocusto)
        {
            session(['error' => ' Registro sendo usado! Não posso excluir! ']);
            return redirect(route('ContasCentroCustos.index'));
        }

        $ContasCentroCustos->delete();

        session(['success2' => ' Registro excluído com sucesso ']);
        return redirect(route('ContasCentroCustos.index'));

    }

    public function gerarCalculoPdf(string $id)
    {

        $ContasCentroCustos = ContasCentroCustos::where('CentroCustoID' , '=', $id)->get();

        //    dd($ContasCentroCustos);


     $Resultado = array();
    $ResultadoLoop = array();

           foreach($ContasCentroCustos as $TodasContas){

           $ContasCentroCustosID = $TodasContas->ID;
           $CentroCusto = $TodasContas->CentroCustoID;
           $ContaID = $TodasContas->ContaID;



           $De = Carbon::now()->format('d/m/Y');



           $EmpresaID = $TodasContas->MostraContaCentroCusto->EmpresaID;
           $NomeCentroCustos = $TodasContas->MostraCentroCusto?->Descricao;
           $NomeConta = $TodasContas->MostraContaCentroCusto->PlanoConta?->Descricao;
           $Empresa = $TodasContas->MostraContaCentroCusto->Empresa?->Descricao;

        //    $de = Carbon::createFromDate($De);
        $de = $De;
           $contaID = $ContaID;
           $totalCredito = Lancamento::where(function ($q) use ($de, $contaID,$EmpresaID) {
               return $q
                   ->where('ContaCreditoID', $contaID)
                   ->where('EmpresaID', $EmpresaID)
                   ->where('DataContabilidade', '<', $de);
           })
               ->whereDoesntHave('SolicitacaoExclusao')
               ->sum('Lancamentos.Valor');

           $totalDebito = Lancamento::where(function ($q) use ($de, $contaID, $EmpresaID) {
               return $q
                   ->where('ContaDebitoID', $contaID)
                   ->where('EmpresaID', $EmpresaID)
                   ->where('DataContabilidade', '<', $de);
           })
               ->whereDoesntHave('SolicitacaoExclusao')
               ->sum('Lancamentos.Valor');

           $saldoAnterior = $totalDebito - $totalCredito;



             $SaldoDia = SaldoLancamentoHelper::Dia($de, $contaID, $EmpresaID);

    $SaldoAtual = $saldoAnterior + $SaldoDia;

    /////////////////////// MONTA ARRAY

              $Resultado['Data'] = $De;

              $Resultado['NomeCentroCustos'] = $NomeCentroCustos;

              $Resultado['NomeConta'] = $NomeConta;


              $Resultado['Empresa'] = $Empresa;



              $Resultado['saldoAnterior'] = $saldoAnterior;


              $Resultado['totalDebito'] = $totalDebito;


              $Resultado['totalCredito'] = $totalCredito;


              $Resultado['SaldoDia'] = $SaldoDia;


              $Resultado['SaldoAtual'] = $SaldoAtual;


    $ResultadoLoop[] = $Resultado;


        }

    $Resultado = $ResultadoLoop;

    $somaSaldoAnterior = 0;
    $somaSaldoAtual = 0;
    $somaSaldoDia = 0;


    foreach ($ResultadoLoop as
    $registro) {
        $somaSaldoAtual += $registro['SaldoAtual'];
        $somaSaldoAnterior += $registro['saldoAnterior'];
        $somaSaldoDia += $registro['SaldoDia'];
    }



        //     return view('ContasCentroCustos.calculoscontascentrocustos',compact('Resultado','SaldoAtual', 'saldoAnterior', 'SaldoDia',
        // 'somaSaldoAtual', 'somaSaldoAnterior', 'somaSaldoDia'));



// dd($Resultado,$SaldoAtual, $saldoAnterior, $SaldoDia, $somaSaldoAtual, $somaSaldoAnterior, $somaSaldoDia);


        // Construir a tabela HTML
        $htmlTable = '<style>
            @page {
                margin-top: 50px;
            }

            h1, h5 {
                text-align: center;
                margin: 10px 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }

            th {
                background-color: #f2f2f2;
            }

            .saldo-anterior {
                font-weight: bold;
            }

            .total {
                font-weight: bold;
            }

            .header {
                position: fixed;
                top: -40px;
                left: 0;
                right: 0;
                height: 40px;
                background-color: #f2f2f2;
                text-align: center;
                line-height: 40px;
            }
        </style>';




        // $htmlTable .= '<div class="header">
        // <h5>Período de: ' . $deformatada . ' à ' . $ateformatada .  '</h5>
        // <h5>Conta: ' . $descricaoconta . '</h5>
        // </div>';

        // $htmlTable .= '<h1>RELATÓRIO DE LANÇAMENTOS</h1>';
        // $htmlTable .= '<h5>Conta: ' . $descricaoconta . '</h5>';
        // $htmlTable .= '<h1>Período de: ' . $deformatada . ' à ' . $ateformatada .  '</h1>';

        $Nomecalculo =reset($Resultado)['NomeCentroCustos'];
        $htmlTable .= '

            <table>
                <thead>
                    <tr style="background-color: #eaf2ff;">
                            <th colspan="3" class="saldo-anterior"><h4>SALDOS NAS CONTAS ABAIXO</h4></td>
                            <th colspan="4" class="saldo-anterior"><h4> ' . $Nomecalculo . '</h4></td>
                    </tr>
                    <tr>
                        <th>Saldo anterior</th>
                        <th>Saldo dia</th>
                        <th>Atual</th>
                        <th>Conta</th>
                    </tr>
                    <tr>
                        <td colspan="4"><hr></td>
                    </tr>

                </thead>
                <tbody>';

        $debitoTotal = 0;
        $creditoTotal = 0;


        //     $htmlTable .= '<tr>
        //         <td>' . $data . '</td>
        //         <td>' . $descricaocompleta . '</td>
        //         <td style="text-align: right;">' . (($conta == $lancamento->ContaDebitoID) ? $valor : '') . '</td>
        //         <td style="text-align: right;">' . (($conta == $lancamento->ContaCreditoID) ? $valor : '') . '</td>
        //     </tr>';
        // }

        // "NomeCentroCustos" => "SOMATÓRIA DAS CONTAS GRUPO NET RUBI - DISPONÍVEL IMEDIATO"
        // "NomeConta" => "NET RUBI SERVICOS DE TECNOLOGIA LTDA CONTAS 11382- 9 - AGENCIA 0703- SICREDI"
        // "Empresa" => "NET RUBI SERVICOS DE TECNOLOGIA LTDA"
        // "saldoAnterior" => 1.0
        // "totalDebito" => "6401080.68"
        // "totalCredito" => "6401079.68"
        // "SaldoDia" => 8506.51
        // "SaldoAtual" => 85



        foreach($Resultado as $resultado)
        {

            if($resultado['SaldoAtual'] < 0){
            $htmlTable .= '<tr>
                <td style="text-align: right; color: red;">' . number_format($resultado['saldoAnterior'], 2, ',', '.')  .  '</td>
                <td style="text-align: right; color: red;">' . number_format($resultado['SaldoDia'], 2, ',', '.')  . '</td>
                <td  style="text-align: right; color: red;">' .number_format($resultado['SaldoAtual'], 2, ',', '.') . '</td>
                <td  style="text-align: left; color: red;">' . $resultado['NomeConta'] . ' - '. $resultado['Empresa'] . '</td>
               </tr>';
            }
            else
            {
                $htmlTable .= '<tr>
                <td style="text-align: right;">' . number_format($resultado['saldoAnterior'], 2, ',', '.')  .  '</td>
                <td style="text-align: right;">' . number_format($resultado['SaldoDia'], 2, ',', '.')  . '</td>
                <td  style="text-align: right;">' .number_format($resultado['SaldoAtual'], 2, ',', '.') . '</td>
                <td  style="text-align: left;">' . $resultado['NomeConta'] . ' - '. $resultado['Empresa'] . '</td>
               </tr>';

            }


                $Data = $resultado['Data'];
            }

        $htmlTable .= '<tr>
            <td colspan="0"><hr></td>
        </tr>';

        $htmlTable .= '<tr class="total">

            </tr>';


        $somaSaldoAnteriorFormatado  =  number_format($somaSaldoAnterior, 2, ',', '.');
        $somaSaldoDiaFormatado   =   number_format($somaSaldoDia, 2, ',', '.');
        $somaSaldoAtualFormatado = number_format($somaSaldoAtual, 2, ',', '.');

        if($somaSaldoAtual < 0){
            $htmlTable.='<tr>
            <td style="text-align: right; color: red;">' . ($somaSaldoAnteriorFormatado != 0 ? $somaSaldoAnteriorFormatado : '') . '</td>
            <td style="text-align: right; color: red;">' . ($somaSaldoDiaFormatado != 0 ? $somaSaldoDiaFormatado : '') . '</td>
            <td style="text-align: right; color: red;">' . ($somaSaldoAtualFormatado != 0 ? $somaSaldoAtualFormatado : '') . '</td>
            <td style="text-align: left; color: red;">' . 'SALDOS EM '. $Data . '</td>
           </tr>';
        }
        else{
        $htmlTable.='<tr>
         <td style="text-align: right; color: blue;">' . ($somaSaldoAnteriorFormatado != 0 ? $somaSaldoAnteriorFormatado : '') . '</td>
         <td style="text-align: right; color: blue;">' . ($somaSaldoDiaFormatado != 0 ? $somaSaldoDiaFormatado : '') . '</td>
         <td style="text-align: right; color: blue;">' . ($somaSaldoAtualFormatado != 0 ? $somaSaldoAtualFormatado : '') . '</td>
         <td style="text-align: left; color: red;">' . 'SALDOS DE ' . $De . ' A ' .  $Ate . '</td>
        </tr>';
 }




        $htmlTable .= '
            </tbody>
        </table>';




        // Configurar e gerar o PDF com o Dompdf
        $dompdf = new Dompdf();


        // Habilitar opção de cabeçalho
        $options = $dompdf->getOptions();
        $options->setIsHtml5ParserEnabled(true);
        $options->setIsRemoteEnabled(true);
        $options->setChroot(base_path());

        // Definir o cabeçalho
        $header = '<div style="text-align: center; color: green;">SALDO DISPONÍVEL IMEDIATO EM ' . $Data .  '</div>';
        // $header = '<div style="text-align: center;">
        // <h5>Período de: ' . $deformatada . ' à ' . $ateformatada .  '</h5>
        // <h5>Conta: ' . $descricaoconta . '</h5>
        // </div>';
        // $options->setPdfBackendOptions(['enable_html5_parser' => true, 'enable_remote' => true]);
        $dompdf->setOptions($options);
        $dompdf->setBasePath(base_path());
        // $dompdf->setHttpContext(new Dompdf\FrameDecorator($header));


 $html = $header . $htmlTable;

 $dompdf->loadHtml($html);
        $dompdf->render();

        // Salvar ou exibir o PDF
        $dompdf->stream('lancamentos.pdf', ['Attachment' => false]);

        // Obter o conteúdo do PDF
        // $output = $dompdf->output();

        // Exibir o PDF em uma nova página
        // return response($output)
        //     ->header('Content-Type', 'application/pdf')
        //     ->header('Content-Disposition', 'inline; filename="lancamentos.pdf"');
    }

    public function gerarCalculoPdfPeriodo(request $request)
    {



        $DataInicial = Carbon::createFromFormat('Y-m-d', $request->DataInicial);
        $DataFinal = Carbon::createFromFormat('Y-m-d', $request->DataFinal);

        if($DataInicial > $DataFinal)
        {
           session(['error' => ' Data inicial está maior que a data final ']);
           return redirect(route('ContasCentroCustos.index'));
        }
        


        $id = $request->idcusto;


//  dd($DataInicial, $DataFinal, $id);

        $ContasCentroCustos = ContasCentroCustos::where('CentroCustoID' , '=', $id)->get();

        //    dd($ContasCentroCustos);


     $Resultado = array();
    $ResultadoLoop = array();

           foreach($ContasCentroCustos as $TodasContas){

           $ContasCentroCustosID = $TodasContas->ID;
           $CentroCusto = $TodasContas->CentroCustoID;
           $ContaID = $TodasContas->ContaID;



        //    $De = Carbon::now()->format('d/m/Y');
           $DataInicialPesquisar =  $DataInicial->format('d/m/Y');
           $DataFinalPesquisar =  $DataFinal->format('d/m/Y');
         $De = $DataInicialPesquisar;
         $Ate = $DataFinalPesquisar;

           $EmpresaID = $TodasContas->MostraContaCentroCusto->EmpresaID;
           $NomeCentroCustos = $TodasContas->MostraCentroCusto?->Descricao;
           $NomeConta = $TodasContas->MostraContaCentroCusto->PlanoConta?->Descricao;
           $Empresa = $TodasContas->MostraContaCentroCusto->Empresa?->Descricao;

        //    $de = Carbon::createFromDate($De);
        // $de = $De;
           $contaID = $ContaID;
           $totalCredito = Lancamento::where(function ($q) use ($Ate, $contaID,$EmpresaID) {
               return $q
                   ->where('ContaCreditoID', $contaID)
                   ->where('EmpresaID', $EmpresaID)
                   ->where('DataContabilidade', '<', $Ate);

           })
               ->whereDoesntHave('SolicitacaoExclusao')
               ->sum('Lancamentos.Valor');

           $totalDebito = Lancamento::where(function ($q) use ($Ate, $contaID, $EmpresaID) {
               return $q
                   ->where('ContaDebitoID', $contaID)
                   ->where('EmpresaID', $EmpresaID)
                   ->where('DataContabilidade', '<', $Ate);


           })
               ->whereDoesntHave('SolicitacaoExclusao')
               ->sum('Lancamentos.Valor');

           $saldoAnterior = $totalDebito - $totalCredito;



             $SaldoDia = SaldoLancamentoHelper::Dia($Ate, $contaID, $EmpresaID);

    $SaldoAtual = $saldoAnterior + $SaldoDia;

    /////////////////////// MONTA ARRAY

              $Resultado['Data'] = $De;

              $Resultado['NomeCentroCustos'] = $NomeCentroCustos;

              $Resultado['NomeConta'] = $NomeConta;


              $Resultado['Empresa'] = $Empresa;



              $Resultado['saldoAnterior'] = $saldoAnterior;


              $Resultado['totalDebito'] = $totalDebito;


              $Resultado['totalCredito'] = $totalCredito;


              $Resultado['SaldoDia'] = $SaldoDia;


              $Resultado['SaldoAtual'] = $SaldoAtual;


    $ResultadoLoop[] = $Resultado;


        }

    $Resultado = $ResultadoLoop;

    $somaSaldoAnterior = 0;
    $somaSaldoAtual = 0;
    $somaSaldoDia = 0;


    foreach ($ResultadoLoop as
    $registro) {
        $somaSaldoAtual += $registro['SaldoAtual'];
        $somaSaldoAnterior += $registro['saldoAnterior'];
        $somaSaldoDia += $registro['SaldoDia'];
    }



        //     return view('ContasCentroCustos.calculoscontascentrocustos',compact('Resultado','SaldoAtual', 'saldoAnterior', 'SaldoDia',
        // 'somaSaldoAtual', 'somaSaldoAnterior', 'somaSaldoDia'));



// dd($Resultado,$SaldoAtual, $saldoAnterior, $SaldoDia, $somaSaldoAtual, $somaSaldoAnterior, $somaSaldoDia);


        // Construir a tabela HTML
        $htmlTable = '<style>
            @page {
                margin-top: 50px;
            }

            h1, h5 {
                text-align: center;
                margin: 10px 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }

            th {
                background-color: #f2f2f2;
            }

            .saldo-anterior {
                font-weight: bold;
            }

            .total {
                font-weight: bold;
            }

            .header {
                position: fixed;
                top: -40px;
                left: 0;
                right: 0;
                height: 40px;
                background-color: #f2f2f2;
                text-align: center;
                line-height: 40px;
            }
        </style>';






        $Nomecalculo =reset($Resultado)['NomeCentroCustos'];
        $htmlTable .= '

            <table>
                <thead>
                    <tr style="background-color: #eaf2ff;">
                            <th colspan="3" class="saldo-anterior"><h4>SALDOS NAS CONTAS ABAIXO</h4></td>
                            <th colspan="4" class="saldo-anterior"><h4> ' . $Nomecalculo . '</h4></td>
                    </tr>
                    <tr>
                        <th>Saldo anterior</th>
                        <th>Saldo dia</th>
                        <th>Atual</th>
                        <th>Conta</th>
                    </tr>
                    <tr>
                        <td colspan="4"><hr></td>
                    </tr>

                </thead>
                <tbody>';








        foreach($Resultado as $resultado)
        {

            if($resultado['SaldoAtual'] < 0){
            $htmlTable .= '<tr>
                <td style="text-align: right; color: red;">' . number_format($resultado['saldoAnterior'], 2, ',', '.')  .  '</td>
                <td style="text-align: right; color: red;">' . number_format($resultado['SaldoDia'], 2, ',', '.')  . '</td>
                <td  style="text-align: right; color: red;">' .number_format($resultado['SaldoAtual'], 2, ',', '.') . '</td>
                <td  style="text-align: left; color: red;">' . $resultado['NomeConta'] . ' - '. $resultado['Empresa']  .  '</td>
               </tr>';
            }
            else
            {
                $htmlTable .= '<tr>
                <td style="text-align: right;">' . number_format($resultado['saldoAnterior'], 2, ',', '.')  .  '</td>
                <td style="text-align: right;">' . number_format($resultado['SaldoDia'], 2, ',', '.')  . '</td>
                <td  style="text-align: right;">' .number_format($resultado['SaldoAtual'], 2, ',', '.') . '</td>
                <td  style="text-align: left;">' . $resultado['NomeConta'] . ' - '. $resultado['Empresa'] . '</td>
               </tr>';

            }


                $Data = $resultado['Data'];
            }

        $htmlTable .= '<tr>
            <td colspan="0"><hr></td>
        </tr>';

        $htmlTable .= '<tr class="total">

            </tr>';


        $somaSaldoAnteriorFormatado  =  number_format($somaSaldoAnterior, 2, ',', '.');
        $somaSaldoDiaFormatado   =   number_format($somaSaldoDia, 2, ',', '.');
        $somaSaldoAtualFormatado = number_format($somaSaldoAtual, 2, ',', '.');

        if($somaSaldoAtual < 0){
            $htmlTable.='<tr>
            <td style="text-align: right; color: red;">' . ($somaSaldoAnteriorFormatado != 0 ? $somaSaldoAnteriorFormatado : '') . '</td>
            <td style="text-align: right; color: red;">' . ($somaSaldoDiaFormatado != 0 ? $somaSaldoDiaFormatado : '') . '</td>
            <td style="text-align: right; color: red;">' . ($somaSaldoAtualFormatado != 0 ? $somaSaldoAtualFormatado : '') . '</td>
            <td style="text-align: left; color: red;">' . 'SALDOS DE '.  $De . ' A ' .  $Ate . '</td>
           </tr>';
        }
        else{
        $htmlTable.='<tr>
         <td style="text-align: right; color: blue;">' . ($somaSaldoAnteriorFormatado != 0 ? $somaSaldoAnteriorFormatado : '') . '</td>
         <td style="text-align: right; color: blue;">' . ($somaSaldoDiaFormatado != 0 ? $somaSaldoDiaFormatado : '') . '</td>
         <td style="text-align: right; color: blue;">' . ($somaSaldoAtualFormatado != 0 ? $somaSaldoAtualFormatado : '') . '</td>
         <td style="text-align: left; color: red;">' . 'SALDOS DE '.  $De . ' A ' .  $Ate .  '</td>
        </tr>';
        }




        $htmlTable .= '
            </tbody>
        </table>';




        // Configurar e gerar o PDF com o Dompdf
        $dompdf = new Dompdf();


        // Habilitar opção de cabeçalho
        $options = $dompdf->getOptions();
        $options->setIsHtml5ParserEnabled(true);
        $options->setIsRemoteEnabled(true);
        $options->setChroot(base_path());

        // Definir o cabeçalho
        $header = '<div style="text-align: center; color: green;">SALDO DISPONÍVEL IMEDIATO DE ' . $De . ' A ' .  $Ate .   '</div>';
        // $header = '<div style="text-align: center;">
        // <h5>Período de: ' . $deformatada . ' à ' . $ateformatada .  '</h5>
        // <h5>Conta: ' . $descricaoconta . '</h5>
        // </div>';
        // $options->setPdfBackendOptions(['enable_html5_parser' => true, 'enable_remote' => true]);
        $dompdf->setOptions($options);
        $dompdf->setBasePath(base_path());
        // $dompdf->setHttpContext(new Dompdf\FrameDecorator($header));


 $html = $header . $htmlTable;

 $dompdf->loadHtml($html);
        $dompdf->render();

        // Salvar ou exibir o PDF
        $dompdf->stream('lancamentos.pdf', ['Attachment' => false]);

        // Obter o conteúdo do PDF
        // $output = $dompdf->output();

        // Exibir o PDF em uma nova página
        // return response($output)
        //     ->header('Content-Type', 'application/pdf')
        //     ->header('Content-Disposition', 'inline; filename="lancamentos.pdf"');
    }

}



