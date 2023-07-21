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
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


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



         return view('ContasCentroCustos.index',compact('ContasCentroCustos'));
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
}



