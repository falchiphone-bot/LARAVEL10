<?php

namespace App\Http\Controllers;

use App\Http\Requests\CargoProfissionalCreateRequest;
use App\Models\CargoProfissional;
use App\Models\Preparadores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Exports\CargoProfissionalExport;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;


class CargoProfissionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:CARGOPROFISSIONAL - LISTAR'])->only(['index']);
        $this->middleware(['permission:CARGOPROFISSIONAL - EXPORTAR'])->only(['export','exportXlsx','exportPdf']);
        $this->middleware(['permission:CARGOPROFISSIONAL - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:CARGOPROFISSIONAL - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:CARGOPROFISSIONAL - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:CARGOPROFISSIONAL - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        // Limpar filtros salvos
        if ($request->boolean('clear')) {
            Session::forget('cargoprofissional.index.filters');
            return redirect()->route('CargoProfissional.index');
        }

        // Carregar filtros salvos se nenhum parâmetro informado
        $saved = Session::get('cargoprofissional.index.filters', []);
        $incomingFilters = $request->only(['nome','per_page','sort','dir']);
        $hasIncoming = collect($incomingFilters)->filter(function($v){ return $v !== null && $v !== ''; })->isNotEmpty();
        if (!$hasIncoming && !empty($saved)) {
            return redirect()->route('CargoProfissional.index', $saved);
        }

        // Salvar filtros se solicitado
        if ($request->boolean('remember')) {
            Session::put('cargoprofissional.index.filters', $incomingFilters);
        }

        $query = CargoProfissional::query();

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }

        // Ordenação
        $allowedSorts = ['nome'];
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

        return view('CargoProfissional.index', compact('model','total','perPage','sort','dir'));
    }

    public function create()
    {
        return view('CargoProfissional.create');
    }


    public function store(CargoProfissionalCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = CargoProfissional::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('CargoProfissional.index'));
        }


        $model= $request->all();


        CargoProfissional::create($model);
        session(['success' => "Cargo Profissional:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('CargoProfissional.index'));

    }


    public function show(string $id)
    {
        $cadastro = CargoProfissional::find($id);
        return view('CargoProfissional.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= CargoProfissional::find($id);


        return view('CargoProfissional.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = CargoProfissional::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
        //     return redirect(route('CargoProfissional.index'));
        // }


        $cadastro = CargoProfissional::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('CargoProfissional.index'));
    }

    public function destroy(Request $request, string $id)
    {

       $Achar = Preparadores::where('CargoProfissional', $id)->get();

       if($Achar->Count() > 0)
       {

        session(['error' => "CARGO PROFISSIONAL:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
        return redirect(route('CargoProfissional.index'));
       }


        $model= CargoProfissional::find($id);

        $model->delete();

       session(['success' => "CARGO PROFISSIONAL:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('CargoProfissional.index'));

    }

    public function export(Request $request)
    {
        $query = CargoProfissional::query();

        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }

        $allowedSorts = ['nome'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $data = $query->orderBy($sort, $dir)->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cargo-profissional.csv"',
        ];

        $columns = ['Nome'];

        return response()->streamDownload(function () use ($data, $columns) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');
            foreach ($data as $row) {
                fputcsv($out, [
                    $row->nome,
                ], ';');
            }
            fclose($out);
        }, 'cargo-profissional.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['nome','sort','dir']);
        return Excel::download(new CargoProfissionalExport($filters), 'cargo-profissional.xlsx');
    }

    // Exportação PDF (Dompdf)
    public function exportPdf(Request $request)
    {
        $query = CargoProfissional::query();
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }

        $allowedSorts = ['nome'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $registros = $query->orderBy($sort, $dir)->get();

        $html = view('CargoProfissional.export-pdf', [
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

        $fileName = 'cargo-profissional-'.date('Ymd-His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
