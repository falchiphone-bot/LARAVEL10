<?php

namespace App\Http\Controllers;

use App\Http\Requests\FuncaoProfissionalRequest;
use App\Models\FuncaoProfissional;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Exports\FuncaoProfissionalExport;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;


class FuncaoProfissionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FUNCAOPROFISSIONAL - LISTAR'])->only(['index']);
        $this->middleware(['permission:FUNCAOPROFISSIONAL - EXPORTAR'])->only(['export','exportXlsx','exportPdf']);
        $this->middleware(['permission:FUNCAOPROFISSIONAL - INCLUIR'])->only(['create', 'store']);
    $this->middleware(['permission:FUNCAOPROFISSIONAL - EDITAR'])->only(['edit', 'update']);
    $this->middleware(['permission:FUNCAOPROFISSIONAL - VER'])->only(['show']);
        $this->middleware(['permission:FUNCAOPROFISSIONAL - EXCLUIR'])->only('destroy');
    }

    public function index(Request $request)
    {
        // Limpar filtros salvos
        if ($request->boolean('clear')) {
            Session::forget('funcaoprofissional.index.filters');
            return redirect()->route('FuncaoProfissional.index');
        }

        // Carregar filtros salvos se nenhum parâmetro informado
        $saved = Session::get('funcaoprofissional.index.filters', []);
        $incomingFilters = $request->only(['q','per_page','sort','dir']);
        $hasIncoming = collect($incomingFilters)->filter(function($v){ return $v !== null && $v !== ''; })->isNotEmpty();
        if (!$hasIncoming && !empty($saved)) {
            return redirect()->route('FuncaoProfissional.index', $saved);
        }

        // Salvar filtros se solicitado
        if ($request->boolean('remember')) {
            Session::put('funcaoprofissional.index.filters', $incomingFilters);
        }

        $allowedSorts = ['nome'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $perPage = (int) $request->query('per_page', 25);
        if ($perPage < 5) { $perPage = 5; }
        if ($perPage > 100) { $perPage = 100; }

        $q = trim((string) $request->query('q', ''));
        $query = FuncaoProfissional::query();
        if ($q !== '') {
            $query->where('nome', 'like', "%{$q}%");
        }

        $total = (clone $query)->count();
        $model = $query->orderBy($sort, $dir)->paginate($perPage)->appends($request->except('page'));

        return view('FuncaoProfissional.index', compact('model','sort','dir','q','perPage','total'));
    }

    public function export(Request $request)
    {
        $query = FuncaoProfissional::query();
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where('nome', 'like', "%{$q}%");
        }
        $allowedSorts = ['nome'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $data = $query->orderBy($sort, $dir)->get();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="funcao-profissional.csv"',
        ];
        $columns = ['Nome'];
        return response()->streamDownload(function () use ($data, $columns) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');
            foreach ($data as $row) {
                fputcsv($out, [$row->nome], ';');
            }
            fclose($out);
        }, 'funcao-profissional.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['q','sort','dir']);
        return Excel::download(new FuncaoProfissionalExport($filters), 'funcao-profissional.xlsx');
    }

    // Exportação PDF (Dompdf) respeitando filtros e ordenação atuais
    public function exportPdf(Request $request)
    {
        $query = FuncaoProfissional::query();
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where('nome', 'like', "%{$q}%");
        }
        $allowedSorts = ['nome'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $registros = $query->orderBy($sort, $dir)->get();

        $html = view('FuncaoProfissional.export-pdf', [
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

        $fileName = 'funcao-profissional-'.date('Ymd-His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }


    public function create()
    {
        return view('FuncaoProfissional.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FuncaoProfissionalRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = FuncaoProfissional::where('nome', trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect()->route('FuncaoProfissional.index');
        }


        $model= $request->all();


        FuncaoProfissional::create($model);
        session(['success' => "FUNÇÃO PROFISSIONAL:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect()->route('FuncaoProfissional.index');

    }


    public function show(string $id)
    {
        $cadastro = FuncaoProfissional::find($id);
        return view('FuncaoProfissional.show',compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model= FuncaoProfissional::find($id);


        return view('FuncaoProfissional.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FuncaoProfissionalRequest $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = FuncaoProfissional::where('nome', trim($request["nome"]))
            ->where('id', '!=', $id)
            ->first();
        if ($existecadastro) {
            session(['error' => "NOME:  " . $request->nome . ", já usado por outro registro! "]);
            return redirect()->route('FuncaoProfissional.index');
        }


        $cadastro = FuncaoProfissional::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        session(['success' => "FUNÇÃO PROFISSIONAL:  ". $cadastro->nome  .",  ATUALIZADA COM SUCESSO!"]);
        return redirect()->route('FuncaoProfissional.index');
    }


    public function destroy(Request $request, string $id)
    {

        $model= FuncaoProfissional::find($id);
        if (!$model) {
            session(['error' => 'Registro não encontrado.']);
            return redirect()->route('FuncaoProfissional.index');
        }
        $nome = $model->nome;
        $model->delete();
        session(['success' => "FUNÇÃO PROFISSIONAL:  ". $nome  .",  EXCLUÍDA COM SUCESSO!"]);
        return redirect()->route('FuncaoProfissional.index');

    }
}
