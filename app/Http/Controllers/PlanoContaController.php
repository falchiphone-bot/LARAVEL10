<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SaldoLancamentoHelper;
use App\Http\Requests\PlanoContasCreateRequest;
use App\Models\AgrupamentosContas;
use App\Models\Conta;
use App\Models\Empresa;
use App\Models\EmpresaUsuario;
use App\Models\Lancamento;
use App\Models\PlanoConta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Strings;
use PHPUnit\Framework\Constraint\Count;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpParser\Node\Stmt\Else_;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\Support\Facades\Response;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Php;

class PlanoContaController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:PLANO DE CONTAS - LISTAR'])->only('index');
        $this->middleware(['permission:PLANO DE CONTAS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:PLANO DE CONTAS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:PLANO DE CONTAS - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:PESQUISA AVANCADA'])->only('pesquisaavancada');
    }
    /**public function __construct()
    {
        $this->middleware('auth');
    }*/

    /**
     * Display a listing of the resource.
     */
    public function pesquisaavancada()
    {
        $pesquisa = Lancamento::Limit(100)
            ->join('Contabilidade.EmpresasUsuarios', 'Lancamentos.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
            ->Where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->orderBy('Lancamentos.ID', 'desc')
            ->select(['Lancamentos.ID', 'DataContabilidade', 'Lancamentos.Descricao', 'Lancamentos.EmpresaID', 'Lancamentos.Valor', 'Historicos.Descricao as DescricaoHistorico', 'Lancamentos.ContaDebitoID', 'Lancamentos.ContaCreditoID'])
            ->get();
        // dd($pesquisa->first());?

        if ($pesquisa->count() > 0) {
            session(['entrada' => 'A pesquisa abaixo mostra os 100 últimos lançamentos de todas as empresas autorizadas!']);
            session(['success' => '']);
            session(['error' => '']);
        } else {
            session(['error' => 'Nenhum lançamento encontrado para as empresas autorizadas!']);
        }

        $retorno['DataInicial'] = date('Y-m-d');
        $retorno['DataFinal'] = date('Y-m-d');
        $retorno['EmpresaSelecionada'] = null;

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        return view('PlanoContas.pesquisaavancada', compact('pesquisa', 'retorno', 'Empresas'));
    }

    public function pesquisaavancadapost(Request $Request)
    {
        $CompararDataInicial = $Request->DataInicial;

        $pesquisa = Lancamento::Limit($Request->Limite ?? 100)
            ->join('Contabilidade.EmpresasUsuarios', 'Lancamentos.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
            ->Where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->select(['Lancamentos.ID', 'DataContabilidade', 'Lancamentos.Descricao', 'Lancamentos.EmpresaID', 'Contabilidade.Lancamentos.Valor', 'Historicos.Descricao as DescricaoHistorico', 'Lancamentos.ContaDebitoID', 'Lancamentos.ContaCreditoID'])
            ->orderBy('Lancamentos.ID', 'desc');

        if ($Request->Texto) {
            $texto = $Request->Texto;
            $pesquisa->where(function ($query) use ($texto) {
                return $query->where('Lancamentos.Descricao', 'like', '%' . $texto . '%')->orWhere('Historicos.Descricao', 'like', '%' . $texto . '%');
            });
        }

        if ($Request->Valor) {
            $pesquisa->where('Lancamentos.Valor', '==', $Request->Valor);
        }

        if ($Request->DataInicial) {
            $DataInicial = Carbon::createFromFormat('Y-m-d', $Request->DataInicial);
            $pesquisa->where('DataContabilidade', '>=', $DataInicial->format('d/m/Y'));
        }

        if ($Request->DataFinal) {
            $DataFinal = Carbon::createFromFormat('Y-m-d', $Request->DataFinal);
            $pesquisa->where('DataContabilidade', '<=', $DataFinal->format('d/m/Y'));
        }

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        $retorno = $Request->all();

        if ($pesquisa->count() > 0) {
            session(['success' => 'A pesquisa abaixo mostra os lançamentos de todas as empresas autorizadas conforme a pesquisa proposta!']);
        } else {
            session(['error' => 'Nenhum lançamento encontrado para as empresas autorizadas!']);
        }

        if ($Request->DataInicial && $Request->DataFinal) {
            if ($DataInicial > $DataFinal) {
                session(['error' => 'Data de início MAIOR que a final. VERIFIQUE!']);
                return view('PlanoContas.pesquisaavancada', compact('pesquisa', 'retorno', 'Empresas'));
            }
        }

        if ($Request->EmpresaSelecionada) {
            $pesquisa->where('Lancamentos.EmpresaID', $Request->EmpresaSelecionada);
        }

        $pesquisa = $pesquisa->get();

        // dd($pesquisa->first()->ContaDebito->PlanoConta);
        return view('PlanoContas.pesquisaavancada', compact('pesquisa', 'retorno', 'Empresas'));
    }

    public function Balancetes()
    {


        $retorno['EmpresaSelecionada'] = 5;

        $retorno['DataInicial'] = date('Y-m-d');
        $retorno['DataFinal'] =     date('Y-m-d');

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
        ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
        ->OrderBy('Descricao')
        ->select(['Empresas.ID', 'Empresas.Descricao'])
        ->get();

        return view('PlanoContas.Balancetes', compact('retorno', 'Empresas'));
    }



    public function BalanceteEmpresa(request $request)
    {

            $pdfgerar = $request->pdfgerar;

            $tela = $request->tela;

            $Agrupar = $request->Agrupar;
            $Selecao = $request->Selecao;
            $Agrupamentovazio = $request->Agrupamentovazio;
            $MostrarValorRecebido = $request->MostrarValorRecebido;

            if($tela){
                $pdfgerar = null;
            }
            else
            if(!$pdfgerar){

                return redirect('/PlanoContas/Balancetes')->with('error', 'Selecionar > Gerar e agrupar por descrição ou  Gerar por agrupar por agrupamento - L176', 'retorno', 'Empresas');

            }
            // dd('PAREI AQUI - 179');



            // $pdfdownload =  $request->pdfdownload;
            // $pdfvisualizar = $request->pdfvisualizar;
            $EmpresaID = $request->EmpresaSelecionada;
            $Ativo = $request->Ativo;
            $Passivo = $request->Passivo;
            $Despesas = $request->Despesas;
            $Receitas = $request->Receitas;
            $somaSaldoAtualDespesas = 0;
            $somaSaldoAtualPassivo = 0;
            $somaSaldoAtualAtivo = 0;
            $somaSaldoAtualReceitas = 0;
            $ResultadoReceitasDespesas = 0;
            $totalDebitoAtivo = 0;
            $totalDebitoPassivo = 0;
            $SaldoAtualPassivo = 0;
            $SaldoAtualAtivo = 0;
               //////////////  converter em data e depois em string data
            $DataInicialCarbon = Carbon::parse($request->input('DataInicial')) ;
            $DataFinalCarbon = Carbon::parse($request->input('DataFinal'));
            $DataInicial = $DataInicialCarbon->format('d/m/Y');
            $DataFinal = $DataFinalCarbon->format('d/m/Y');
            $retorno['EmpresaSelecionada'] = $EmpresaID;
                $retorno['DataInicial'] = $DataInicialCarbon->format('Y-m-d');
                $retorno['DataFinal'] = $DataFinalCarbon->format('Y-m-d');


            // dd($Ativo, $Passivo, $Despesas, $Receitas, $request->all() );
            $empresa = Empresa::find($EmpresaID);
            if ($empresa) {
                session(['Empresa' => $empresa]);

                // return redirect('/PlanoContas/dashboard');
            }else {
                return redirect(route('Empresas.index'))->with('error','Emprese não localizada');
            }




            if($DataInicialCarbon > $DataFinalCarbon )
            {
                session(['error' => 'Data inicial maior que a data final']);
                // return redirect(route('planocontas.balancetes'));



                 $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
                ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
                ->OrderBy('Descricao')
                ->select(['Empresas.ID', 'Empresas.Descricao'])
                ->get();

                return view('PlanoContas.Balancetes', compact('retorno', 'Empresas'));
            }




            if (!session('Empresa')) {
                return redirect('/Empresas')->with('error', 'Necessário selecionar uma empresa');
            } else {

                $C172 = 172; ///  LIQUIDACAO DE COBRANCA INFRANET

                $C19104 = 19104; //// LIQUIDACAO DE COBRANCA NET RUBI SERVICOS
                $C19268 = 19268; //// RECEBIMENTO DA CIELO NET RUBI SERVICOS

                $C5920 = 5920;//// LIQUIDACAO DE COBRANCA DA PRF PROVEDOR DE INTERNET LTDA.
                $C19497 = 19497;//// LIQUIDACAO DE COBRANCA DE TANABI - PARTE NET RUBI
                $C19495 = 19495;//// LIQUIDACAO DE COBRANCA DE AMERICO DE CAMPOS - PARTE NET RUBI
                $C129 = 129; //// LIQUIDACAO DE COBRANCA DA FIBRA INTERNET
                $C97 = 97;//// LIQUIDACAO DE COBRANCA DA STTARMAAKE INTERNET LTDA
                $C878 = 878;//// LIQUIDACAO DE COBRANCA DA STTARMAAKE INTERNET LTDA DE AMERICO DE CAMPOS - PARTE NET RUBI


                $soma5 = Lancamento::
                        where('EmpresaID', "=", 5)
                        ->where(function($query) use ($C172) {
                        $query->where('ContaCreditoID', "=", $C172);
                        })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal)
                        ->sum('Valor');

                $soma1021 = Lancamento::
                        where('EmpresaID', "=", 1021)
                        ->where(function($query) use ($C5920) {
                        $query->where('ContaCreditoID', "=", $C5920);
                        })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal)
                        ->sum('Valor');

                 $soma1021TANABI = Lancamento::
                        where('EmpresaID', "=", 1021)
                        ->where(function($query) use ($C19497) {
                        $query->where('ContaCreditoID', "=", $C19497);
                        })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal)
                        ->sum('Valor');

                $soma1021AMERICOCAMPOS = Lancamento::
                        where('EmpresaID', "=", 1021)
                        ->where(function($query) use ($C19495) {
                        $query->where('ContaCreditoID', "=", $C19495);
                        })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal)
                        ->sum('Valor');

                $soma1027 = Lancamento::
                        where('EmpresaID', "=", 1027)
                        ->where(function($query) use ($C19104) {
                        $query->where('ContaCreditoID', "=", $C19104);
                        })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal)
                        ->sum('Valor');

                $soma1027CIELO = Lancamento::
                        where('EmpresaID', "=", 1027)
                        ->where(function($query) use ($C19268) {
                        $query->where('ContaCreditoID', "=", $C19268);
                        })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal)
                        ->sum('Valor');

                $soma4 = Lancamento::
                        where('EmpresaID', "=", 4)
                        ->where(function($query) use ($C129) {
                        $query->where('ContaCreditoID', "=", $C129);
                        })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal)
                        ->sum('Valor');

                $soma3 = Lancamento::
                        where('EmpresaID', "=", 3)
                        ->where(function($query) use ($C97, $C878) {
                        $query->where('ContaCreditoID', "=", $C97);
                        $query->OrWhere('ContaCreditoID', "=", $C878);
                        })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal)
                        ->sum('Valor');

                $ValorRecebido = $soma5 + $soma1027 + $soma1021 + $soma4 + $soma3 + $soma1021TANABI + $soma1021AMERICOCAMPOS + $soma1027CIELO;



    if ($MostrarValorRecebido) {
        echo " Total Recebido Geral : "   . number_format(abs($ValorRecebido), 2, ',', '.') . "<br>". "<br>";
        echo "INFRANET              : " . number_format(abs($soma5), 2, ',', '.')     . "<br>". "<br>";




        echo "FIBRA NET RUBI        : " . number_format(abs($soma4), 2, ',', '.')     . "<br>". "<br>";

        echo "STTARMAAKE            : " . number_format(abs($soma3), 2, ',', '.')     . "<br>". "<br>";


        echo "PRF TANABI            : " . number_format(abs($soma1021TANABI), 2, ',', '.')     . "<br>";
        echo "PRF AMERICO DE CAMPO  : " . number_format(abs($soma1021AMERICOCAMPOS), 2, ',', '.')     . "<br>";
        echo "PRF                   : " . number_format(abs($soma1021), 2, ',', '.')     . "<br>" . "<br>";


        echo "NET RUBI SERVICOS     :  " . number_format(abs($soma1027), 2, ',', '.')     . "<br>";
        echo "NET RUBI SERVICO CIELO: " . number_format(abs($soma1027CIELO), 2, ',', '.')     . "<br>". "<br>";

        dd();
    }
                // $ValorRecebido = 1752890.08;

                // $EmpresasID = [5,1027,3,4,1021];

            // $contasEmpresa = Conta::whereIn('EmpresaID', $EmpresasID)
            // ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', '=', 'Contas.planocontas_id')
            // ->join('Contabilidade.Agrupamentos', 'PlanoContas.Agrupamento', '=', 'Agrupamentos.id')
            // ->orderBy('Codigo', 'asc')
            // ->where('Grau', '=', '5');

            if($EmpresaID == 5)
            {
                $EmpresasID = [5,11,1027,3,4,1021];
            }
            else
            {
                $EmpresasID = $request->EmpresaSelecionada;
                dd($EmpresasID);
            }



            $contasEmpresa = Conta::whereIn('EmpresaID', $EmpresasID)
            ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', '=', 'Contas.planocontas_id');

            if($Selecao != "Todas"){
                $contasEmpresa->join('Contabilidade.Agrupamentos', 'PlanoContas.Agrupamento', '=', 'Agrupamentos.id');
            }

            $contasEmpresa->orderBy('Codigo', 'asc')
            ->where('Grau', '=', '5');



            if ($Selecao == "Nulos") {
                $contasEmpresa->where(function ($query) {
                    $query->where('Agrupamento', '=', '0')
                    ->orWhereNull('Agrupamento');
                });

            } else
            if ($Selecao == "Agrupados") {
                $contasEmpresa->where('Agrupamento', '>', 0);
            }
            if ($Selecao == "Todas") {
                $contasEmpresa = $contasEmpresa;

            }

            $contasEmpresa->where(function ($query) use ($Ativo, $Passivo, $Despesas, $Receitas) {
                if ($Ativo) {
                    $query->whereRaw("SUBSTRING(PlanoContas.Codigo, 1, 1) = '1'");
                }
                if ($Passivo) {
                    $query->orWhereRaw("SUBSTRING(PlanoContas.Codigo, 1, 1) = '2'");
                }
                if ($Despesas) {
                    $query->orWhereRaw("SUBSTRING(PlanoContas.Codigo, 1, 1) = '3'");
                }
                if ($Receitas) {
                    $query->orWhereRaw("SUBSTRING(PlanoContas.Codigo, 1, 1) = '4'");
                }
            });
            if($Agrupar == 'Descricao' && $Selecao == "Agrupados" || $Selecao == "Todas" )
            {
                $contasEmpresa->select(['Contas.ID', 'Descricao', 'Codigo', 'Grau', 'Agrupamento']);

            }
            else
            if($Agrupar == 'Agrupamento' && $Selecao == "Agrupados")
            {
                $contasEmpresa->select(['Contas.ID', 'Descricao', 'Codigo', 'Grau', 'Agrupamento', 'Agrupamentos.nome']);

            }


                $contasEmpresa = $contasEmpresa->get();




                $Resultado = [];
                $ResultadoLoop = [];

            foreach ($contasEmpresa as $contasEmpresa5) {
                $contaID = $contasEmpresa5->ID;

                $Agrupamento = $contasEmpresa5->Agrupamento;
                $NomeAgrupamento = $contasEmpresa5->nome;



                $totalCredito = Lancamento::where(function ($q) use ($DataInicial, $DataFinal, $contaID, $EmpresasID) {
                    return $q
                        ->where('ContaCreditoID', $contaID)
                        ->whereIn('EmpresaID', $EmpresasID)
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal);
                })
                    ->whereDoesntHave('SolicitacaoExclusao')
                    ->sum('Lancamentos.Valor');


                $totalDebito = Lancamento::where(function ($q) use ($DataInicial, $DataFinal, $contaID, $EmpresasID) {
                    return $q
                        ->where('ContaDebitoID', $contaID)
                        ->whereIn('EmpresaID', $EmpresasID)
                        ->where('DataContabilidade', '>=', $DataInicial)
                        ->where('DataContabilidade', '<=', $DataFinal);
                })
                    ->whereDoesntHave('SolicitacaoExclusao')
                    ->sum('Lancamentos.Valor');

                $saldoAnterior = $totalDebito - $totalCredito;

                $SaldoDia = SaldoLancamentoHelper::Dia($DataFinal, $contaID, $EmpresaID);

                $SaldoAtual = $saldoAnterior + $SaldoDia;

                if($Passivo  ){
                    $totalDebitoPassivo = Lancamento::where(function ($q) use ($DataInicial, $DataFinal, $contaID, $EmpresasID) {
                        return $q
                            ->where('ContaDebitoID', $contaID)
                            ->whereIn('EmpresaID', $EmpresasID)
                            ->where('DataContabilidade', '>=', $DataInicial)
                            ->where('DataContabilidade', '<=', $DataFinal);
                    })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->sum('Lancamentos.Valor');
                }

                if($Ativo){
                    $totalDebitoAtivo = Lancamento::where(function ($q) use ($DataInicial, $DataFinal, $contaID, $EmpresasID) {
                        return $q
                            ->where('ContaDebitoID', $contaID)
                            ->whereIn('EmpresaID', $EmpresasID)
                            ->where('DataContabilidade', '>=', $DataInicial)
                            ->where('DataContabilidade', '<=', $DataFinal);
                    })
                        ->whereDoesntHave('SolicitacaoExclusao')
                        ->sum('Lancamentos.Valor');
                }




                /////////////////////// MONTA ARRAY
                if($Agrupamento)
                {
                    $Resultado['Agrupamento'] = $Agrupamento;

                    $Resultado['NomeAgrupamento'] = $NomeAgrupamento;
                }
                else
                {
                    $Resultado['Agrupamento'] = null;
                    $Resultado['NomeAgrupamento'] = null;
                }

                $Resultado['ID'] = $contasEmpresa5->ID;

                $Resultado['Descricao'] = $contasEmpresa5->Descricao;

                $Resultado['Codigo'] = $contasEmpresa5->Codigo;

                $Resultado['Grau'] = $contasEmpresa5->Grau;

                $Resultado['saldoAnterior'] = $saldoAnterior;

                $Resultado['totalDebito'] = $totalDebito;

                $Resultado['totalCredito'] = $totalCredito;

                $Resultado['SaldoDia'] = $SaldoDia;


                $Resultado['SaldoAtualPassivo'] = $totalDebitoPassivo;

                $Resultado['SaldoAtualAtivo'] = $totalDebitoAtivo;

                $Resultado['SaldoAtual'] = $SaldoAtual;

                $Resultado['ValorRecebido'] = $ValorRecebido;

                $Resultado['PercentualValorRecebido'] = ($SaldoAtual/$ValorRecebido)*100;

                $ResultadoLoop[] = $Resultado;
                // selecionar se já existe. Se existir acumular.;

                // dd($contasEmpresa5,$Resultado, $Ativo, $Passivo, $Despesas, $Receitas);
            }





            $somaSaldoAnterior = 0;
            $somaSaldoAtual = 0;
            $somaSaldoDia = 0;

            foreach ($ResultadoLoop as $registro) {
                $somaSaldoAtual += $registro['SaldoAtual'];
                $somaSaldoAnterior += $registro['saldoAnterior'];
                $somaSaldoDia += $registro['SaldoDia'];
            }
        }


        $contasEmpresa = $ResultadoLoop;

