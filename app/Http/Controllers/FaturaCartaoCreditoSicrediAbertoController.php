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
        // if ($extension != 'txt' && $extension != 'csv' && $extension != 'xlsx' && $extension != 'xls') {
        //     session(['Lancamento' => 'Arquivo considerado não compatível para este procedimento! Autorizados arquivos com extensões csv, txt, xls e xlsx. Apresentado o último enviado. ATENÇÃO!']);
        //     return redirect(route('LeituraArquivo.index'));
        // }

        if ($extension != 'csv') {
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

        if ($linha_7 == null) {
            session(['Lancamento' => 'Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento!']);
            return redirect(route('LeituraArquivo.index'));
        }

        ///////////////////////////// DADOS DA LINHA 12 PARA DEFINIR SITUAÇÃO
        $linha_8 = trim($planilha_ativa->getCell('B' . 8)->getValue());

        if ($linha_8 != 'Fatura em aberto, sujeita a alterações') {
            session([
                'Lancamento' =>
                    'Arquivo e ou ficheiro não identificado!
     Verifique se o mesmo está correto para este procedimento!
      A situação do extrato tem que ser: Fatura em aberto, sujeita a alterações. Neste arquivo está como situação: ' . $linha_8,
            ]);
            return redirect(route('LeituraArquivo.index'));
        }
        // dd('Pesquisar se já lançada!');

        $ContaCartao = null;
        $DespesaContaDebitoID = null;
        $CashBackContaCreditoID = '19271';

        $string = $linha_7;
        $parts = explode('-', $string);
        $result_linha7 = trim($parts[0]);
        $linhas1_7 = $linha_1 . '-' . $result_linha7;

        if ($linhas1_7 === 'SANDRA ELISA MAGOSSI FALCHI-4891.67XX.XXXX.9125') {
            $ContaCartao = '17457';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
        } elseif ($linhas1_7 === 'SANDRA ELISA MAGOSSI FALCHI-4891.67XX.XXXX.9919') {
            $ContaCartao = '17457';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
            } elseif ($linhas1_7 === 'SANDRA ELISA MAGOSSI FALCHI-5122.67XX.XXXX.0910') {
                $ContaCartao = '19468';
                $Empresa = 11;
                $DespesaContaDebitoID = '19426';
                $CashBackContaCreditoID = '19271';
                // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
            }elseif ($linhas1_7 === 'PEDRO ROBERTO FALCHI-4891.67XX.XXXX.2113') {
                    $ContaCartao = '17458';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
        } elseif ($linhas1_7 === 'PEDRO ROBERTO FALCHI-4891.67XX.XXXX.2915') {
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

        $novadata = array_slice($cellData, 10);
        // $novadata = array_slice($cellData, 152);

        ///// CONFERE SE EMPRESA BLOQUEADA
        $Empresa = '11';
        $EmpresaBloqueada = Empresa::find($Empresa);
        $Data_bloqueada = $EmpresaBloqueada->Bloqueiodataanterior->format('d/m/Y');

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        foreach ($novadata as $PegaLinha => $item) {
            $Data = $item[1];

            if ($Data == 'Histórico de Despesas') {
                session(['Lancamento' => 'Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento!']);
                return redirect(route('LeituraArquivo.index'));
            }
            $Descricao = $item[2];

            $linha = $PegaLinha + 10; ///// pega a linha atual da lista. Deve fazer a seguir:$PegaLinha => $item, conforme linha anterior

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
                $NumeroParcela = substr($Parcela, 1, 2);
                $QuantidadeParcela = substr($Parcela, 6, 2);
            }
            //  dd($Parcela,$NumeroParcela, $QuantidadeParcela, $Descricao );

            $Valor = $item[4];

            $valor_numerico = preg_replace('/[^0-9,.]/', '', $Valor);
            $Valor_sem_virgula = str_replace(',', '', $Valor);
            $Valor_sem_pontos_virgulas = str_replace('.', '', $Valor_sem_virgula);
            $valor_sem_simbolo = substr($Valor_sem_pontos_virgulas, 3); // Extrai a string sem o símbolo "R$"

            $valor_numerico = floatval($valor_sem_simbolo) / 100;
            $valor_formatado = number_format($valor_numerico, 2, '.', '');
            if ($valor_formatado == 0.0) {
                session([
                    'Lancamento' => 'ALGO ERRADO! VALOR 0.00. Linha:  ' . $linha + 1,
                ]);
                $Mensagem = 'ALGO ERRADO! VALOR 0.00. Linha:  ' . $linha + 1;
                // return redirect(route('LeituraArquivo.index'));

                continue;
            }

            $arraydatanova = compact('Data', 'Descricao', 'valor_formatado');
            // dd($Valor,$Valor_sem_virgula,$Valor_sem_pontos_virgulas,$valor_sem_simbolo ,$valor_numerico,$arraydatanova);
            $Extrato[] = null;

            $rowData = $cellData;
            //   dd($cellData);

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

                if ($Parcela) {
                    $DescricaoCompleta = $arraydatanova['Descricao'] . ' Parcela ' . $Parcela;
                } else {
                    $DescricaoCompleta = $arraydatanova['Descricao'];
                }
 $arraydatanova['Localizou'] = 'NAO' ;
                // dd($arraydatanova);

                $historico = Historicos::where('EmpresaID', $Empresa)
                    ->where('Descricao', 'like', '%' . trim($Descricao) . '%')
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

                if ($NumeroParcela !== null && $QuantidadeParcela !== null) {
                    if ($NumeroParcela > 1) {
                        // dd($NumeroParcela, $QuantidadeParcela, $linha );
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
                         $arraydatanova['Lancou registros'] = 'SIM' ;
                        }
                    }
                } else {
                    if ($historico == true) {
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
                         $arraydatanova['Criou com historico'] = 'SIM' ;
                    }

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
                         $arraydatanova['CRIOU SEM HISTORICO'] = 'SIM' ;
                        session(['Lancamento' => 'Lancamentos criados sem históricos!']);
                    }
                }

                // dd('fim');
                // session(['Lancamento' => 'Lancamentos criados!']);
                // dd('Criado lançamento com histórico', $historico);
            }
            $Extrato[] = $arraydatanova;
            // }///////////////retirar - liga com linha 658
        }
        if ($Mensagem) {
            $xMensagem = session('Lancamento') . ' Mensagem auxiliar: ' . $Mensagem;
            session([
                'Lancamento' => $xMensagem,
            ]);
            $Mensagem = null;
        }

        /////////////// filtra somente o Localizou = NAO
$registros = $Extrato;

$registrosNaoLocalizados = array_filter($registros, function ($registro) {
    return isset($registro['Localizou']) && $registro['Localizou'] === 'NAO';
});

// Resultado

// dd($registrosNaoLocalizados);
///////////////////////////////////////////////////////////////////////////////////



        //    $rowData = $novadata;
        // dd("Fim",$Descricao,$lancamento);

        // $rowKCData = $cellData;

        $rowData = ($registrosNaoLocalizados) ;

        return view('LeituraArquivo.SelecionaDatas', ['array' => $rowData]);
    }
}
