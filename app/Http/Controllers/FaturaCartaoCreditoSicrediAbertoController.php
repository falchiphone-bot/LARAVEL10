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

class FaturaCartaoCreditoSicrediAbertoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:LEITURA DE ARQUIVO - LISTAR'])->only('SelecionaDatasFaturaEmAberto');
    }

    public function SelecionaDatasFaturaEmAberto(Request $request)
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
        $SITUACAO_EXTRATO_FECHADA = $request->SITUACAO_EXTRATO_FECHADA;
        $SITUACAO_EXTRATO_ABERTO = $request->SITUACAO_EXTRATO_ABERTO;

        // if ($extension != 'txt' && $extension != 'csv' && $extension != 'xlsx' && $extension != 'xls') {
        //     session(['Lancamento' => 'Arquivo considerado não compatível para este procedimento! Autorizados arquivos com extensões csv, txt, xls e xlsx. Apresentado o último enviado. ATENÇÃO!']);
        //     return redirect(route('LeituraArquivo.index'));
        // }

        if ($extension != 'xls' && $extension != 'xlsx') {
            session(['Lancamento' => 'Arquivo considerado não compatível para este procedimento! Autorizados arquivos com extensões xls. Apresentado o último enviado. ATENÇÃO!']);
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
        $linha_2 = $planilha_ativa->getCell('B' . 2)->getValue();
        ///////////////////////////// DADOS DA LINHA 4 COLUNA 2 PARA DEFINIR CONTAS
        $linha_4_coluna_2 = $planilha_ativa->getCell('B' . 4)->getValue();
        ///////////////////////////// DADOS DA LINHA 7 PARA DEFINIR CONTAS
        $linha_8 = null;

        $linha_9 = null;
        $linha_12 = trim($planilha_ativa->getCell('C' . 12)->getValue());

        if($SITUACAO_EXTRATO_FECHADA == true)
        {
            $linha_8 = trim($planilha_ativa->getCell('A' . 8)->getValue());

            // dd('SITUAÇÃO DO EXTRATO: ' . $linha_8 . ' - ' . $linha_12, 'TEM QUE SER FECHADA');
        }
        else
        if($SITUACAO_EXTRATO_ABERTO == true)
        {

             ///////////////////////////// DADOS DA LINHA 12 PARA DEFINIR SITUAÇÃO
             $linha_8 = trim($planilha_ativa->getCell('A' . 8)->getValue());
             $linha_9 = trim($planilha_ativa->getCell('C' . 9)->getValue());

            //  dd('SITUAÇÃO DO EXTRATO: ' . $linha_8 . ' - ' . $linha_12, "TEM QUE SER ABERTO");
                if ($linha_9 != 'Fatura em aberto, sujeita a alterações') {
                    session([
                        'Lancamento' =>
                                        'Arquivo e ou ficheiro não identificado!
                        Verifique se o mesmo está correto para este procedimento!
                        A situação do extrato tem que ser: Fatura em aberto, sujeita a alterações. Neste arquivo está como situação: ' . $linha_8,
                                ]);
                    return redirect(route('LeituraArquivo.index'));
                }


        }
        else
        if ($linha_8 == null || $linha_9 == null)
         {
            session(['Lancamento' => 'Linha 122. Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento!']);
            return redirect(route('LeituraArquivo.index'));
        }



        // dd('Pesquisar se já lançada!');

        $ContaCartao = null;
        $DespesaContaDebitoID = null;
        $CashBackContaCreditoID = '19271';

        $string = $linha_8;
        $parts = explode('-', $string);
        $result_linha8 = trim($parts[0]);
        $linhas1_8 = $linha_2 . '-' . $result_linha8;



        if ($linhas1_8 === 'SANDRA ELISA MAGOSSI FALCHI-4891.67XX.XXXX.9125') {
            $ContaCartao = '17457';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
        } elseif ($linhas1_8 === 'SANDRA ELISA MAGOSSI FALCHI-4891.67XX.XXXX.9919') {
            $ContaCartao = '17457';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
        } elseif ($linhas1_8 === 'SANDRA ELISA MAGOSSI FALCHI-5122.67XX.XXXX.0910') {
            $ContaCartao = '19468';
            $Empresa = 11;
            $DespesaContaDebitoID = '15372';   //// ALTERADO EM 31.07.2023
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
        }
        elseif ($linhas1_8 === 'SANDRA ELISA MAGOSSI FALCHI-5122.67XX.XXXX.0126') {
            $ContaCartao = '19468';
            $Empresa = 11;
            $DespesaContaDebitoID = '15372';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
        }
        elseif ($linhas1_8 === 'PEDRO ROBERTO FALCHI-4891.67XX.XXXX.2113') {
            $ContaCartao = '17458';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);

        } elseif ($linhas1_8 === 'PEDRO ROBERTO FALCHI-4891.67XX.XXXX.2915') {
            $ContaCartao = '17458';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19271';
            //  dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
        } elseif ($linha_4_coluna_2 === '54958-4') {
            $ContaCartao = '17458';
            $Empresa = 11;
            $DespesaContaDebitoID = '15354';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
        } else {
            session(['Lancamento' => 'Linha 187 - Arquivo e ou ficheiro não identificado! Não localizei nenhuma conta para associar. Verifique se o mesmo está correto para este procedimento! '.$linhas1_8]);
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

        $novadata = null;

        if($SITUACAO_EXTRATO_ABERTO == true)
        {
            $AcrescentaLinha = 13;
            $novadata = array_slice($cellData, $AcrescentaLinha);
            // dd($novadata);
        }
        else
        if($SITUACAO_EXTRATO_FECHADA == true)
        {
            $AcrescentaLinha = 23;
            $novadata = array_slice($cellData, $AcrescentaLinha);

        }

        if($novadata == null)
        {
            session(['Lancamento' =>
            'Linha 226 - Arquivo e ou ficheiro não identificado!
             Verifique se o mesmo está correto para este procedimento ou selecionou errado a opção de aberto ou fechado!
             A situação deste extrato é: ' . $linha_8 . ' ou ' . $linha_9]);
            return redirect(route('LeituraArquivo.index'));
        }



        $despesasnobrasil = array_slice($cellData, 11);
        // $novadata = array_slice($cellData, 152);

        ///// CONFERE SE EMPRESA BLOQUEADA
        $Empresa = '11';
        $EmpresaBloqueada = Empresa::find($Empresa);
        $Data_bloqueada = $EmpresaBloqueada->Bloqueiodataanterior->format('d/m/Y');

        /////////   ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        foreach ($novadata as $PegaLinha => $item) {

            $Data = $item[1];


            $linha = $PegaLinha + $AcrescentaLinha;


            //  if($linha == 24) {
            //     dd($linha, $item);
            // }

            if ($Data === null || $Data === 'Valor Total R$:') {
                session(['Lancamento' => 'Última linha executada, ou seja, terminado na linha: ' . $linha]);
                break;
            }

            if ($Data == 'Histórico de Despesas') {
                session(['Lancamento' => 'Linha 211 - Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento!']);
                return redirect(route('LeituraArquivo.index'));
            }
            $Descricao = $item[2];


            if (strpos($Descricao, 'CREDITO CASH BACK') !== false) {
                //// se contiver, conter o texto na variável
                // dd($linha, $Descricao);
                continue;
            } elseif (strpos($Descricao, 'PAGAMENTO DEBITO EM') !== false) {
                //// se contiver, conter o texto na variável

                continue;
            } elseif (strpos($Descricao, 'PAGAMENTO') !== false) {
                //// se contiver, conter o texto na variável

                continue;
            }



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
                            $linha +
                            1,
                    ]);
                    return redirect(route('LeituraArquivo.index'));
                }
            }

            $NumeroParcela = null;
            $QuantidadeParcela = null;

            $Descricao = $item[2];
            $Parcela = $item[3];
            if ($Parcela == '  ') {
                // continue;
            } else {
                $NumeroParcela = substr($Parcela, 0, 2);
                $QuantidadeParcela = substr($Parcela, 4, 2);
                // dd("318","Parcela: ".$Parcela, "Número de parcela: ".$NumeroParcela, 'Quantidade de parcela: '.$QuantidadeParcela, $Descricao );
            }


            $Valor = $item[4];

            $valor_numerico = preg_replace('/[^0-9,.]/', '', $Valor);
            $Valor_sem_virgula = str_replace(',', '', $Valor);
            $Valor_sem_pontos_virgulas = str_replace('.', '', $Valor_sem_virgula);
            $valor_sem_simbolo = substr($Valor_sem_pontos_virgulas, 3); // Extrai a string sem o símbolo "R$"

            $valor_numerico = floatval($valor_sem_simbolo) / 100;
            // $valor_formatado = number_format($valor_numerico, 2, '.', ''); retirado em colocado a linha debaixo
            $valor_formatado = $Valor_sem_pontos_virgulas / 100;

            if ($valor_formatado == 0.0) {
                session([
                    'Lancamento' => 'ALGO ERRADO! VALOR 0.00. Linha:  ' . $linha + 2,
                ]);
                $Mensagem = 'ALGO ERRADO! VALOR 0.00. Linha:  ' . $linha + 2;
                // return redirect(route('LeituraArquivo.index'));

                continue;
            }
            // dd('342',$Data, $Descricao, $linha, $Valor, $valor_formatado, $valor_numerico, $Parcela, $NumeroParcela, $QuantidadeParcela);
            $arraydatanova = compact('Data', 'Descricao', 'valor_formatado',  'valor_sem_simbolo', 'valor_numerico', 'Parcela', 'NumeroParcela', 'QuantidadeParcela');
            // dd($Valor,$Valor_sem_virgula,$Valor_sem_pontos_virgulas,$valor_sem_simbolo ,$valor_numerico,$arraydatanova);
            $Extrato[] = null;

            $rowData = $cellData;