// dd( $contasEmpresa);



/////////////// filtra somente o valor maior que 0
        $registros = $contasEmpresa;

        $registrosValoresTodos = array_filter($registros, function ($registro) {
            return isset($registro['SaldoAtual']) && $registro['SaldoAtual'] !== 0;
        });
////////////////////////////// /////////////// /////////////// /////////////// ///////////////

/////////////// filtra somente as contas do ativo = 1.X.XX.XX

if($Ativo) {
    $registros = $contasEmpresa;
            $registrosValores = array_filter($registros, function ($registro) {
                return isset($registro['SaldoAtual']) && $registro['SaldoAtual'] !== 0 && substr($registro['Codigo'], 0, 1) === '1';
            });

            $somaSaldoAtualAtivo = 0;
            $SaldoAtualAtivo = 0;
            foreach ($registrosValores  as $registro) {
                $somaSaldoAtualAtivo += $registro['SaldoAtual'];
                $SaldoAtualAtivo += $registro['SaldoAtualAtivo'];
            ////////////////////////////// /////////////// /////////////// /////////////// ///////////////
            }
}
////////////////////////////// /////////////// /////////////// /////////////// ///////////////
if($Passivo) {
            /////////////// filtra somente as contas do passivo= 2.X.XX.XX
            $registros = $contasEmpresa;

            $registrosValores = array_filter($registros, function ($registro) {
                return isset($registro['SaldoAtual']) && $registro['SaldoAtual'] !== 0 && substr($registro['Codigo'], 0, 1) === '2';
            });

            $somaSaldoAtualPassivo = 0;
            $SaldoAtualPassivo = 0;
            foreach ($registrosValores  as $registro) {
                $somaSaldoAtualPassivo += $registro['SaldoAtual'];
                $SaldoAtualPassivo += $registro['SaldoAtualPassivo'];
            }
            ////////////////////////////// /////////////// /////////////// /////////////// ///////////////
}

