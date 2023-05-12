<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Lancamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Faker\Core\DateTime;
use Illuminate\Support\Collection;
use PhpParser\Node\Stmt\Foreach_;

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

    public function SelecionaDatas()
    {
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Obter a planilha ativa (por exemplo, a primeira planilha)
        $planilha_ativa = $spreadsheet->getActiveSheet();
        ///////////////////////////// DADOS DA LINHA 1 PARA DEFINIR CONTAS
        $linha_1 = $planilha_ativa->getCell('B' . 1)->getValue();

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
        } else {
            session(['Lancamento' => 'Arquivo e ou ficheiro não identificado! Verifique o mesmo está correto para este procedimento!']);
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

        foreach ($novadata as $item) {
            $Data = $item[1];
            if ($Data <= $Data_bloqueada) {
                session(['Lancamento' => 'Empresa bloqueada no sistema para o primeiro lançamento encontrado! Deverá desbloquear a data de bloqueio da empresa para seguir este procedimento. Bloqueada para até ' . $EmpresaBloqueada->Bloqueiodataanterior->format('d/m/Y') . '!']);
                return redirect(route('LeituraArquivo.index'));
            }

            $Descricao = $item[2];
            $Parcela = $item[3];
            $Valor = $item[4];

            $valor_numerico = preg_replace('/[^0-9,.]/', '', $Valor);
            $valor_numerico = str_replace(',', '.', $valor_numerico);
            $valor_numerico = floatval($valor_numerico);
            $valor_formatado = number_format($valor_numerico, 2, '.', '');

            $arraydatanova = compact('Data', 'Descricao', 'valor_formatado');

            // dd($arraydatanova['Descricao']);

            $rowData = $cellData;

            $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                ->where('Valor', $valorString = $arraydatanova['valor_formatado'])
                ->where('EmpresaID', '11')
                ->where('ContaCreditoID', '17457')
                ->First();

            if ($lancamento) {
                // dd($lancamento);
                session(['Lancamento' => 'Nenhum lançamento criado!']);
            } else {
                if ($Parcela) {
                    $DescricaoCompleta = $arraydatanova['Descricao'] . ' Parcela ' . $Parcela;
                } else {
                    $DescricaoCompleta = $arraydatanova['Descricao'];
                }

                Lancamento::create([
                    'Valor' => ($valorString = $arraydatanova['valor_formatado']),
                    'EmpresaID' => '11',
                    'ContaDebitoID' => '15372',
                    'ContaCreditoID' => '17457',
                    'Descricao' => $DescricaoCompleta,
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' => $Data,
                    'HistoricoID' => '',
                ]);

                $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                    ->where('EmpresaID', '11')
                    ->where('ContaCreditoID', '17457')
                    ->First();
                // dd($lancamento);

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
        $caminho_arquivo = storage_path('app/contabilidade/sicredi.csv');

        // Carregar o arquivo da planilha
        $planilha = IOFactory::load($caminho_arquivo);

        // Obter a planilha ativa (por exemplo, a primeira planilha)
        $planilha_ativa = $planilha->getActiveSheet();
        ///////////////////////////// DADOS DA LINHA 1 PARA DEFINIR CONTAS
        $linha_1 = $planilha_ativa->getCell('B' . 1)->getValue();

        ///////////////////////////// DADOS DA LINHA 7 PARA DEFINIR CONTAS
        $linha_7 = $planilha_ativa->getCell('A' . 7)->getValue();

        $Empresa = '11';
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
            $valor_sem_simbolo = substr($linha_valor, 4); // Extrai a string sem o símbolo "R$"
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
