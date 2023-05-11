<?php

namespace App\Http\Controllers;

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

        foreach ($novadata as $item) {
            $Data = $item[1];
            $Descricao = $item[2];
            $Parcela = $item[3];
            $Valor =$item[4];

            $valor_numerico = preg_replace('/[^0-9,.]/', '', $Valor);
            $valor_numerico = str_replace(',', '.', $valor_numerico);
            $valor_numerico = floatval($valor_numerico);
            $valor_formatado = number_format($valor_numerico, 2, '.', '');




            $arraydatanova = compact('Data', 'Descricao', 'valor_formatado');

// dd($arraydatanova['Descricao']);

            $rowData = $cellData;

            $lancamento = Lancamento::where('DataContabilidade', $arraydatanova['Data'])
                ->where('EmpresaID', '11')
                ->where('ContaCreditoID', '17457')
                ->First();

            if ($lancamento) {
                // dd($lancamento);
                session(['Lancamento' => 'Nenhum lançamento criado!']);
            } else {


                if($Parcela)
                    {
                        $DescricaoCompleta =  $arraydatanova['Descricao'].' Parcela '. $Parcela;
                    }
                    else{
                        $DescricaoCompleta =  $arraydatanova['Descricao'];
                    }



                Lancamento::create([
                    'Valor' => $valorString = $arraydatanova['valor_formatado'],
                    'EmpresaID' => '11',
                    'ContaDebitoID' => '15372',
                    'ContaCreditoID' => '17457',
                    'Descricao' => $DescricaoCompleta,
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' =>  $Data,
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
        $cadastro = Moeda::find($id);
        return view('Moedas.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $moedas = Moeda::find($id);
        // dd($cadastro);

        return view('Moedas.edit', compact('moedas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cadastro = Moeda::find($id);

        $cadastro->fill($request->all());

        $cadastro->save();

        return redirect(route('Moedas.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $moedas = Moeda::find($id);

        $moedas->delete();
        return redirect(route('Moedas.index'));
    }
}