// dd( $arraydatanova, $Valor_sem_pontos_virgulas, $valor_formatado);
            $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                ->where('Valor', $valorString = $arraydatanova['valor_formatado'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaCreditoID', $ContaCartao)
                ->First();


            // if($lancamento == null){
            //     dd($lancamento,$linha, $item);
            // }

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
                                $linha +
                                1,
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

            $sequencia = "POSTO CIDADE JARDIM";
            $sequenciaExistePOSTOCIDADEJARDIM = false;
            if (strpos($Descricao, $sequencia) !== false) {
                $sequenciaExistePOSTOCIDADEJARDIM = true;
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
                // dd($lancamento,$linha, $item);
            } else {
                // if ($request->criarlancamentosemhistorico == true) {

                // dd($lancamento,$linha, $item, $ContaCartao,'SEM LANÇAR');
                if ($Parcela) {
                    $DescricaoCompleta = $arraydatanova['Descricao'] . ' Parcela ' . $Parcela;
                } else {
                    $DescricaoCompleta = $arraydatanova['Descricao'];
                }

                $arraydatanova['Localizou'] = 'NAO';
                // dd($arraydatanova);

                $historico = Historicos::where('EmpresaID', $Empresa)
                    ->where('Descricao', 'like', '%' . trim($Descricao) . '%')
                    ->where('ContaCreditoID', $ContaCartao)
                    ->first();


                if ($historico) {
                    $DespesaContaDebitoID = $historico->ContaDebitoID;
                    if($sequenciaExistePOSTOCIDADEJARDIM)
                    {
                        if($historico->Valor <= 100)
                        {
                            $DespesaContaDebitoID = '15366';
                        }
                        //  dd($DespesaContaDebitoID, $historico->Valor);
                        //  continue;
                    }
                    // dd($lancamento,$linha, $item, $ContaCartao,'SEM LANÇAR', $DespesaContaDebitoID);
                    ////// LINHAS ABAIXO INSERIDAS AQUI APOS 01.10.2024
                    if(empty($Parcela) || $Parcela == null) {
                        DD('466 - criar lançamento sem parcelas',$Parcela, $DescricaoCompleta);
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
                    session(['Lancamento' => 'Lancamentos criados com históricos!']);
                    // dd('Criando lançamento com histórico', $historico,session('Lancamento'));
                    $arraydatanova['Criou com historico'] = 'SIM';

                    // DD($arraydatanova, 'inserico pelo histórico');



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

                if ($NumeroParcela !== null && $QuantidadeParcela !== null) {



                    if ($NumeroParcela > 1) {
                        dd(500,$NumeroParcela, $QuantidadeParcela, $linha );
                        continue;
                    }

                    $registros = [];
                    for ($i = 1; $i <= $QuantidadeParcela; $i++) {
                        $novoRegistroParcelas = [
                            'EmpresaID' => $Empresa,
                            'ContaDebitoID' => $DespesaContaDebitoID,
                            'ContaCreditoID' => $ContaCartao,
                            'NumeroParcela' => $NumeroParcela,
                            'QuantidadeParcela' => $QuantidadeParcela,
                            'Valor' => ($valorString = $valor_formatado),
                            'Data' => $Data,
                            'Descricao' => $Descricao . ' Parcela:' . $i . ' de ' . $QuantidadeParcela,
                            'Usuarios_id' => auth()->user()->id,
                        ];

                        // Faça algo com o novo registro, como armazená-lo em um banco de dados ou exibi-lo
                        // por exemplo:
                        // salvarRegistroNoBancoDeDados($novoRegistro);
                        // exibirRegistro($novoRegistro);
                        // echo $i, ' ';
                        // Você também pode adicionar o registro a uma lista, array, ou qualquer outra estrutura de dados necessária
                        $registros[] = $novoRegistroParcelas;
                    }




                    foreach ($registros as $incluirregistros) {
                        $lancamentoregistros = Lancamento::where('DataContabilidade', $incluirregistros['Data'])
                            ->where('Valor', $valorString = $incluirregistros['Valor'])
                            ->where('EmpresaID', $incluirregistros['EmpresaID'])
                            ->where('ContaCreditoID', $incluirregistros['ContaCreditoID'])
                            ->where('Descricao', trim($incluirregistros['Descricao']))
                            ->First();

                        if ($lancamentoregistros) {
                            // dd('JÁ LANÇADO', $Descricao, $incluirregistros);
                            continue;
                        } else {
                            // if ($request->criarlancamentosemhistorico !== true) {
                            //     continue;
                            // }
                            // dd($linha, $Descricao,'400',$lancamentoregistros );

                            Lancamento::create([
                                'Valor' => $incluirregistros['Valor'],
                                'EmpresaID' => $incluirregistros['EmpresaID'],
                                'ContaDebitoID' => $incluirregistros['ContaDebitoID'],
                                'ContaCreditoID' => $incluirregistros['ContaCreditoID'],
                                'Descricao' => $incluirregistros['Descricao'],
                                'Usuarios_id' => $incluirregistros['Usuarios_id'],
                                'DataContabilidade' => $incluirregistros['Data'],
                                'HistoricoID' => '',
                            ]);
                            $arraydatanova['Lancou registros'] = 'SIM';
                        }
                    }
                } else {


                    ///// RETIRADO APOS 01.10.2024
                    // if ($historico) {

                    //     Lancamento::create([
                    //         'Valor' => ($valorString = $valor_formatado),
                    //         'EmpresaID' => $Empresa,
                    //         'ContaDebitoID' => $DespesaContaDebitoID,
                    //         'ContaCreditoID' => $ContaCartao,
                    //         'Descricao' => $DescricaoCompleta,
                    //         'Usuarios_id' => auth()->user()->id,
                    //         'DataContabilidade' => $Data,
                    //         'HistoricoID' => '',
                    //     ]);
                    //     session(['Lancamento' => 'Lancamentos criados com históricos!']);
                    //     // dd('Criando lançamento com histórico', $historico,session('Lancamento'));
                    //     $arraydatanova['Criou com historico'] = 'SIM';

                    //     DD($arraydatanova, 'inserico pelo histrórico');
                    // }

                    ////// O ACIMA FOI RETIRADO APOS 01.10.2024

                    if ($request->criarlancamentosemhistorico == true) {
                        //  dd("Criando lançamento sem histórico!");
                        if ($historico === null) {
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
                        $arraydatanova['CRIOU SEM HISTORICO'] = 'SIM';
                        session(['Lancamento' => 'Lancamentos criados sem históricos!']);
                    }
                }

                // dd('fim');
                // session(['Lancamento' => 'Lancamentos criados!']);
                // dd('Criado lançamento com histórico', $historico);
            }
            $Extrato[] = $arraydatanova;
            // }///////////////retirar - liga com linha 658

            // dd($Extrato);
        }
        if ($Mensagem) {
            $xMensagem = session('Lancamento') . ' Mensagem auxiliar: ' . $Mensagem;
            session([
                'Lancamento' => $xMensagem,
            ]);
            $Mensagem = null;
        }

        if ($request->filtrarnaolocalizou) {
            /////////////// filtra somente o Localizou = NAO
            $registros = $Extrato;

            $registrosNaoLocalizados = array_filter($registros, function ($registro) {
                return isset($registro['Localizou']) && $registro['Localizou'] === 'NAO';
            });

            if($registrosNaoLocalizados == null){
                session(['Lancamento' => 'SEM REGISTROS PARA APRESENTAR!']);
                return view('LeituraArquivo.SelecionaDatas', ['array' => $rowData]);
            }

            $rowData = $registrosNaoLocalizados;
            if($request->verarray){
                //   dd($rowData, 'Linha 538');

             dd($rowData, $lancamento,'ÚLTIMA LINHA: ' . $linha, "ÚLTIMO REGISTRO: " , $item, $ContaCartao,'SEM LANÇAR', $DespesaContaDebitoID);

            }

            return view('LeituraArquivo.SelecionaDatas', ['array' => $rowData]);
            ///////////////////////////////////////////////////////////////////////////////////
        }



        $rowKCData = $cellData;

        return view('LeituraArquivo.SelecionaDatas', ['array' => $rowData]);
    }
}
