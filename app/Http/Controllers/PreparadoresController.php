<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArquivoPreparadoresCreateRequest;
use App\Http\Requests\PreparadoresCreateRequest;
use App\Models\CargoProfissional;
use App\Models\FuncaoProfissional;
use App\Models\LancamentoDocumento;
use App\Models\Preparadores;
use App\Models\PreparadoresArquivo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Exports\PreparadoresExport;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;


class PreparadoresController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:PREPARADORES - LISTAR'])->only(['index']);
        $this->middleware(['permission:PREPARADORES - EXPORTAR'])->only(['export','exportXlsx','exportPdf']);
        $this->middleware(['permission:PREPARADORES - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:PREPARADORES - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:PREPARADORES - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:PREPARADORES - EXCLUIR'])->only('destroy');
    }


    public function index(Request $request)
    {
        // Limpar filtros salvos
        if ($request->boolean('clear')) {
            Session::forget('preparadores.index.filters');
            return redirect()->route('Preparadores.index');
        }

        // Carregar filtros salvos se nenhum parâmetro informado
        $saved = Session::get('preparadores.index.filters', []);
        $incomingFilters = $request->only(['nome','email','telefone','licencaCBF','per_page','sort','dir']);
        $hasIncoming = collect($incomingFilters)->filter(function($v){ return $v !== null && $v !== ''; })->isNotEmpty();
        if (!$hasIncoming && !empty($saved)) {
            return redirect()->route('Preparadores.index', $saved);
        }

        // Salvar filtros se solicitado
        if ($request->boolean('remember')) {
            Session::put('preparadores.index.filters', $incomingFilters);
        }

        $query = Preparadores::query();

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . trim($request->input('email')) . '%');
        }
        if ($request->filled('telefone')) {
            $query->where('telefone', 'like', '%' . trim($request->input('telefone')) . '%');
        }
        if ($request->filled('licencaCBF')) {
            $query->where('licencaCBF', 'like', '%' . trim($request->input('licencaCBF')) . '%');
        }

        // Ordenação
        $allowedSorts = ['nome','email','telefone','licencaCBF'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Total antes de paginar
        $total = (clone $query)->count();

        // Paginação
        $perPage = (int)($request->input('per_page', 25));
        if ($perPage <= 0) { $perPage = 25; }

        $model = $query->orderBy($sort, $dir)
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view('Preparadores.index', compact('model','total','perPage','sort','dir'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Preparadores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PreparadoresCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Preparadores::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('Preparadores.index'));
        }


        $model= $request->all();


        Preparadores::create($model);
        session(['success' => "Preparador:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('Preparadores.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Preparadores::find($id);
        return view('Preparadores.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $model= Preparadores::find($id);

        $cargoprofissional = CargoProfissional::OrderBy('nome')->get();
        $funcaoprofissional = FuncaoProfissional::OrderBy('nome')->get();
        $documento = LancamentoDocumento::where('tipoarquivo','>',0)->orderBy('ID', 'desc')->get();


        $arquivoExiste = null;
        $PreparadoresArquivo = PreparadoresArquivo::where('preparadores_id', $id)
             ->orderBy('id')
             ->get();

             foreach ($PreparadoresArquivo as $PreparadoresArquivos) {
                 $arquivoExiste = $PreparadoresArquivos->id;

             }


        // dd( $cargoprofissional , $funcaoprofissional );
        return view('Preparadores.edit',compact('model','cargoprofissional','funcaoprofissional','documento','arquivoExiste','PreparadoresArquivo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = Preparadores::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
        //     return redirect(route('Preparadores.index'));
        // }


        $cadastro = Preparadores::find($id);
        // $request['cargoProfissional'] = $request->cargoprofissional;
        // $request['FuncaoProfissional'] = $request->funcaoprofissional;

        // dd($request);
        $cadastro->fill($request->all()) ;
        $cadastro->save();


        return redirect(route('Preparadores.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

        // session(['error' => "Preparador  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO! AVISAR ADMINISTRADOR - ERRO: L121"]);
        // return redirect(route('Preparadores.index'));

    //    $Posicao = Posicoes::where('tipo_esporte', $id)->get();

    //    if($Posicao->Count() > 0)
    //    {

    //     session(['error' => "TIPO DE DOCUMENTO:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
    //     return redirect(route('TipoArquivo.index'));
    //    }


        $model= Preparadores::find($id);

        $model->delete();

       session(['success' => "Preparador:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('Preparadores.index'));

    }

    public function export(Request $request)
    {
        $query = Preparadores::query();

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . trim($request->input('email')) . '%');
        }
        if ($request->filled('telefone')) {
            $query->where('telefone', 'like', '%' . trim($request->input('telefone')) . '%');
        }
        if ($request->filled('licencaCBF')) {
            $query->where('licencaCBF', 'like', '%' . trim($request->input('licencaCBF')) . '%');
        }

        $allowedSorts = ['nome','email','telefone','licencaCBF'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $data = $query->orderBy($sort, $dir)->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="preparadores.csv"',
        ];

        $columns = ['Nome','Email','Telefone','Licença CBF'];

        return response()->streamDownload(function () use ($data, $columns) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');
            foreach ($data as $row) {
                fputcsv($out, [
                    $row->nome,
                    $row->email,
                    $row->telefone,
                    $row->licencaCBF,
                ], ';');
            }
            fclose($out);
        }, 'preparadores.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['nome','email','telefone','licencaCBF','sort','dir']);
        return Excel::download(new PreparadoresExport($filters), 'preparadores.xlsx');
    }

    // Exportação PDF (Dompdf) respeitando filtros e ordenação atuais
    public function exportPdf(Request $request)
    {
        $query = Preparadores::query();

        if ($request->filled('nome')) { $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%'); }
        if ($request->filled('email')) { $query->where('email', 'like', '%' . trim($request->input('email')) . '%'); }
        if ($request->filled('telefone')) { $query->where('telefone', 'like', '%' . trim($request->input('telefone')) . '%'); }
        if ($request->filled('licencaCBF')) { $query->where('licencaCBF', 'like', '%' . trim($request->input('licencaCBF')) . '%'); }

        $allowedSorts = ['nome','email','telefone','licencaCBF'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $registros = $query->orderBy($sort, $dir)->get();

        $html = view('Preparadores.export-pdf', [
            'registros' => $registros,
            'headerTitle' => $request->query('header_title'),
            'headerSubtitle' => $request->query('header_subtitle'),
            'footerLeft' => $request->query('footer_left'),
            'footerRight' => $request->query('footer_right'),
            'logoUrl' => $request->query('logo_url'),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $fileName = 'preparadores-'.date('Ymd-His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function CreateArquivoPreparadores(ArquivoPreparadoresCreateRequest $request)
    {


        $id = $request->preparadores_id;
        $preparadores_id = $request->preparadores_id;
        $arquivo_id = $request->arquivo_id;


        $Existe = PreparadoresArquivo::where('arquivo_id',$arquivo_id)
        ->where('preparadores_id',$preparadores_id)
        ->first();



        if($Existe){
            session(['error' => "ARQUIVO EXISTE:  " . $Existe->MostraLancamentoDocumento->Rotulo.  ' do tipo de arquivo: '. $Existe->MostraLancamentoDocumento->TipoArquivoNome->nome .",  já existe para este registro!"]);
            return redirect(route('FormandoBase.edit', $id));
        }

        $request['user_created'] = Auth ::user()->email;

        $model = $request->all();


        PreparadoresArquivo::create($model);

        return redirect(route('Preparadores.edit', $id));

    }



}
