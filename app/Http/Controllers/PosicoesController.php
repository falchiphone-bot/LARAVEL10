<?php

namespace App\Http\Controllers;


use App\Http\Requests\PosicoesCreateRequest;
use App\Models\FormandoBasePosicoes;
use App\Models\Posicoes;
use App\Models\TipoEsporte;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Exports\PosicoesExport;
use Maatwebsite\Excel\Facades\Excel;


class PosicoesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:POSICOES - LISTAR'])->only(['index','export','exportXlsx']);
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
            Session::forget('posicoes.index.filters');
            return redirect()->route('Posicoes.index');
        }

        // Carregar filtros salvos se nenhum parâmetro informado
        $saved = Session::get('posicoes.index.filters', []);
        $incomingFilters = $request->only(['nome','tipo_esporte','per_page','sort','dir']);
        $hasIncoming = collect($incomingFilters)->filter(function($v){ return $v !== null && $v !== ''; })->isNotEmpty();
        if (!$hasIncoming && !empty($saved)) {
            return redirect()->route('Posicoes.index', $saved);
        }

        // Salvar filtros se solicitado
        if ($request->boolean('remember')) {
            Session::put('posicoes.index.filters', $incomingFilters);
        }

        $query = Posicoes::query()->with('MostraTipoEsporte');
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
        return view('Posicoes.index', compact('model','total','perPage','sort','dir','TipoEsporte'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $retorno['TipoEsporte'] = null;
        $TipoEsporte = TipoEsporte::get();
        return view('Posicoes.create', compact('TipoEsporte', 'retorno'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PosicoesCreateRequest $request)
    {

        if($request->tipo_esporte === null){

            session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .", DEVE SER SELECIONADO! NADA INCLUÍDO! "]);
            return redirect(route('Posicoes.index'));
        }

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Posicoes::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! "]);
            return redirect(route('Posicoes.index'));
        }

        $request['user_created'] = Auth::user()->email;
        $model = $request->all();


        Posicoes::create($model);

        session(['success' => 'Posição inserida com sucesso!']);

        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  ." INCLUÍDO COM SUCESSO!"]);
        return redirect(route('Posicoes.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Posicoes::find($id);
        return view('Posicoes.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)

    {

        $model= Posicoes::find($id);
        $retorno['TipoEsporte'] = $model->tipo_esporte;
        $TipoEsporte = TipoEsporte::get();

        // DD($model, $retorno['TipoEsporte']);
        return view('Posicoes.edit',compact('model', 'TipoEsporte', 'retorno',));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {




        if($request->tipo_esporte === null){

            session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .", DEVE SER SELECIONADO! NADA FOI ALTERADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
            return  redirect(route('Posicoes.edit', $id));
        }
        else
        if($request->nome === null || $request->nome === ''){

            session(['error' => "TIPO DE ESPORTE, DEVE SER PREENCHIDO. NÃO PODE SER VAZIO! NADA ALTERADO! "]);
             return  redirect(route('Posicoes.edit', $id));
        }

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Posicoes::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('Posicoes.index'));
        }


        $cadastro = Posicoes::find($id);

        $request['user_updated'] = Auth::user()->email;
        $cadastro->fill($request->all()) ;


        $cadastro->save();

        session(['success' => "TIPO DE ESPORTE:  ". $request->nome  ." ALTERADO COM SUCESSO!"]);
        return  redirect(route('Posicoes.edit', $id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $FormandoBasePosicao = FormandoBasePosicoes::where('posicao_id',$id)->first();

        if($FormandoBasePosicao){
            session(['error' => "POSIÇÃO:  ". $FormandoBasePosicao->MostraPosicao->nome  ." sendo usado!"]);
            return redirect(route('Posicoes.index'));
        }

        $model = Posicoes::find($id);




        $model->delete();
        session(['success' => "TIPO DE ESPORTE:  ". $model->nome  ." EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('Posicoes.index'));

    }

    public function export(Request $request)
    {
        $query = Posicoes::query()->with('MostraTipoEsporte');
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
            'Content-Disposition' => 'attachment; filename="posicoes.csv"',
        ];
        $columns = ['Nome','Esporte'];
        return response()->streamDownload(function () use ($data, $columns) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');
            foreach ($data as $row) {
                fputcsv($out, [
                    $row->nome,
                    optional($row->MostraTipoEsporte)->nome,
                ], ';');
            }
            fclose($out);
        }, 'posicoes.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['nome','tipo_esporte','sort','dir']);
        return Excel::download(new PosicoesExport($filters), 'posicoes.xlsx');
    }
}
