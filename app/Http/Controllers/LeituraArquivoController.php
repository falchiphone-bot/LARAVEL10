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

class LeituraArquivoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:LEITURA DE ARQUIVO - LISTAR'])->only('index');
        // $this->middleware(['permission:PLANO DE CONTAS - INCLUIR'])->only(['create', 'store']);
        // $this->middleware(['permission:PLANO DE CONTAS - EDITAR'])->only(['edit', 'update']);
        // $this->middleware(['permission:PLANO DE CONTAS - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:LEITURA DE ARQUIVO - LISTAR'])->only('SelecionaDatas');
        $this->middleware(['permission:LEITURA DE ARQUIVO - LISTAR'])->only('SelecionaDatasExtratoSicrediPJ');
        $this->middleware(['permission:LEITURA DE ARQUIVO - ENVIAR ARQUIVO PARA VISUALIZAR'])->only('SelecionaLinha');
    }

    /**
     * Display a listing of the resource.
     */
    // public function dashboard()
    // {
    //     return view('Moedas.dashboard');
    // }

    public function index()
    {
        $email = auth()->user()->email;
        $user = str_replace('@', '', $email);
        $user = str_replace('.', '', $user);
        $arquivosalvo = 'app/contabilidade/' . $user . '.prf';

        $caminho = storage_path($arquivosalvo);
        if (File::exists($caminho)) {
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

            // Array que irá armazenar os dados das células
            $cellData = [];

            // Loop para percorrer todas as células da planilha
            for ($row = 1; $row <= $lastRow; $row++) {
                for ($column = 1; $column <= $lastColumnIndex; $column++) {
                    // Obtém o valor da célula
                    $cellValue = $worksheet->getCellByColumnAndRow($column, $row)->getValue();

                    // Adiciona o valor da célula ao array $cellData
                    $cellData[$row][$column] = $cellValue;
                }
            }

            // dd($cellData);
            // return view('LeituraArquivo.index', ['cellData' => $cellData]);

            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            return view('LeituraArquivo.index', ['cellData' => $cellData]);
        } else {
            return view('LeituraArquivo.SomenteLinha');
        }
    }

    public function SelecionaDatas(Request $request)
    {
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
        $linha_12 = $planilha_ativa->getCell('B' . 12)->getValue();
        if ($linha_12 != 'Fechada') {
            session([
                'Lancamento' =>
                    'Arquivo e ou ficheiro não identificado!
     Verifique se o mesmo está correto para este procedimento!
      A situação do extrato tem que ser: Fechada' .
                    ' Neste arquivo está como situação: ' .
                    $linha_12,
            ]);
            return redirect(route('LeituraArquivo.index'));
        }

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
        } elseif ($linhas1_7 === 'PEDRO ROBERTO FALCHI-4891.67XX.XXXX.2113') {
            $ContaCartao = '17458';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
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

        $novadata = array_slice($cellData, 19);
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

            $linha = $PegaLinha + 20; ///// pega a linha atual da lista. Deve fazer a seguir:$PegaLinha => $item, conforme linha anterior

            if (strpos($Descricao, 'CREDITO CASH BACK') !== false) {
                //// se contiver, conter o texto na variável
                // dd($linha, $Descricao);
                continue;
            } elseif (strpos($Descricao, 'PAGAMENTO DEBITO EM') !== false) {
                //// se contiver, conter o texto na variável

                continue;
            }

            $carbon_data = \Carbon\Carbon::createFromFormat('d/m/Y', $Data);
            $linha_data_comparar = $carbon_data->format('Y-m-d');

            $carbon_data = \Carbon\Carbon::createFromFormat('d/m/Y', $Data_bloqueada);
            $Data_bloqueada_comparar = $carbon_data->format('Y-m-d');

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

            $Descricao = $item[2];
            $Parcela = $item[3];
            $Valor = $item[4];

            $valor_numerico = preg_replace('/[^0-9,.]/', '', $Valor);
            $Valor_sem_virgula = str_replace(',', '', $Valor);
            $Valor_sem_pontos_virgulas = str_replace('.', '', $Valor_sem_virgula);
            $valor_sem_simbolo = substr($Valor_sem_pontos_virgulas, 3); // Extrai a string sem o símbolo "R$"

            $valor_numerico = floatval($valor_sem_simbolo) / 100;
            $valor_formatado = number_format($valor_numerico, 2, '.', '');
            if ($valor_formatado == 0.0) {
                session([
                    'Lancamento' => 'ALGO ERRADO! VALOR 0.00. Linha:  ' . $linha,
                ]);
                return redirect(route('LeituraArquivo.index'));
            }
            $arraydatanova = compact('Data', 'Descricao', 'valor_formatado');
            // dd($Valor,$Valor_sem_virgula,$Valor_sem_pontos_virgulas,$valor_sem_simbolo ,$valor_numerico,$arraydatanova);

            $rowData = $cellData;

            $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                ->where('Valor', $valorString = $arraydatanova['valor_formatado'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaCreditoID', $ContaCartao)
                ->First();

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
                            $linha,
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

            if ($lancamento) {
                // dd($lancamento);
                session(['Lancamento' => 'Nenhum lançamento criado! Consultado todos os lançamentos iniciado na linha 20!']);
            } else {
                if ($Parcela) {
                    $DescricaoCompleta = $arraydatanova['Descricao'] . ' Parcela ' . $Parcela;
                } else {
                    $DescricaoCompleta = $arraydatanova['Descricao'];
                }

                // dd($arraydatanova);

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

                session(['Lancamento' => 'Lancamentos criados!']);
            }
        }

        $rowData = $cellData;
        //    $rowData = $novadata;
        return view('LeituraArquivo.SelecionaDatas', ['array' => $rowData]);
    }

    public function SelecionaDatasFaturaEmAberto(Request $request)
    {
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
        } elseif ($linhas1_7 === 'PEDRO ROBERTO FALCHI-4891.67XX.XXXX.2113') {
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
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
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
                    'Lancamento' => 'ALGO ERRADO! VALOR 0.00. Linha:  ' . $linha,
                ]);
                return redirect(route('LeituraArquivo.index'));
            }
            $arraydatanova = compact('Data', 'Descricao', 'valor_formatado');
            // dd($Valor,$Valor_sem_virgula,$Valor_sem_pontos_virgulas,$valor_sem_simbolo ,$valor_numerico,$arraydatanova);

            $rowData = $cellData;

            $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                ->where('Valor', $valorString = $arraydatanova['valor_formatado'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaCreditoID', $ContaCartao)
                ->First();

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
                            $linha,
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

            if ($lancamento && $NumeroParcela === null && $QuantidadeParcela === null) {
                // dd($lancamento);
                session([
                    'Lancamento' =>
                        'Nenhum lançamento criado!
                 Consultado todos os lançamentos iniciado na linha 11 e terminado na linha ' .
                        $linha +
                        1 .
                        '!',
                ]);
            } else {
                if ($request->criarlancamentosemhistorico == true) {
                    if ($Parcela) {
                        $DescricaoCompleta = $arraydatanova['Descricao'] . ' Parcela ' . $Parcela;
                    } else {
                        $DescricaoCompleta = $arraydatanova['Descricao'];
                    }

                    // dd($arraydatanova);
                    $historico = Historicos::where('EmpresaID', $Empresa)
                        ->where('Descricao', 'like', '%' . trim($Descricao) . '%')
                        ->where('ContaCreditoID', $ContaCartao)
                        ->first();

                    if ($historico) {
                        $DespesaContaDebitoID = $historico->ContaDebitoID;
                    } else {
                        if ($request->versemhistorico == true) {
                            dd('Sem histórico!', $historico, $arraydatanova, $Descricao, $ContaCartao, $DespesaContaDebitoID);
                        }
                    }

                    if ($request->criarlancamentosemhistorico == null) {
                        dd('histórico nulo!', $historico, $arraydatanova, $Descricao, $ContaCartao, $DespesaContaDebitoID);
                    }

                    if ($NumeroParcela !== null && $QuantidadeParcela !== null) {
                        if($NumeroParcela > 1){

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
                            echo $i, ' ';
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

                            }


                        }
                    } else {
                         
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
                    // dd('fim');
                    session(['Lancamento' => 'Lancamentos criados!']);
                }
            }
        }

        $rowData = $cellData;
        //    $rowData = $novadata;
        return view('LeituraArquivo.SelecionaDatas', ['array' => $rowData]);
    }

    public function SelecionaDatasExtratoSicrediPJ(Request $request)
    {
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        /////// aqui fica na pasta temporário /temp/    - apaga
        $path = $request->file('arquivo')->getRealPath();
        $file = $request->file('arquivo');
        $extension = $file->getClientOriginalExtension();

        $Complemento = $request->complemento;
        $name = $file->getClientOriginalName();
        $caminho = $path;
        if ($extension != 'txt' && $extension != 'TXT' && $extension != 'csv' && $extension != 'CSV' && $extension != 'xlsx' && $extension != 'XLSX' && $extension != 'xls' && $extension != 'XLS') {
            session(['Lancamento' => 'Arquivo considerado não compatível para este procedimento! Autorizados arquivos com extensões csv, txt, xls e xlsx. Apresentado o último enviado. ATENÇÃO!']);
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

        ///////////////////////////// DADOS DA LINHA 2 COLUNA 2 PARA DEFINIR CONTAS
        $linha_2_coluna_2 = $planilha_ativa->getCell('B' . 2)->getValue();
        ///////////////////////////// DADOS DA LINHA 4 COLUNA 2 PARA DEFINIR CONTAS
        $linha_4_coluna_2 = $planilha_ativa->getCell('B' . 4)->getValue();
        if ($linha_4_coluna_2 == null) {
            session(['Lancamento' => 'LINHA 4 COLUNA 2 =  NULA. Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento!']);
            return redirect(route('LeituraArquivo.index'));
        }

        ///////////////////////////// DADOS DA LINHA 7 PARA DEFINIR CONTAS
        $linha_7 = $planilha_ativa->getCell('A' . 7)->getValue();
        if ($linha_7 == null) {
            session(['Lancamento' => 'LINHA 7 = NULA. Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento!']);
            return redirect(route('LeituraArquivo.index'));
        }

        $NomeEmpresa = $linha_2_coluna_2;

        // $Conta = null;
        // $DespesaContaDebitoID = null;
        // $CashBackContaCreditoID = '19271';

        // $string = $linha_7;
        // $parts = explode('-', $string);
        // $result_linha7 = trim($parts[0]);
        // $linhas1_7 = $linha_1 . '-' . $result_linha7;

        if ($linha_4_coluna_2 === '54958-4') {
            $Conta = '5860';
            $Empresa = 5;
            $DespesaContaDebitoID = '19417';
            $CashBackContaCreditoID = '19417';
        } elseif ($linha_4_coluna_2 === '11382-9') {
            $Conta = '19099';
            $Empresa = 1027;
            $DespesaContaDebitoID = '19420';
            $CashBackContaCreditoID = '19420';
        } elseif ($linha_4_coluna_2 === '53998-8') {
            $Conta = '5971';
            $Empresa = 4;
            $DespesaContaDebitoID = '19421';
            $CashBackContaCreditoID = '19421';
        } elseif ($linha_4_coluna_2 === '72334-7') {
            $Conta = '5863';
            $Empresa = 1021;
            $DespesaContaDebitoID = '1942';
            $CashBackContaCreditoID = '19422';
        } elseif ($linha_4_coluna_2 === '72640-0') {
            $Conta = '5921';
            $Empresa = 3;
            $DespesaContaDebitoID = '19423';
            $CashBackContaCreditoID = '19423';
        } elseif ($linha_4_coluna_2 === '02069-4') {
            $Conta = '15314';
            $Empresa = 1025;
            $DespesaContaDebitoID = '19424';
            $CashBackContaCreditoID = '19424';
        } elseif ($linha_4_coluna_2 === '01409-3') {
            $Conta = '15295';
            $Empresa = 6;
            $DespesaContaDebitoID = '19425';
            $CashBackContaCreditoID = '19425';
        } elseif ($linha_4_coluna_2 === '72335-5') {
            $Conta = '15251';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19426';
        } elseif ($linha_4_coluna_2 === '72642-7') {
            $Conta = '15252';
            $Empresa = 11;
            $DespesaContaDebitoID = '19426';
            $CashBackContaCreditoID = '19426';
            // dd($Empresa,' - ',$Conta, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
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
        $EmpresaBloqueada = Empresa::find($Empresa);
        $Data_bloqueada = $EmpresaBloqueada->Bloqueiodataanterior->format('d/m/Y');

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $Saldo = 0;
        $DataInicial = null;

        foreach ($novadata as $PegaLinha => $item) {
            // echo $PegaLinha ."<br>";
            //     foreach ($item as $key => $value) {
            //         echo $value ." | ";
            //     }

            $Data = $item[1];

            if ($DataInicial == null) {
                $DataInicial = $Data;
            }
            $Descricao = $item[2];

            $linha = $PegaLinha + 10; ///// pega a linha atual da lista. Deve fazer a seguir:$PegaLinha => $item, conforme linha anterior

            if ($Data == '') {
                $SaldoAnterior = SaldoLancamentoHelper::Anterior($DataInicial, $Conta, $Empresa);
                $SaldoDia = SaldoLancamentoHelper::Dia($DataInicial, $Conta, $Empresa);

                $SaldoAtual = $SaldoAnterior + $SaldoDia;

                $DiferecaSaldo = number_format($Saldo - $SaldoAtual);

                if ($DiferecaSaldo == 0) {
                    $TextoConciliado = 'CONCILIAÇÃO COM EXATIDÃO DE SALDOS.';
                } else {
                    $TextoConciliado = 'SALDOS NÃO CONFEREM! VERIFIQUE!';
                }

                session([
                    'Lancamento' => 'Terminado na linha ' . $linha . '. Saldo no extrato bancário de: ' . number_format($Saldo, 2, '.', ',') . '.' . ' Saldo atual no sistema contábil de ' . number_format($SaldoAtual, 2, '.', ',') . ' = ' . $TextoConciliado,
                ]);

                return redirect(route('LeituraArquivo.index'));
            }
            if (strpos($Descricao, 'CREDITO CASH BACK') !== false) {
                //// se contiver, conter o texto na variável
                // dd($linha, $Descricao);
                continue;
            } elseif (strpos($Descricao, 'PAGAMENTO DEBITO EM') !== false) {
                //// se contiver, conter o texto na variável
                continue;
            } elseif (strpos(trim($Descricao), 'LIQ.COBRANCA SIMPLES') !== false) {
                //// se contiver, conter o texto na variável
                // $lancamento = Lancamento::where('DataContabilidade', $item[1])
                //     ->where('Valor', $item[4])
                //     ->where('EmpresaID', $Empresa)
                //     ->where('ContaDebitoID', $Conta)
                //     ->First();
                //     // DD($lancamento);
                //     session([
                //         'Lancamento' =>
                //             'Lançamento não encontrado no sistema contábil!'.
                //             '! Lançamento na linha no extrato de número '.
                //              $linha.
                //              " Valor de  ".$item[4]." com descrição de ".trim($Descricao).".",
                //     ]);
                //     return redirect(route('LeituraArquivo.index'));
            } elseif (strpos($Descricao, 'TARIFA COM R LIQUIDACAO') !== false) {
                //// se contiver, conter o texto na variável
                continue;
            }

            $carbon_data = \Carbon\Carbon::createFromFormat('d/m/Y', $Data);
            $linha_data_comparar = $carbon_data->format('Y-m-d');

            $carbon_data = \Carbon\Carbon::createFromFormat('d/m/Y', $Data_bloqueada);
            $Data_bloqueada_comparar = $carbon_data->format('Y-m-d');

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

            $Descricao = $item[2];
            $Parcela = $item[3];
            $Saldo = $item[5];
            $primeirosCincoDeParcela = substr($Parcela, 0, 5);
            // if ($primeirosCincoDeParcela == 'COB00') {
            //     continue;
            // }

            $Valor = $item[4];

            $valor_str = strval($Valor);

            $posicao_ponto = strpos($valor_str, '.');
            if ($posicao_ponto !== false) {
                $caracteres_decimal = strlen(substr($valor_str, $posicao_ponto + 1));
                if ($caracteres_decimal == 1) {
                    $Valor = $Valor . '0';
                }
                // dd("Número de caracteres após o ponto decimal: " . $caracteres_decimal." Valor:".$Valor);
            } else {
                $Valor = $Valor . '00';
                //    dd("O número não possui parte decimal. ".$Valor);
            }

            // $valorString = strval($Valor); // Converte o valor para uma string

            // if (strpos($valorString, '.') !== false) {
            //     // dd("O valor contém um ponto decimal.");
            // } else {
            //     // dd("O valor não contém um ponto decimal.");
            //     $Valor = $Valor * 100;
            // }

            $primeiro_caractere = substr($Valor, 0, 1);
            if ($primeiro_caractere !== '-') {
                $Valor_Positivo = true;
                $Valor_Negativo = false;
            } else {
                $Valor_Positivo = false;
                $Valor_Negativo = true;
            }

            $valor_numerico = preg_replace('/[^0-9,.]/', '', $Valor);
            $Valor_sem_virgula = str_replace(',', '', $Valor);
            $Valor_sem_pontos_virgulas = str_replace('.', '', $Valor_sem_virgula);
            // $valor_sem_simbolo = substr($Valor_sem_pontos_virgulas, 3); // Extrai a string sem o símbolo "R$"

            $valor_numerico = floatval($Valor_sem_pontos_virgulas) / 100;
            $valor_formatado = number_format($valor_numerico, 2, '.', '');

            $valor_formatado = abs($valor_formatado);

            $arraydatanova = compact('Data', 'Descricao', 'valor_formatado');
            //
            // if ($linha == 13) {
            //     // if (strpos(trim($Descricao), 'LIQ.COBRANCA SIMPLES') !== false) {
            //         dd($arraydatanova);
            //     // }

            // }

            $rowData = $cellData;
            $lancamento = null;

            if ($Valor_Positivo) {
                $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                    ->where('Valor', $valorString = $arraydatanova['valor_formatado'])
                    ->where('EmpresaID', $Empresa)
                    ->where('ContaDebitoID', $Conta)
                    ->First();

                // if ($lancamento == null) {
                //     session([
                //         'Lancamento' => 'Lançamento não encontrado no sistema contábil!' . '! Lançamento na linha no extrato de número ' . $linha . ' Valor de  ' . $item[4] . ' com descrição de ' . trim($Descricao) . '.',
                //     ]);
                //     return redirect(route('LeituraArquivo.index'));
                // }
            }

            if ($Valor_Negativo) {
                $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                    ->where('Valor', $valorString = $arraydatanova['valor_formatado'])
                    ->where('EmpresaID', $Empresa)
                    ->where('ContaCreditoID', $Conta)
                    ->First();
                // dd("LANCAMENTO NEGATIVO",$lancamento,$arraydatanova['Data'],$arraydatanova['valor_formatado'],$Empresa,$Conta );
            }

            if ($lancamento) {
                $idDoLancamento = $lancamento->ID;

                Lancamento::where('id', $idDoLancamento)->update([
                    'Conferido' => true,
                ]);

                session(['Lancamento' => 'Nenhum lançamento criado!']);
            } else {
                if ($Parcela) {
                    $DescricaoCompleta = $arraydatanova['Descricao'] . ' Parcela ' . $Parcela;
                } else {
                    $DescricaoCompleta = $arraydatanova['Descricao'];
                }

                // session([
                //     'Lancamento' => 'LANÇAMENTO NÃO ENCONTRADO NO SISTEMA CONTÁBIL. ' . ' Valor: ' . $valor_formatado . ' Descrição: ' . $DescricaoCompleta . ' Encontrado lançamento na linha ' . $linha . ' do extrato.',
                // ]);
                // return redirect(route('LeituraArquivo.index'));

                if ($Valor_Positivo) {
                    $historico = Historicos::where('EmpresaID', $Empresa)
                        ->where('Descricao', 'like', '%' . trim($Descricao) . '%')
                        ->where('ContaDebitoID', $Conta)
                        ->first();

                    if ($historico !== true) {
                        $historico = Historicos::where('EmpresaID', $Empresa)
                            ->where('Descricao', 'like', '%' . substr(trim($Descricao), 0, 30) . '%')
                            ->where('ContaDebitoID', $Conta)
                            ->first();
                    }
                } elseif ($Valor_Negativo) {
                    $historico = Historicos::where('EmpresaID', $Empresa)
                        ->where('Descricao', 'like', '%' . trim($Descricao) . '%')
                        ->where('ContaCreditoID', $Conta)
                        ->first();
                    if ($historico == null) {
                        $historico = Historicos::where('EmpresaID', $Empresa)
                            ->where('Descricao', 'like', '%' . substr(trim($Descricao), 0, 30) . '%')
                            ->where('ContaCreditoID', $Conta)
                            ->first();
                        // dd(substr($Descricao, 0, 30),$historico,trim($Descricao), $Conta,$Empresa,'1088');
                    }
                }
                //  dd($historico,trim($Descricao), $Conta,$Empresa,'1094');

                $Conferir_Bloqueio = false;
                if ($request->vercriarlancamentocomhistorico) {
                    if ($historico) {
                        exit('Lançamento sendo criado com histórico! ' . $historico);
                    } else {
                        exit('Lançamento sendo criado sem histórico pré programado! ');
                    }
                }

                if ($historico) {
                    if ($Conferir_Bloqueio == true) {
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
                                    $linha,
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

                        $data_conta_credito_bloqueio = $lancamento->ContaCredito->Bloqueiodataanterior;
                        if ($data_conta_credito_bloqueio == null) {
                            session([
                                'Lancamento' =>
                                    'Conta CRÉDITO: ' .
                                    $lancamento->ContaCredito->PlanoConta->Descricao .
                                    ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio
                                                                da conta para seguir este procedimento. Bloqueada para até NULA' .
                                    '! Encontrado lançamento na linha ' .
                                    $linha,
                            ]);
                            return redirect(route('LeituraArquivo.index'));
                        }

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

                    Lancamento::create([
                        'Valor' => ($valorString = $valor_formatado),
                        'EmpresaID' => $Empresa,
                        'ContaDebitoID' => $historico->ContaDebitoID,
                        'ContaCreditoID' => $historico->ContaCreditoID,
                        'Descricao' => $Parcela,
                        'Usuarios_id' => auth()->user()->id,
                        'DataContabilidade' => $Data,
                        'Conferido' => true,
                        'HistoricoID' => $historico->ID,
                    ]);
                } else {
                    if ($request->criarlancamentosemhistorico) {
                        if ($request->vercriarlancamento) {
                            exit('Necessito criar lançamento sem histórico pré programado! ');
                        }

                        if ($Valor_Positivo) {
                            $ContaDebito = $Conta;
                            $ContaCredito = $DespesaContaDebitoID;
                        }
                        if ($Valor_Negativo) {
                            $ContaDebito = $DespesaContaDebitoID;
                            $ContaCredito = $Conta;
                        }

                        Lancamento::create([
                            'Valor' => ($valorString = $valor_formatado),
                            'EmpresaID' => $Empresa,
                            'ContaDebitoID' => $ContaDebito,
                            'ContaCreditoID' => $ContaCredito,
                            'Descricao' => $DescricaoCompleta,
                            'Usuarios_id' => auth()->user()->id,
                            'DataContabilidade' => $Data,
                            'Conferido' => false,
                            'HistoricoID' => null,
                        ]);
                    }
                }

                session(['Lancamento' => 'Lancamentos criados!']);
            }
        }

        // $rowData = $cellData;
        //    $rowData = $novadata;
        return view('LeituraArquivo.SelecionaDatas', ['array' => $rowData]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Moedas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MoedaValoresCreateRequest $request)
    {
        $moedas = $request->all();
        //dd($dados);

        Moeda::create($moedas);

        return redirect(route('Moedas.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    }

    public function SelecionaLinha(Request $request)
    {
        /////// aqui fica na pasta temporário /temp/    - apaga
        $path = $request->file('arquivo')->getRealPath();

        $file = $request->file('arquivo');
        $Complemento = $request->complemento;
        $name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $caminho_arquivo = $path;

        if ($extension != 'txt' && $extension != 'csv' && $extension != 'xlsx') {
            session(['Lancamento' => 'Arquivo considerado não compatível para este procedimento! Apresentado o último enviado. ATENÇÃO!']);
            return redirect(route('LeituraArquivo.index'));
        }
        $email = auth()->user()->email;
        $user = str_replace('@', '', $email);
        $user = str_replace('.', '', $user);
        $arquivosalvo = 'app/contabilidade/' . $user . '.prf';
        copy($path, storage_path($arquivosalvo));

        // Carregar o arquivo da planilha
        $planilha = IOFactory::load($caminho_arquivo);

        // Obter a planilha ativa (por exemplo, a primeira planilha)
        $planilha_ativa = $planilha->getActiveSheet();
        ///////////////////////////// DADOS DA LINHA 1 PARA DEFINIR CONTAS
        $linha_1 = $planilha_ativa->getCell('B' . 1)->getValue();

        ///////////////////////////// DADOS DA LINHA 7 PARA DEFINIR CONTAS
        $linha_7 = $planilha_ativa->getCell('A' . 7)->getValue();

        $Empresa = '11';

        ///// CONFERE SE EMPRESA BLOQUEADA
        $Empresa = '11';
        $EmpresaBloqueada = Empresa::find($Empresa);
        $Data_bloqueada = $EmpresaBloqueada->Bloqueiodataanterior->format('d/m/Y');

        /////////////////////////////////////////////////////////////////////

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
            $DespesaContaDebitoID = '15372';
            $CashBackContaCreditoID = '19271';
            // dd($Empresa,' - ',$ContaCartao, ' - ',$DespesaContaDebitoID, $CashBackContaCreditoID);
        } else {
            session(['Lancamento' => 'Arquivo e ou ficheiro não identificado! Verifique o mesmo está correto para este procedimento!']);
            return redirect(route('LeituraArquivo.SomenteLinha'));
        }

        // Número da linha que você deseja obter (por exemplo, linha 3)
        $numero_linha = $request->linha;

        // Obter os dados da linha desejada
        $linha_data = $planilha_ativa->getCell('A' . $numero_linha)->getValue();

        $linha_descricao = $planilha_ativa->getCell('B' . $numero_linha)->getValue();
        $linha_parcela = $planilha_ativa->getCell('C' . $numero_linha)->getValue();
        $linha_valor = $planilha_ativa->getCell('D' . $numero_linha)->getValue();

        $primeiro_caractere = substr($linha_valor, 0, 1);

        $valor_sem_simbolo = '';
        if ($primeiro_caractere === '-') {
            $valor_sem_simbolo = substr($linha_valor, 3); // Extrai a string sem o símbolo R$
            // dd($valor_sem_simbolo);
        } else {
            // dd(O valor não começa com R.);
        }

        $valor_numerico = preg_replace('/[^0-9,.]/', '', $linha_valor);
        $valor_numerico = str_replace(',', '.', $valor_numerico);
        $valor_numerico = floatval($valor_numerico);
        $linha_valor_formatado = number_format($valor_numerico, 2, '.', '');

        $arraydatanova = compact('linha_data', 'linha_descricao', 'linha_parcela', 'linha_valor_formatado', 'numero_linha');

        $SeValor = floatval($arraydatanova['linha_valor_formatado']);

        if ($SeValor == null) {
            session(['Lancamento' => 'A linha ' . $numero_linha . ' não possui valor']);
            return redirect(route('LeituraArquivo.index'));
        }

        $carbon_data = \Carbon\Carbon::createFromFormat('d/m/Y', $linha_data);
        $linha_data_comparar = $carbon_data->format('Y-m-d');

        $carbon_data = \Carbon\Carbon::createFromFormat('d/m/Y', $Data_bloqueada);
        $Data_bloqueada_comparar = $carbon_data->format('Y-m-d');

        if ($linha_data_comparar <= $Data_bloqueada_comparar) {
            session(['Lancamento' => 'Empresa bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio da empresa para seguir este procedimento. Bloqueada para até ' . $EmpresaBloqueada->Bloqueiodataanterior->format('d/m/Y') . '!']);

            return redirect(route('LeituraArquivo.index'));
        }

        if ($primeiro_caractere === '-') {
            $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['linha_data'])
                ->where('Valor', $valorString = $arraydatanova['linha_valor_formatado'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaDebitoID', $ContaCartao)
                ->First();
        } else {
            $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['linha_data'])
                ->where('Valor', $valorString = $arraydatanova['linha_valor_formatado'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaCreditoID', $ContaCartao)
                ->First();
        }

        $dataLancamento_carbon = Carbon::createFromDate($lancamento->DataContabilidade);
        $dataLancamento = $dataLancamento_carbon->format('Y/m/d');
        if ($lancamento) {
            $data_conta_debito_bloqueio = $lancamento->ContaDebito->Bloqueiodataanterior;
            if ($data_conta_debito_bloqueio->greaterThanOrEqualTo($dataLancamento)) {
                session(['Lancamento' => 'Conta DÉBITO: ' . $lancamento->ContaDebito->PlanoConta->Descricao . ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio da conta para seguir este procedimento. Bloqueada para até ' . $data_conta_debito_bloqueio->format('d/m/Y') . '!']);
                return redirect(route('LeituraArquivo.index'));
            }
        }

        if ($lancamento) {
            $data_conta_credito_bloqueio = $lancamento->ContaCredito->Bloqueiodataanterior;
            if ($data_conta_credito_bloqueio->greaterThanOrEqualTo($dataLancamento)) {
                session(['Lancamento' => 'Conta CRÉDITO: ' . $lancamento->ContaCredito->PlanoConta->Descricao . ' bloqueada no sistema para o lançamento solicitado! Deverá desbloquear a data de bloqueio da conta para seguir este procedimento. Bloqueada para até ' . $data_conta_credito_bloqueio->format('d/m/Y') . '!']);

                return redirect(route('LeituraArquivo.index'));
            }
        }

        if ($lancamento) {
            // dd($lancamento);
            session(['Lancamento' => 'Nenhum lançamento criado!']);
        } else {
            if ($linha_parcela) {
                $DescricaoCompleta = $arraydatanova['linha_descricao'] . ' Parcela ' . $linha_parcela;
            } else {
                $DescricaoCompleta = $arraydatanova['linha_descricao'];
            }

            if ($primeiro_caractere === '-') {
                //// valor negativo ----- é estorno ou retorno cash back
                Lancamento::create([
                    'Valor' => ($valorString = $arraydatanova['linha_valor_formatado']),
                    'EmpresaID' => $Empresa,
                    'ContaDebitoID' => $ContaCartao,
                    'ContaCreditoID' => $CashBackContaCreditoID,
                    'Descricao' => $DescricaoCompleta,
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' => $linha_data,
                    'HistoricoID' => '',
                ]);
            } else {
                /////////   lança a despesa
                Lancamento::create([
                    'Valor' => ($valorString = $arraydatanova['linha_valor_formatado']),
                    'EmpresaID' => $Empresa,
                    'ContaDebitoID' => $DespesaContaDebitoID,
                    'ContaCreditoID' => $ContaCartao,
                    'Descricao' => $DescricaoCompleta,
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' => $linha_data,
                    'HistoricoID' => '',
                ]);
            }
            session(['Lancamento' => 'Lancamentos criados!']);
        }

        return redirect(route('dashboardContabilidade'));
    }
}
