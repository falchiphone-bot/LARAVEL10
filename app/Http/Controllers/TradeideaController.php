<?php

namespace App\Http\Controllers;


use App\Models\Tradeidea;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Faker\Core\DateTime;
use Illuminate\Support\Collection;
use PhpParser\Node\Stmt\Foreach_;
use Livewire\Component;
use PhpParser\Node\Stmt\Continue_;
use Ramsey\Uuid\Type\Decimal;
use Illuminate\Support\Facades\File;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Auth;


class TradeideaController  extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:TRADEIDEA - LISTAR'])->only('index');
        $this->middleware(['permission:TRADEIDEA - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:TRADEIDEA - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:TRADEIDEA - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:TRADEIDEA - EXCLUIR'])->only('destroy');
    }

    public function index()
    {
       $model = Tradeidea::OrderBy('created_at')->get();


        return view('Tradeidea.index',compact('model'));
    }


    public function create()
    {
        // return view('RedeSocial.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RedeSocialCreateRequest $request)
    {
        // $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = RedeSocial::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
        //     return redirect(route('RedeSocial.index'));
        // }

        // $request['user_created'] = Auth::user()->email;

        // $model= $request->all();


        // RedeSocial::create($model);
        // session(['success' => "TIPO DE ESPORTE:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        // return redirect(route('RedeSocial.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $cadastro = RedeSocial::find($id);
        // return view('RedeSocial.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // $model= RedeSocial::find($id);


        // return view('RedeSocial.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = RedeSocial::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
        //     return redirect(route('RedeSocial.index'));
        // }


        // $cadastro = RedeSocial::find($id);


        // $request['user_updated'] = Auth::user()->email;
        // $cadastro->fill($request->all()) ;


        // $cadastro->save();


        // return redirect(route('RedeSocial.index'));
    }


    public function destroy(Request $request, string $id)
    {

        $model= Tradeidea::find($id);

        $model->delete();

        session(['success' => "REGISTRO EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('Tradeidea.index'));

    }

    public function ImportaArquivoExcelTradeIdea(Request $request)
    {

        /////// aqui fica na pasta temporário /temp/    - apaga
        $path = $request->file('arquivo')->getRealPath();
        $file = $request->file('arquivo');
        $extension = $file->getClientOriginalExtension();

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




                                                                // ///////////////////////////// DADOS DA LINHA 1 PARA DEFINIR CONTAS
                                                                // $linha_1 = $planilha_ativa->getCell('B' . 1)->getValue();

                                                                // ///////////////////////////// DADOS DA LINHA 2 COLUNA 2 PARA DEFINIR CONTAS
                                                                // $linha_2_coluna_2 = $planilha_ativa->getCell('B' . 2)->getValue();
                                                                // ///////////////////////////// DADOS DA LINHA 4 COLUNA 2 PARA DEFINIR CONTAS
                                                                // $linha_4_coluna_2 = $planilha_ativa->getCell('B' . 4)->getValue();
                                                                // if ($linha_4_coluna_2 == null) {
                                                                //     session(['Lancamento' => 'LINHA 4 COLUNA 2 =  NULA. Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento! Erro L551']);
                                                                //     return redirect(route('LeituraArquivo.index'));
                                                                // }

                                                                // ///////////////////////////// DADOS DA LINHA 7 PARA DEFINIR CONTAS
                                                                // $linha_7 = $planilha_ativa->getCell('A' . 7)->getValue();
                                                                // if ($linha_7 == null) {
                                                                //     session(['Lancamento' => 'LINHA 7 = NULA. Arquivo e ou ficheiro não identificado! Verifique se o mesmo está correto para este procedimento! Erro L558']);
                                                                //     return redirect(route('LeituraArquivo.index'));
                                                                // }





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

        $novadata = array_slice($cellData, 1);



        $Dados1 = array();

foreach ($novadata as $item) {
    $Cliente = $item[1];
    $Assessor = $item[2];
    $Id_tradeidea = $item[3];
    $Tradeidea = $item[4];
    $Analista = $item[5];
    $Valor_aportado = $item[6];
    $Valor_liquidado = $item[7];
    $Lucro_prejuizo = $item[8];
    $Quantidade = $item[9];
    $Preco_entrada = $item[10];
    $Entrada = $item[11];
    $Preco_encerramento = $item[12];
    $Encerramento = $item[13];
     $Motivo = $item[14];

    // Crie um novo array associativo com os valores de Cliente e Assessor

    if ($Cliente !== null) {
            $novoItem = array(
                "cliente" => $Cliente,
                "assessor" => $Assessor,
                "Id_Tradeidea" => $Id_tradeidea,
                "tradeidea" => $Tradeidea,
                "analista" => $Analista,
                "valor_aportado" => $Valor_aportado,
                "valor_liquidado" => $Valor_liquidado,
                "lucro_prejuizo" => $Lucro_prejuizo,
                "quantidade" => $Quantidade,
                "preco_entrada" => $Preco_entrada,
                "entrada" => $Entrada,
                "preco_encerramento" => $Preco_encerramento,
                "encerramento" => $Encerramento,
                "motivo" => $Motivo,
            );
           $Dados1[] = $novoItem;
    }



}



        $model = $Dados1;
        return view('Tradeidea.index',  compact('model'));
    }

      public function salvarTradeidea(Request $request)
    {
        $modeloCompleto = json_decode($request->input('modelo_completo'), true);


        // dd($modeloCompleto);
        // Agora você pode acessar as propriedades da model como $modeloCompleto['Cliente'], $modeloCompleto['Assessor'], etc.

        // Faça a lógica de salvar no banco de dados aqui

        // Redirecione de volta para a página ou faça qualquer ação necessária

        foreach ($modeloCompleto as $model) {
            $model['user_created'] = Auth::user()->email;

            $id = $model['id']??null;
            $cliente = $model['cliente'];
            $entrada = $model['entrada'];
            $quantidade = $model['quantidade'];
            $preco_entrada = $model['preco_entrada'];
            $Id_Tradeidea = $model['Id_Tradeidea'];
            $assessor = $model['assessor'];
            $valor_aportado = $model['valor_aportado'];


            $Existir =Tradeidea::
              where('cliente',$cliente)
            ->where('entrada',$entrada)
            ->where('preco_entrada',$preco_entrada)
            ->where('quantidade',$quantidade)
            ->where('assessor',$assessor)
            ->where('valor_aportado',$valor_aportado)
            ->where('Id_Tradeidea',$Id_Tradeidea)
            ->first();

            if($Existir){
                $tradeidea = Tradeidea::findOrFail($id);
                $tradeidea->update($model);
                    // Tradeidea::where('Id_Tradeidea', $id)->update($modeloCompleto);
                session(['error' => "Registro não incluído, pois já existe! NADA INCLUÍDO, porém alterado! "]);
                // return redirect(route('Tradeidea.index'));
            }
            else
            {
                 Tradeidea::create($model);
                 session(['success' => "REGISTROS INCLUÍDOS!"]);
            }



         }
            // dd($Existir, $model );



            return view('Tradeidea.index',compact('model'));
    }




}
