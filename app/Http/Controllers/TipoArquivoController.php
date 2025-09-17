<?php

namespace App\Http\Controllers;

use App\Http\Requests\TipoArquivoCreateRequest;
use App\Models\TipoArquivo;
use App\Models\Posicoes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Exports\TipoArquivoExport;
use Maatwebsite\Excel\Facades\Excel;


class TipoArquivoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:TIPOARQUIVO - LISTAR'])->only(['index','export','exportXlsx']);
        $this->middleware(['permission:TIPOARQUIVO - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:TIPOARQUIVO - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:TIPOARQUIVO - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:TIPOARQUIVO - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Limpar filtros salvos
        if ($request->boolean('clear')) {
            Session::forget('tipoarquivo.index.filters');
            return redirect()->route('TipoArquivo.index');
        }

        // Carregar filtros salvos se nenhum parâmetro informado
        $saved = Session::get('tipoarquivo.index.filters', []);
        $incomingFilters = $request->only(['nome','per_page','sort','dir']);
        $hasIncoming = collect($incomingFilters)->filter(function($v){ return $v !== null && $v !== ''; })->isNotEmpty();
        if (!$hasIncoming && !empty($saved)) {
            return redirect()->route('TipoArquivo.index', $saved);
        }

        // Salvar filtros se solicitado
        if ($request->boolean('remember')) {
            Session::put('tipoarquivo.index.filters', $incomingFilters);
        }

        $query = TipoArquivo::query();

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

        return view('TipoArquivo.index', compact('model','total','perPage','sort','dir'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('TipoArquivo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipoArquivoCreateRequest $request)
    {
        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoArquivo::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('TipoArquivo.index'));
        }


        $model= $request->all();


        TipoArquivo::create($model);
        session(['success' => "TIPO DE DOCUMENTO:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoArquivo.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = TipoArquivo::find($id);
        return view('TipoArquivo.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= TipoArquivo::find($id);


        return view('TipoArquivo.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoArquivo::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('TipoArquivo.index'));
        }


        $cadastro = TipoArquivo::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('TipoArquivo.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

        session(['error' => "TIPO DE DOCUMENTO:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO! AVISAR ADMINISTRADOR - ERRO: L121"]);
        return redirect(route('TipoArquivo.index'));

       $Posicao = Posicoes::where('tipo_esporte', $id)->get();

       if($Posicao->Count() > 0)
       {

        session(['error' => "TIPO DE DOCUMENTO:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
        return redirect(route('TipoArquivo.index'));
       }


        $model= TipoArquivo::find($id);

        $model->delete();

       session(['success' => "TIPO DE ARQUIVO:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoArquivo.index'));

    }

    public function export(Request $request)
    {
        $query = TipoArquivo::query();
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
            'Content-Disposition' => 'attachment; filename="tipo-arquivo.csv"',
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
        }, 'tipo-arquivo.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['nome','sort','dir']);
        return Excel::download(new TipoArquivoExport($filters), 'tipo-arquivo.xlsx');
    }
}
