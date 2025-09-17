<?php

namespace App\Http\Controllers;


use App\Http\Requests\PosicoesCreateRequest;
use App\Http\Requests\TipoEsporteCreateRequest;
use App\Models\Posicoes;
use App\Models\TipoEsporte;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Exports\TipoEsporteExport;
use Maatwebsite\Excel\Facades\Excel;


class TipoEsporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    $this->middleware(['permission:TIPOESPORTE - LISTAR'])->only(['index','export','exportXlsx']);
        $this->middleware(['permission:POSICOES - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:POSICOES - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:POSICOES - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:POSICOES - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Limpar filtros salvos
        if ($request->boolean('clear')) {
            Session::forget('tipoesporte.index.filters');
            return redirect()->route('TipoEsporte.index');
        }

        // Carregar filtros salvos se nenhum parâmetro informado
        $saved = Session::get('tipoesporte.index.filters', []);
        $incomingFilters = $request->only(['nome','per_page','sort','dir']);
        $hasIncoming = collect($incomingFilters)->filter(function($v){ return $v !== null && $v !== ''; })->isNotEmpty();
        if (!$hasIncoming && !empty($saved)) {
            return redirect()->route('TipoEsporte.index', $saved);
        }

        // Salvar filtros se solicitado
        if ($request->boolean('remember')) {
            Session::put('tipoesporte.index.filters', $incomingFilters);
        }

        $query = TipoEsporte::query();
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }

        // Ordenação
        $allowedSorts = ['nome'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $total = (clone $query)->count();
        $perPage = (int)($request->input('per_page', 25));
        if ($perPage <= 0) { $perPage = 25; }
        $model = $query->orderBy($sort, $dir)
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view('TipoEsporte.index', compact('model','total','perPage','sort','dir'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('TipoEsporte.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipoEsporteCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoEsporte::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('TipoEsporte.index'));
        }


        $model= $request->all();


        TipoEsporte::create($model);
        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoEsporte.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = TipoEsporte::find($id);
        return view('TipoEsporte.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= TipoEsporte::find($id);


        return view('TipoEsporte.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoEsporte::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('TipoEsporte.index'));
        }


        $cadastro = TipoEsporte::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('TipoEsporte.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

       $Posicao = Posicoes::where('tipo_esporte', $id)->get();

       if($Posicao->Count() > 0)
       {

        session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
        return redirect(route('TipoEsporte.index'));
       }


        $model= TipoEsporte::find($id);

        $model->delete();

       session(['success' => "TIPO DE ESPORTE:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoEsporte.index'));

    }

    public function export(Request $request)
    {
        $query = TipoEsporte::query();
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
            'Content-Disposition' => 'attachment; filename="tipo-esporte.csv"',
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
        }, 'tipo-esporte.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['nome','sort','dir']);
        return Excel::download(new TipoEsporteExport($filters), 'tipo-esporte.xlsx');
    }
}
