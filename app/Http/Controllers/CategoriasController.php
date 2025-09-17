<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriasCreateRequest;
use App\Models\Categorias;
use App\Models\TipoEsporte;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Exports\CategoriasExport;
use Maatwebsite\Excel\Facades\Excel;


class CategoriasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:CATEGORIAS - LISTAR'])->only(['index','export','exportXlsx']);
        $this->middleware(['permission:CATEGORIAS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:CATEGORIAS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:CATEGORIAS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:CATEGORIAS - EXCLUIR'])->only('destroy');
    }

    public function index(Request $request)
    {
        // Limpar filtros salvos
        if ($request->boolean('clear')) {
            Session::forget('categorias.index.filters');
            return redirect()->route('Categorias.index');
        }

        // Carregar filtros salvos se nenhum parâmetro informado
        $saved = Session::get('categorias.index.filters', []);
        $incomingFilters = $request->only(['nome','tipo_esporte','per_page','sort','dir']);
        $hasIncoming = collect($incomingFilters)->filter(function($v){ return $v !== null && $v !== ''; })->isNotEmpty();
        if (!$hasIncoming && !empty($saved)) {
            return redirect()->route('Categorias.index', $saved);
        }

        // Salvar filtros se solicitado
        if ($request->boolean('remember')) {
            Session::put('categorias.index.filters', $incomingFilters);
        }

        $query = Categorias::query()->with('MostraCategoria');
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }
        if ($request->filled('tipo_esporte')) {
            $query->where('tipo_esporte', (int)$request->input('tipo_esporte'));
        }

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

        $TipoEsporte = TipoEsporte::orderBy('nome')->get();
        return view('Categorias.index', compact('model','total','perPage','sort','dir','TipoEsporte'));
    }


    public function create()
    {
        $retorno['TipoEsporte'] = null;
        $TipoEsporte = TipoEsporte::get();
        return view('Categorias.create', compact('TipoEsporte', 'retorno'));
    }


    public function store(CategoriasCreateRequest $request)
    {

        if($request->tipo_esporte === null){

            session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .", DEVE SER SELECIONADO! NADA INCLUÍDO! "]);
            return redirect(route('Posicoes.index'));
        }

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Categorias::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! "]);
            return redirect(route('Posicoes.index'));
        }

        $request['user_created'] = Auth::user()->email;
        $model = $request->all();


        Categorias::create($model);

        session(['success' => 'Categoria inserida com sucesso!']);

        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  ." INCLUÍDO COM SUCESSO!"]);
        return redirect(route('Categorias.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Categorias::find($id);
        return view('Categorias.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)

    {

        $model= Categorias::find($id);
        $retorno['TipoEsporte'] = $model->tipo_esporte;
        $TipoEsporte = TipoEsporte::get();

        // DD($model, $retorno['TipoEsporte']);
        return view('Categorias.edit',compact('model', 'TipoEsporte', 'retorno',));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {




        if($request->tipo_esporte === null){

            session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .", DEVE SER SELECIONADO! NADA FOI ALTERADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
            return  redirect(route('Categorias.edit', $id));
        }
        else
        if($request->nome === null || $request->nome === ''){

            session(['error' => "TIPO DE ESPORTE, DEVE SER PREENCHIDO. NÃO PODE SER VAZIO! NADA ALTERADO! "]);
             return  redirect(route('Categorias.edit', $id));
        }

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Categorias::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('Categorias.index'));
        }


        $cadastro = Categorias::find($id);

        $request['user_updated'] = Auth::user()->email;
        $cadastro->fill($request->all()) ;


        $cadastro->save();

        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  ." ALTERADO COM SUCESSO!"]);
        return  redirect(route('Categorias.edit', $id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        // $FormandoBasePosicao = FormandoBasePosicoes::where('posicao_id',$id)->first();

        // if($FormandoBasePosicao){
        //     session(['error' => "CATEGORIA:  ". $FormandoBasePosicao->MostraPosicao->nome  ." sendo usado!"]);
        //     return redirect(route('Categorias.index'));
        // }

        $model = Categorias::find($id);

        $model->delete();
        session(['success' => "CATEGORIA:  ". $model->nome  ." EXCLUÍDA COM SUCESSO!"]);
        return redirect(route('Categorias.index'));

    }

    public function export(Request $request)
    {
        $query = Categorias::query()->with('MostraCategoria');
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }
        if ($request->filled('tipo_esporte')) {
            $query->where('tipo_esporte', (int)$request->input('tipo_esporte'));
        }
        $allowedSorts = ['nome'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $data = $query->orderBy($sort, $dir)->get();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="categorias.csv"',
        ];
        $columns = ['Nome','Esporte'];
        return response()->streamDownload(function () use ($data, $columns) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');
            foreach ($data as $row) {
                fputcsv($out, [
                    $row->nome,
                    optional($row->MostraCategoria)->nome,
                ], ';');
            }
            fclose($out);
        }, 'categorias.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['nome','tipo_esporte','sort','dir']);
        return Excel::download(new CategoriasExport($filters), 'categorias.xlsx');
    }
}