if($Despesas){
           /////////////// filtra somente as contas do despesas= 3.X.XX.XX
           $registros = $contasEmpresa;

           $registrosValores = array_filter($registros, function ($registro) {
               return isset($registro['SaldoAtual']) && $registro['SaldoAtual'] !== 0 && substr($registro['Codigo'], 0, 1) === '3';
           });

           $somaSaldoAtualDespesas = 0;
           foreach ($registrosValores  as $registro) {
               $somaSaldoAtualDespesas += $registro['SaldoAtual'];
           }
            ////////////////////////////// /////////////// /////////////// /////////////// ///////////////
}
if($Receitas){
            /////////////// filtra somente as contas do receitas = 4.X.XX.XX
            $registros = $contasEmpresa;

            $registrosValores = array_filter($registros, function ($registro) {
                return isset($registro['SaldoAtual']) && $registro['SaldoAtual'] !== 0 && substr($registro['Codigo'], 0, 1) === '4';
            });

                    $somaSaldoAtualReceitas = 0;
                    foreach ($registrosValores  as $registro) {
                        $somaSaldoAtualReceitas += $registro['SaldoAtual'];
            }

        ////////////////////////////// /////////////// /////////////// /////////////// ///////////////

}
                            // dd($somaSaldoAtualAtivo, $somaSaldoAtualReceitas, $registro, $ResultadoLoop);
                        $dados  = $registrosValoresTodos;


