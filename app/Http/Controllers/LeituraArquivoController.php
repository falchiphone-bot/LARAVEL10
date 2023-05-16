<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\Conta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Faker\Core\DateTime;
use Illuminate\Support\Collection;
use PhpParser\Node\Stmt\Foreach_;
use Livewire\Component;

use function Pest\Laravel\get;

class LeituraArquivoController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware(['permission:MOEDAS - LISTAR'])->only('index');
        // $this->middleware(['permission:MOEDAS - INCLUIR'])->only(['create', 'store']);
        // $this->middleware(['permission:MOEDAS - EDITAR'])->only(['edit', 'update']);
        // $this->middleware(['permission:MOEDAS - VER'])->only(['edit', 'update']);
        // $this->middleware(['permission:MOEDAS - EXCLUIR'])->only('destroy');
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
        $caminho = storage_path('app/contabilidade/sicredi.csv');

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
    }

    public function SelecionaDatas(Request $request)
    {
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // $caminho = storage_path('app/contabilidade/sicredi.csv');

        /////// aqui fica na pasta temporário /temp/    - apaga
        $path = $request->file('arquivo')->getRealPath();
        $file = $request->file('arquivo');
        $extension = $file->getClientOriginalExtension();

        $Complemento = $request->complemento;
        $name = $file->getClientOriginalName();
        $caminho = $path;
        if ($extension != 'txt' && $extension != 'csv' && $extension != 'xlsx' && $extension != 'xls') {
            session(['Lancamento' => 'Arquivo considerado não compatível para este procedimento! Autorizados arquivos com extensões csv, txt, xls e xlsx. Apresentado o último enviado. ATENÇÃO!']);
            return redirect(route('LeituraArquivo.index'));
        }
        copy($path, storage_path('app/contabilidade/sicredi.csv'));

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
        } elseif ($linhas1_7 === 'PEDRO ROBERTO FALCHI-4891.67XX.XXXX.2113') {
            $ContaCartao = '17458';
            $Empresa = 11;
            $DespesaContaDebitoID = '15354';
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
                session(['Lancamento' => 'Nenhum lançamento criado!']);
            } else {
                if ($Parcela) {
                    $DescricaoCompleta = $arraydatanova['Descricao'] . ' Parcela ' . $Parcela;
                } else {
                    $DescricaoCompleta = $arraydatanova['Descricao'];
                }
                dd($linha_4_coluna_2, 'Linha 303 do código');

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
        if ($extension != 'txt' && $extension != 'csv' && $extension != 'xlsx' && $extension != 'xls') {
            session(['Lancamento' => 'Arquivo considerado não compatível para este procedimento! Autorizados arquivos com extensões csv, txt, xls e xlsx. Apresentado o último enviado. ATENÇÃO!']);
            return redirect(route('LeituraArquivo.index'));
        }
        copy($path, storage_path('app/contabilidade/sicredi.csv'));

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

        $novadata = array_slice($cellData, 11);
        // $novadata = array_slice($cellData, 152);

        ///// CONFERE SE EMPRESA BLOQUEADA
        $EmpresaBloqueada = Empresa::find($Empresa);
        $Data_bloqueada = $EmpresaBloqueada->Bloqueiodataanterior->format('d/m/Y');

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        foreach ($novadata as $PegaLinha => $item) {
            $Data = $item[1];



            $Descricao = $item[2];

            $linha = $PegaLinha + 12; ///// pega a linha atual da lista. Deve fazer a seguir:$PegaLinha => $item, conforme linha anterior
            if($Data == '')
            {
                session([
                    'Lancamento'
                     => 'Terminado: '
                    .$linha,
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

            $valorString = strval($Valor); // Converte o valor para uma string

            if (strpos($valorString, '.') !== false) {
                // dd("O valor contém um ponto decimal.");
            } else {
                // dd("O valor não contém um ponto decimal.");
                $Valor = $Valor * 100;
            }

            $primeiro_caractere = substr($Valor, 0, 1);
            if ($primeiro_caractere !== '-') {
                $Valor_Positivo = true;
                $Valor_Negativo = false;
            }else{
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

            // dd($Valor,$Valor_sem_virgula,
            //             $Valor_sem_pontos_virgulas,
            //             $valor_numerico,$arraydatanova,  $Descricao,$Parcela,$linha  );

            $rowData = $cellData;
            $lancamento = null;
            if($Valor_Positivo){
                $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                ->where('Valor', $valorString = $arraydatanova['valor_formatado'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaDebitoID', $Conta)
                ->First();
            //    dd("LANCAMENTO POSITIVO",$lancamento,$arraydatanova['Data'],$arraydatanova['valor_formatado'],$Empresa,$Conta );

            }

            if($Valor_Negativo){
                 $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                ->where('Valor', $valorString = $arraydatanova['valor_formatado'])
                ->where('EmpresaID', $Empresa)
                ->where('ContaCreditoID', $Conta)
                ->First();
                // dd("LANCAMENTO NEGATIVO",$lancamento,$arraydatanova['Data'],$arraydatanova['valor_formatado'],$Empresa,$Conta );

            }




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

            if ($lancamento) {
                $idDoLancamento = $lancamento->ID;

                Lancamento::where('id', $idDoLancamento)->update([
                    'Conferido' => true,
                    ]);
                    // dd($lancamento);
                session(['Lancamento' => 'Nenhum lançamento criado!']);
            } else {
                if ($Parcela) {
                    $DescricaoCompleta = $arraydatanova['Descricao'] . ' Parcela ' . $Parcela;
                } else {
                    $DescricaoCompleta = $arraydatanova['Descricao'];
                }
                // dd($linha_4_coluna_2, 'Linha 303 do código');

                    session([
                        'Lancamento' => 'LANÇAMENTO NÃO ENCONTRADO NO SISTEMA CONTÁBIL. ' . ' Valor: ' . $valor_formatado . ' Descrição: ' . $DescricaoCompleta . ' Encontrado lançamento na linha ' . $linha . ' do extrato.',
                    ]);
                    return redirect(route('LeituraArquivo.index'));


                // dd($arraydatanova);

                Lancamento::create([
                    'Valor' => ($valorString = $valor_formatado),
                    'EmpresaID' => $Empresa,
                    'ContaDebitoID' => $DespesaContaDebitoID,
                    'ContaCreditoID' => $Conta,
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
        // Caminho do arquivo da planilha
        // // $caminho_arquivo = storage_path('app/contabilidade/sicredi.csv');

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
        copy($path, storage_path('app/contabilidade/sicredi.csv'));

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
            $valor_sem_simbolo = substr($linha_valor, 3); // Extrai a string sem o símbolo "R$"
            // dd($valor_sem_simbolo);
        } else {
            // dd("O valor não começa com 'R'.");
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
