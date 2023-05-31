<?php

namespace App\Http\Controllers;

use App\Helpers\SaldoLancamentoHelper;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\Conta;
use App\Models\Historicos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Faker\Core\DateTime;
use Illuminate\Support\Collection;
use PhpParser\Node\Stmt\Foreach_;
use Livewire\Component;
use PhpParser\Node\Stmt\Continue_;
use Ramsey\Uuid\Type\Decimal;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\get;

class ExtratoConectCarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:EXTRATO CONECTCAR - LISTAR'])->only('ExtratoConectar');
    }

    public function ExtratoConectar(Request $request)
    {
        $DESCONSIDERAR_BLOQUEIOS = $request->DESCONSIDERAR_BLOQUEIOS;
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $Mensagem = null;
        /////// aqui fica na pasta temporário /temp/    - apaga
        $path = $request->file('arquivo')->getRealPath();
        $file = $request->file('arquivo');


        $extension = $file->getClientOriginalExtension();

        $Complemento = $request->complemento;
        $name = $file->getClientOriginalName();
        $caminho = $path;
        // if ($extension != 'txt' && $extension != 'csv' && $extension != 'xlsx' && $extension != 'xls') {
        //     session(['Lancamento' => 'Arquivo considerado não compatível para este procedimento! Autorizados arquivos com extensões csv, txt, xls e xlsx. Apresentado o último enviado. ATENÇÃO!']);
        //     return redirect(route('LeituraArquivo.index'));
        // }

        if ($extension != 'xlsx') {
            session(['Lancamento' => 'Arquivo considerado não compatível para este procedimento! Autorizados arquivos com extensões csv. Apresentado o último enviado. ATENÇÃO!']);
            return redirect(route('LeituraArquivo.index'));
        }

        $email = auth()->user()->email;
        $user = str_replace('@', '', $email);
        $user = str_replace('.', '', $user);
        $arquivosalvo = 'app/contabilidade/' . $user . '.prf';
        copy($path, storage_path($arquivosalvo));

        // Abre o arquivo Excel
        $spreadsheet = IOFactory::load($caminho);

        // Seleciona a primeira planilha do arquivo
        $worksheet = $spreadsheet->getActiveSheet();

        // Obtém a última linha da planilha
        $lastRow = $worksheet->getHighestDataRow();

        // Obtém a última coluna da planilha
        $lastColumn = $worksheet->getHighestDataColumn();

        // Converte a última coluna para um número (ex: "D" para 4)
        $lastColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastColumn);

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Obter a planilha ativa (por exemplo, a primeira planilha)
        $planilha_ativa = $spreadsheet->getActiveSheet();
        ///////////////////////////// DADOS DA LINHA 1 PARA DEFINIR CONTAS
        $linha_1 = $planilha_ativa->getCell('B' . 1)->getValue();
        ///////////////////////////// DADOS DA LINHA 4 COLUNA 2 PARA DEFINIR CONTAS
        $linha_4_coluna_2 = $planilha_ativa->getCell('B' . 4)->getValue();
        ///////////////////////////// DADOS DA LINHA 7 PARA DEFINIR CONTAS
        $linha_7 = $planilha_ativa->getCell('A' . 7)->getValue();

        // if ($linha_7 == null) {
        //     session(['Lancamento' => 'Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento!']);
        //     return redirect(route('LeituraArquivo.index'));
        // }

        // ///////////////////////////// DADOS DA LINHA 12 PARA DEFINIR SITUAÇÃO
        // $linha_8 = trim($planilha_ativa->getCell('B' . 8)->getValue());

        // if ($linha_8 != 'Fatura em aberto, sujeita a alterações') {
        //                 session([
        //                     'Lancamento' =>
        //                         'Arquivo e ou ficheiro não identificado!
        //         Verifique se o mesmo está correto para este procedimento!
        //         A situação do extrato tem que ser: Fatura em aberto, sujeita a alterações. Neste arquivo está como situação: ' . $linha_8,
        //                 ]);
        //                 return redirect(route('LeituraArquivo.index'));
        // }
 ///////////////////////////// DADOS DA LINHA 10 PARA DEFINIR TITULAR POR CPF
 $linha_10 = trim($planilha_ativa->getCell('C' . 10)->getValue());


        $ContaCartao = null;
        $DespesaContaDebitoID = null;
        $CashBackContaCreditoID = '19271';

        // $string = $linha_7;
        // $parts = explode('-', $string);
        // $result_linha7 = trim($parts[0]);
        // $linhas1_7 = $linha_1 . '-' . $result_linha7;

        if ($linha_10 === '5832745876') {
            $ContaCartao = '18949';
            $Empresa = 11;
            $DespesaContaDebitoID = '19460';
            // $CashBackContaCreditoID = '19271';
            //  dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);

        } else {
            session(['Lancamento' => 'Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento!']);
            return redirect(route('LeituraArquivo.index'));
        }


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Array que irá armazenar os dados das células
        $cellData = [];


        // Loop para percorrer todas as células da planilha
        $dateValue = null;
        for ($row = 1; $row <= $lastRow; $row++) {
            for ($column = 1; $column <= $lastColumnIndex; $column++) {
                // Obtém o valor da célula
                $cellValue = $worksheet->getCellByColumnAndRow($column, $row)->getValue();

                // Adiciona o valor da célula ao array $cellData
                $cellData[$row][$column] = $cellValue;
            }
        }

        $novadata = array_slice($cellData, 28);
        // $novadata = array_slice($cellData, 152);

        ///// CONFERE SE EMPRESA BLOQUEADA
        $Empresa = '11';
        $EmpresaBloqueada = Empresa::find($Empresa);
        $Data_bloqueada = $EmpresaBloqueada->Bloqueiodataanterior->format('d/m/Y');


        // dd(131,$linha_10,$ContaCartao ,$Empresa,$DespesaContaDebitoID, $novadata);

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
         $Saldo = null;
         $UltimoDia = null;
        foreach ($novadata as $PegaLinha => $item) {
            if($Saldo == null){
                $Saldo = $item[14];
                $DataCampo_Ultimo_Dia = $item[2];
                $Data = substr($DataCampo_Ultimo_Dia,0,10);
                $UltimoDia = $Data;
               }
            $DataCampo = $item[2];
            $Data = substr($DataCampo,0,10);
 $Descricao = $item[2];
 $passagem = $item[6];


/////////////////////////////////////////////////////////////////////////////// Detecta estacionamentos
$termo_Passagem = null;
if (preg_match('/\bPassagem\b/', $passagem, $matches)) {
    $termo_Passagem = $matches[0];
    // dd($termo_Passagem);
}
if($termo_Passagem == 'Passagem'){
    $DespesaContaDebitoID = '19462';
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////// Detecta plano
$termo_Plano_Completo = null;
if (preg_match('/\bPlano\b/', $passagem, $matches)) {
    $termo_Plano_Completo = $matches[0];
    // dd($termo_Passagem);
}
if($termo_Plano_Completo == 'Plano'){
    $DespesaContaDebitoID = '19463';
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////

            $linha = $PegaLinha + 29; ///// pega a linha atual da lista. Deve fazer a seguir:$PegaLinha => $item, conforme linha anterior

            if ($Data == '') {
                session([
                    'Lancamento' => 'Terminado na linha ' . $linha .
                    '. Saldo no extrato de: ' . $Saldo,
                ]);

                $SaldoAnterior = SaldoLancamentoHelper::Anterior($UltimoDia, $ContaCartao, $Empresa);
                $SaldoDia = SaldoLancamentoHelper::Dia($UltimoDia, $ContaCartao, $Empresa);

                $SaldoAtual = $SaldoAnterior + $SaldoDia;

                // dd($UltimoDia,$SaldoAnterior,$SaldoDia,$SaldoAtual);

                $DiferecaSaldo = number_format($Saldo - $SaldoAtual);

                if ($DiferecaSaldo == 0) {
                    $TextoConciliado = 'CONCILIAÇÃO COM EXATIDÃO DE SALDOS.';
                } else {
                    $TextoConciliado = 'SALDOS NÃO CONFEREM! VERIFIQUE!';
                }

                session([
                    'Lancamento' => 'Terminado na linha ' . $linha .
                     '. Saldo no extrato bancário de: ' . number_format($Saldo, 2, '.', ',') .
                     '.' . ' Saldo atual no sistema contábil de ' . number_format($SaldoAtual, 2, '.', ',') . ' = ' . $TextoConciliado,
                ]);

                $DiferençaApurada = $SaldoAtual - $Saldo ;

                if(number_format($DiferençaApurada, 2, '.', ',') ==     0.00){
                    session([
                        'Lancamento' => 'Terminado na linha ' . $linha .
                         '. Saldo no extrato bancário de: ' . number_format($Saldo, 2, '.', ',') .
                         '.' . ' Saldo atual no sistema contábil de ' . number_format($SaldoAtual, 2, '.', ',') . ' = ' . $TextoConciliado,
                     ]);
                }
                else{
                    session([
                        'Lancamento' => 'Terminado na linha ' . $linha .
                         '. Saldo no extrato bancário de: ' . number_format($Saldo, 2, '.', ',') .
                         '.' . ' Saldo atual no sistema contábil de ' . number_format($SaldoAtual, 2, '.', ',') . ' = ' . $TextoConciliado.
                          " Diferença apurada: ".number_format($DiferençaApurada, 2, '.', ',')
                    ]);

                }
                return redirect(route('LeituraArquivo.index'));
            }


// if($linha == 46){
//  DD($item, $Descricao, $termo_Passagem);
// }

 //  '. Saldo no extrato de: ' . number_format($Saldo, 2, '.', ','),

            // if ($Data == 'Histórico de Despesas') {
            //     session(['Lancamento' => 'Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento!']);
            //     return redirect(route('LeituraArquivo.index'));
            // }


            // if (strpos($Descricao, 'CREDITO CASH BACK') !== false) {
            //     //// se contiver, conter o texto na variável
            //     // dd($linha, $Descricao);
            //     continue;
            // } elseif (strpos($Descricao, 'PAGAMENTO DEBITO EM') !== false) {
            //     //// se contiver, conter o texto na variável

            //     continue;
            // } elseif (strpos($Descricao, 'PAGAMENTO') !== false) {
            //     //// se contiver, conter o texto na variável

            //     continue;
            // }

            $carbon_data = \Carbon\Carbon::createFromFormat('d/m/Y', $Data);
            $linha_data_comparar = $carbon_data->format('Y-m-d');

            $carbon_data = \Carbon\Carbon::createFromFormat('d/m/Y', $Data_bloqueada);
            $Data_bloqueada_comparar = $carbon_data->format('Y-m-d');


            if ($DESCONSIDERAR_BLOQUEIOS == null) {
                    if ($linha_data_comparar <= $Data_bloqueada_comparar) {
                        session([
                            'Lancamento' =>
                                'Empresa bloqueada no sistema para o lançamento
                        solicitado! Deverá desbloquear a data de bloqueio
                        da empresa para seguir este procedimento. Bloqueada para até ' .
                                $EmpresaBloqueada->Bloqueiodataanterior->format('d/m/Y') .
                                '! Encontrado lançamento na linha ' .
                                $linha,
                        ]);
                        return redirect(route('LeituraArquivo.index'));
                    }
            }


                        // $NumeroParcela = null;
                        // $QuantidadeParcela = null;
                        $Veiculo = $item[4];
                        $Descricao_transacao = $item[6];
                        $Descricao = 'Veiculo: '.$Veiculo.' - '.$Descricao_transacao;

                        // $Parcela = $item[3];
                        // if ($Parcela == '  ') {
                        //     // continue;
                        // } else {
                        //     $NumeroParcela = substr($Parcela, 1, 2);
                        //     $QuantidadeParcela = substr($Parcela, 6, 2);
                        // }
                        //  dd($Parcela,$NumeroParcela, $QuantidadeParcela, $Descricao );

                        $Valor12 = $item[12];
                        if($Valor12){
                            $Valor = abs($item[12]);
                        }else
                        {
                            continue;
                        }


                            $valor_numerico = preg_replace('/[^0-9,.]/', '', $Valor);
                            $Valor_sem_virgula = str_replace(',', '', $Valor);
                            // $Valor_sem_pontos_virgulas = str_replace('.', '', $Valor_sem_virgula);
                            // $valor_sem_simbolo = substr($Valor_sem_pontos_virgulas, 3); // Extrai a string sem o símbolo "R$"

                            // $valor_numerico = floatval($valor_sem_simbolo) / 100;
                            // $valor_formatado = number_format($valor_numerico, 2, '.', '');


                            $valor_formatado =  $Valor_sem_virgula;
                            if ($valor_formatado == 0.0) {
                                session([
                                    'Lancamento' => 'ALGO ERRADO! VALOR 0.00. Linha:  ' . $linha + 1,
                                ]);
                                $Mensagem = 'ALGO ERRADO! VALOR 0.00. Linha:  ' . $linha + 1;
                                // return redirect(route('LeituraArquivo.index'));

                                continue;
                            }
          


            $arraydatanova = compact('Data', 'Descricao', 'valor_formatado','Veiculo');
            // dd($Valor,$Valor_sem_virgula,$valor_numerico,$arraydatanova);

            $rowData = $cellData;

            $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                ->where('Valor', $valorString = $arraydatanova['valor_formatado'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaCreditoID', $ContaCartao)
                ->First();

            if ($DESCONSIDERAR_BLOQUEIOS == null) {
                    if ($lancamento) {
                        $dataLancamento_carbon = Carbon::createFromDate($lancamento->DataContabilidade);
                        $dataLancamento = $dataLancamento_carbon->format('Y/m/d');
                        $data_conta_debito_bloqueio = $lancamento->ContaDebito->Bloqueiodataanterior;
                        if ($data_conta_debito_bloqueio == null) {
                            session([
                                'Lancamento' =>
                                    'Conta DÉBITO: ' .
                                    $lancamento->ContaDebito->PlanoConta->Descricao .
                                    ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                                da conta para seguir este procedimento. Bloqueada para até NULA' .
                                    '! Encontrado lançamento na linha ' .
                                    $linha +
                                    1,
                            ]);
                            return redirect(route('LeituraArquivo.index'));
                        }

                        if ($data_conta_debito_bloqueio->greaterThanOrEqualTo($dataLancamento)) {
                            session([
                                'Lancamento' =>
                                    'Conta DÉBITO: ' .
                                    $lancamento->ContaDebito->PlanoConta->Descricao .
                                    ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                                da conta para seguir este procedimento. Bloqueada para até ' .
                                    $data_conta_debito_bloqueio->format('d/m/Y') .
                                    '! Encontrado lançamento na linha ' .
                                    $linha,
                            ]);
                            return redirect(route('LeituraArquivo.index'));
                        }
                    }

                    if ($lancamento) {
                        $data_conta_credito_bloqueio = $lancamento->ContaCredito->Bloqueiodataanterior;
                        if ($data_conta_credito_bloqueio->greaterThanOrEqualTo($dataLancamento)) {
                            session([
                                'Lancamento' =>
                                    'Conta CRÉDITO: ' .
                                    $lancamento->ContaCredito->PlanoConta->Descricao .
                                    ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio da
                                conta para seguir este procedimento. Bloqueada para até ' .
                                    $data_conta_credito_bloqueio->format('d/m/Y') .
                                    '! Encontrado lançamento na linha ' .
                                    $linha,
                            ]);
                            return redirect(route('LeituraArquivo.index'));
                        }
                    }
                }

            if ($lancamento) {
                // dd($lancamento);
                // session([
                //     'Lancamento' =>
                //         'Nenhum lançamento criado!
                //  Consultado todos os lançamentos iniciado na linha 11 e terminado na linha ' .
                //         $linha +
                //         1 .
                //         '!',
                // ]);
            } else {
                // if ($request->criarlancamentosemhistorico == true) {

                // if ($Parcela) {
                //     $DescricaoCompleta = $arraydatanova['Descricao'] . ' Parcela ' . $Parcela;
                // } else {
                //     $DescricaoCompleta = $arraydatanova['Descricao'];
                // }

                $DescricaoCompleta = $arraydatanova['Descricao'];
                // dd($arraydatanova);
                $TextoHistorico = $Veiculo.'-PAGAMENTOS DE PEDAGIOS';
                $historico = Historicos::where('EmpresaID', $Empresa)
                    ->where('Descricao', 'like', '%' . trim($TextoHistorico) . '%')
                    ->where('ContaCreditoID', $ContaCartao)
                    ->first();

                if ($historico) {
                    $DespesaContaDebitoID = $historico->ContaDebitoID;
                } else {
                    if ($request->vercriarlancamento == true) {
                        dd('Sem histórico!', $historico, $arraydatanova, $Descricao, $ContaCartao, $DespesaContaDebitoID);
                    }
                }

                if ($request->verhistorico == true) {
                    $SituacaoHistorico = 'HISTÓRICO CADASTRADO!';
                    if ($historico == null) {
                        $SituacaoHistorico = ' O ABAIXO PRECISA SER CADASTRADO EM HISTÓRICOS PRÉ PROGRAMADO CASO QUEIRA LANÇAR POR HISTÓRICO, POIS NÃO TEM CADASTRO!';
                    }
                    dd('VERIFICANDO SE TEM HISTÓRICO!', ' Mensagem: ' . $SituacaoHistorico . ' => ' . $historico, $arraydatanova, $Descricao, $ContaCartao, $DespesaContaDebitoID);
                }


                    if ($historico == true) {
                        //  dd("Criar com histórico");

                        Lancamento::create([
                            'Valor' => ($valorString = $valor_formatado),
                            'EmpresaID' => $Empresa,
                            'ContaDebitoID' => $DespesaContaDebitoID,
                            'ContaCreditoID' => $ContaCartao,
                            'Descricao' => $DescricaoCompleta,
                            'Usuarios_id' => auth()->user()->id,
                            'DataContabilidade' => $Data,
                            'HistoricoID' => '',
                        ]);
                        session(['Lancamento' => 'Lancamentos criados com históricos!']);
                        // dd('Criando lançamento com histórico', $historico,session('Lancamento'));
                    }

                    if ($request->criarlancamentosemhistorico == true) {
                        //  dd("Criando lançamento sem histórico!");
                        if ($historico === null) {
                            // dd("Criar SEM histórico");
                            Lancamento::create([
                                'Valor' => ($valorString = $valor_formatado),
                                'EmpresaID' => $Empresa,
                                'ContaDebitoID' => $DespesaContaDebitoID,
                                'ContaCreditoID' => $ContaCartao,
                                'Descricao' => $DescricaoCompleta,
                                'Usuarios_id' => auth()->user()->id,
                                'DataContabilidade' => $Data,
                                'HistoricoID' => '',
                            ]);
                        }
                        session(['Lancamento' => 'Lancamentos criados sem históricos!']);
                    }


                // dd('fim');
                // session(['Lancamento' => 'Lancamentos criados!']);
                // dd('Criado lançamento com histórico', $historico);
            }
            // $UltimoDia = $Data ;
        }

        if ($Mensagem) {
            $xMensagem = session('Lancamento') . ' Mensagem auxiliar: ' . $Mensagem;
            session([
                'Lancamento' => $xMensagem,
            ]);
            $Mensagem = null;
        }
        $rowData = $cellData;
        //    $rowData = $novadata;
        // dd("Fim",$Descricao,$lancamento);
        return view('LeituraArquivo.SelecionaDatas', ['array' => $rowData]);
    }
}