// Inicialize um array para armazenar os registros agrupados por 'Descricao'
$registrosAgrupados = [];

if($Agrupar == 'Descricao')
{
    // Percorra o array original
    foreach ($dados as $registro) {
        $descricao = $registro["Descricao"];
        // Verifique se a descrição já existe no array de registros agrupados
        if (array_key_exists($descricao, $registrosAgrupados)) {
            // Se existir, some os campos relevantes
            $registrosAgrupados[$descricao]["SaldoAtual"] += floatval($registro["SaldoAtual"]);
            $registrosAgrupados[$descricao]["SaldoAtualPassivo"] += floatval($registro["SaldoAtualPassivo"]);
            $registrosAgrupados[$descricao]["ValorRecebido"] += floatval($registro["ValorRecebido"]);
            $registrosAgrupados[$descricao]["PercentualValorRecebido"] = ( $registrosAgrupados[$descricao]["SaldoAtual"]/$ValorRecebido)*100;
            // Adicione qualquer outro campo que você queira somar ou manipular aqui
        } else {
            // Se não existir, crie um novo registro no array de registros agrupados
            $registrosAgrupados[$descricao] = $registro;
        }
    }
    // dd('Descricao',$registrosAgrupados[$descricao]);
}
elseif($Agrupar == 'Agrupamento')
{
    // Percorra o array original

    foreach ($dados as $registro) {
        $nomeagrupamento = $registro["NomeAgrupamento"];

        // Verifique se a descrição já existe no array de registros agrupados
        if (array_key_exists($nomeagrupamento, $registrosAgrupados)) {
            // Se existir, some os campos relevantes
            $registrosAgrupados[$nomeagrupamento]["SaldoAtual"] += floatval($registro["SaldoAtual"]);
            $registrosAgrupados[$nomeagrupamento]["SaldoAtualAtivo"] += floatval($registro["SaldoAtualAtivo"]);
            $registrosAgrupados[$nomeagrupamento]["SaldoAtualPassivo"] += floatval($registro["SaldoAtualPassivo"]);
            $registrosAgrupados[$nomeagrupamento]["ValorRecebido"] += floatval($registro["ValorRecebido"]);
            $registrosAgrupados[$nomeagrupamento]["PercentualValorRecebido"] = ( $registrosAgrupados[$nomeagrupamento]["SaldoAtual"]/$ValorRecebido)*100;
            // Adicione qualquer outro campo que você queira somar ou manipular aqui
        } else {
            // Se não existir, crie um novo registro no array de registros agrupados
            $registrosAgrupados[$nomeagrupamento] = $registro;

        }
    }
//   dd('Agrupamento', $registrosAgrupados);
}



$somaPercentual = 0;
foreach($registrosAgrupados as $soma)
{
    $Valor = $soma["PercentualValorRecebido"];
    $somaPercentual +=  $Valor;

}




uasort($registrosAgrupados, function($a, $b) {
    $saldoA = floatval($a['SaldoAtual']);
    $saldoB = floatval($b['SaldoAtual']);

    if ($saldoA > $saldoB) {
        return -1;
    } elseif ($saldoA < $saldoB) {
        return 1;
    } else {
        return 0;
    }
});

if ($Agrupamentovazio == 'Agrupadosvazio') {
    // Itera pelos registros em $registrosAgrupados
    foreach ($registrosAgrupados as $indice => $registro) {
        // Verifica se o campo 'Agrupamento' é diferente de nulo
        if ($registro['Agrupamento'] !== null) {
            // Remove o registro do array $registrosAgrupados
            unset($registrosAgrupados[$indice]);
        }
    }
}



$contasEmpresa = $registrosAgrupados;

// dd($contasEmpresa);




if($Despesas && $Receitas)
{
                //////// resultado entre RECEITAS e DESPESAS
                $ResultadoReceitasDespesas = abs($somaSaldoAtualReceitas) - abs($somaSaldoAtualDespesas);
}


$pdf1 = null;

        if ($pdfgerar) {

            if($pdf1)
{
  $view = view('PlanoContas.BalanceteEmpresaphp', compact(
                'retorno',
                "ValorRecebido",
                'somaSaldoAtual',
                'SaldoAtualPassivo',
                'SaldoAtualAtivo',
                'contasEmpresa',
                'somaSaldoAtualAtivo',
                'somaSaldoAtualReceitas',
                'somaSaldoAtualDespesas',
                'somaSaldoAtualAtivo',
                'somaSaldoAtualPassivo',
                'ResultadoReceitasDespesas',
                'somaPercentual',
                'Agrupar',
                'Ativo',
                'Passivo',
                'Selecao',
                'Agrupamentovazio'
            ))->render();

}



            $view = view('PlanoContas.BalanceteEmpresapdfpaginado', compact(
                'retorno',
                "ValorRecebido",
                'somaSaldoAtual',
                'SaldoAtualPassivo',
                'SaldoAtualAtivo',
                'contasEmpresa',
                'somaSaldoAtualAtivo',
                'somaSaldoAtualReceitas',
                'somaSaldoAtualDespesas',
                'somaSaldoAtualAtivo',
                'somaSaldoAtualPassivo',
                'ResultadoReceitasDespesas',
                'somaPercentual',
                'Agrupar',
                'Ativo',
                'Passivo',
                'Selecao',
                'Agrupamentovazio'
            ))->render();


IF($pdf1)
{
    ob_start();
            $suaView = $view;
            // Imprima o conteúdo HTML
            echo $suaView;

            $conteudoHTML = ob_get_clean();

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $pdf = new Dompdf($options);

            $suaView = $conteudoHTML;

            $pdf->loadHtml($suaView);

            $pdf->render();

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Verifique se o campo "pdfgerar" está definido na solicitação POST
                if (isset($_POST["pdfgerar"])) {
                    // Acesse o valor selecionado com base no atributo "name"
                    $pdfgerado = $_POST["pdfgerar"];

                    if ($pdfgerado === "pdfdownload") {
                        // Ação para o radio button com "value" igual a "pdfdownload"
                        // Faça o que for necessário aqui
                        $pdf->stream('pdf_de_balancete.pdf', array("Attachment" => true));
                    } elseif ($pdfgerado === "pdfvisualizar") {
                        // Ação para o radio button com "value" igual a "pdfvisualizar"
                        // Faça o que for necessário aqui
                        $pdf->stream('pdf_de_balancete.pdf', array("Attachment" => false));
                    }
                }
            }



        }

        ob_start();
        $suaView = $view;
        // Imprima o conteúdo HTML
        echo $suaView;

  // dd('parado');

        $conteudoHTML = ob_get_clean();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $pdf = new Dompdf($options);

        $suaView = $conteudoHTML;

        $pdf->loadHtml($suaView);


        $pdf->setPaper('A4', 'portrait'); // Tamanho do papel e orientação
$pdf->render();

// Adicione números de página ao PDF
$canvas = $pdf->getCanvas();
$canvas->page_text(270, 770, "Página {PAGE_NUM} de {PAGE_COUNT}", 0 ,12);

        $pdf->stream('Balancete.pdf', array("Attachment" => false));






            // return redirect()->route('planocontas.Balancetesgerarpdf')->with('html', $view);
        } else {
            return view('PlanoContas.BalanceteEmpresa', compact(
                'retorno',
                "ValorRecebido",
                'somaSaldoAtual',
                'SaldoAtualPassivo',
                'SaldoAtualAtivo',
                'contasEmpresa',
                'somaSaldoAtualAtivo',
                'somaSaldoAtualReceitas',
                'somaSaldoAtualDespesas',
                'somaSaldoAtualAtivo',
                'somaSaldoAtualPassivo',
                'ResultadoReceitasDespesas',
                'somaPercentual',
                'Agrupar',
                'Selecao',
                'Ativo',
                'Passivo',
                'Agrupamentovazio'
            ));
        }


    }

    public function dashboard()
    {
        if (!session('Empresa')) {
            return redirect('/Empresas')->with('error', 'Necessário selecionar uma empresa');
        } else {
            $contasEmpresa = Conta::where('EmpresaID', session('Empresa')->ID)
                ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', '=', 'Contas.planocontas_id')
                ->orderBy('Codigo', 'asc')
                ->get(['Contas.ID', 'Descricao', 'Codigo', 'Grau']);
            // dd($contasEmpresa->first());

            return view('PlanoContas.dashboard', compact('contasEmpresa'));
        }
    }

    public function index()
    {
        $cadastros = PlanoConta::orderBy('codigo', 'asc')->get();
        //$cadastros = DB::table('PlanoConta')->get();        $num_rows = count($cadastros);

        $linhas = count($cadastros);


        return view('PlanoContas.index', compact('cadastros', 'linhas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('PlanoContas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlanoContasCreateRequest $request)
    {
        $dados = $request->all();

        $dados['Created'] = Carbon::now()->format('d/m/Y H:i:s');
        $dados['Modified'] = Carbon::now()->format('d/m/Y H:i:s');
        $dados['UsuarioID'] = auth()->user()->id;

        //dd($dados);

        PlanoConta::create($dados);
        return redirect(route('PlanoContas.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Empresa::find($id);

        return view('PlanoContas.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = PlanoConta::find($id);
        // dd($cadastro);

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        $Agrupamentos = AgrupamentosContas::orderBy('nome', 'asc')->get();

        return view('PlanoContas.edit', compact('cadastro', 'Empresas','Agrupamentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $EmpresaID = $request->EmpresaSelecionada;

        if ($EmpresaID) {
            $Descricao = Empresa::find($EmpresaID)->Descricao;
            $Registro = $id;
            $Conta = Conta::where('EmpresaID', '=', $EmpresaID)
                ->where('Planocontas_id', '=', $id)
                ->first();

            if ($Conta) {
                session(['error' => 'A conta já existe para a empresa: ' . $Descricao . '!']);
                return redirect(route('PlanoContas.edit', $Registro));
            }

            $Created = Carbon::now()->format('d/m/Y H:i:s');
            $Modified = Carbon::now()->format('d/m/Y H:i:s');
            $UsuarioID = auth()->user()->id;
            $InseridoPor = auth()->user()->email;

            $Contanova = new Conta();

            $Contanova->fill(['EmpresaID' => $EmpresaID,
            'Planocontas_id' => $id,
            'Created' => $Created,
            'Modified' => $Modified,
            'Usuarios_id' => $UsuarioID,
            'Contapagamento' => 1,
            'Nota' => $InseridoPor]);

            $Contanova->save();
            session(['success' => 'Conta cadastrada para a empresa: ' . $Descricao . '!']);
            return redirect(route('PlanoContas.edit', $id));
        }

        $cadastro = PlanoConta::find($id);

        $cadastro->fill($request->all());

        $cadastro->save();



        session(['success' => 'Conta alterada!']);
        return redirect(route('PlanoContas.edit', $id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cadastro = PlanoConta::find($id);
        $cadastro->delete();
        return redirect(route('PlanoContas.index'));
    }

    public function Balancetesgerarpdf(Request $request)
    {
                    $html = $request->view;

                    // Inicie o buffer de saída
                    ob_start();


                    // $suaView = '<html><body><h1>teste view</h1><p>Conteúdo da View...</p></body></html>';
                    $suaView = $html;
                    // Imprima o conteúdo HTML
                    echo $suaView;

                    // Capture o conteúdo HTML na variável
                    $conteudoHTML = ob_get_clean();

                    // Agora $conteudoHTML contém o HTML da sua view



                    // Crie uma nova instância do Dompdf
                    $options = new Options();
                    $options->set('isHtml5ParserEnabled', true);
                    $options->set('isPhpEnabled', true);
                    $pdf = new Dompdf($options);



                    // Suponha que $suaView seja o conteúdo HTML da sua view
                    $suaView = $conteudoHTML;

                    // Carregue o conteúdo HTML no Dompdf
                    $pdf->loadHtml($suaView);

                    // Renderize o PDF (opcional)
                    $pdf->render();

                    // Saída do PDF
                    $pdf->stream('nome_do_arquivo.pdf', ['Attachment' => 0]);

    }
}
