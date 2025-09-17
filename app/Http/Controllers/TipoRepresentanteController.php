<?php

namespace App\Http\Controllers;

use App\Models\Representante;
use App\Models\TipoRepresentante;
use App\Http\Requests\TipoRepresentanteCreateRequest;
use App\Models\Representantes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Exports\TipoRepresentanteExport;
use Maatwebsite\Excel\Facades\Excel;


class TipoRepresentanteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:TIPOREPRESENTANTE - LISTAR'])->only(['index','export','exportXlsx']);
        $this->middleware(['permission:TIPOREPRESENTANTE - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:TIPOREPRESENTANTE - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:TIPOREPRESENTANTE - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:TIPOREPRESENTANTE - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index(Request $request)
    {
        // Limpar filtros salvos
        if ($request->boolean('clear')) {
            Session::forget('tiporepresentante.index.filters');
            return redirect()->route('TipoRepresentantes.index');
        }

        // Carregar filtros salvos quando não houver parâmetros
        $saved = Session::get('tiporepresentante.index.filters', []);
        $incoming = $request->only(['nome','per_page','sort','dir']);
        $hasIncoming = collect($incoming)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
        if (!$hasIncoming && !empty($saved)) {
            return redirect()->route('TipoRepresentantes.index', $saved);
        }
        if ($request->boolean('remember')) {
            Session::put('tiporepresentante.index.filters', $incoming);
        }

        $query = TipoRepresentante::query();
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }
        $allowedSorts = ['nome'];
        $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'nome';
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $total = (clone $query)->count();
        $perPage = (int) $request->input('per_page', 25);
        if ($perPage <= 0) { $perPage = 25; }
        $model = $query->orderBy($sort, $dir)->paginate($perPage)->appends($request->except('page'));

        return view('TipoRepresentante.index', compact('model','total','perPage','sort','dir'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
           return view('TipoRepresentante.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipoRepresentanteCreateRequest $request)
    {


        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = TipoRepresentante::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('TipoRepresentante.index'));
        }


        $model= $request->all();


        TipoRepresentante::create($model);
        session(['success' => "TIPO DE REPRESENTANTE:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoRepresentantes.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = TipoRepresentante::find($id);
        return view('TipoRepresentante.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= TipoRepresentante::find($id);


        return view('TipoRepresentante.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request["nome"] = strtoupper($request["nome"]);
        $existecadastro = Representantes::where('nome',trim($request["nome"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
            return redirect(route('TipoRepresentantes.index'));
        }


        $cadastro = TipoRepresentante::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('TipoRepresentantes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

       $Representante = Representantes::where('tipo_representante', $id)->get();

       if($Representante->Count() > 0)
       {

        session(['error' => "TIPO DE REPRESENTANTE  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
        return redirect(route('TipoEsporte.index'));
       }


        $model= TipoRepresentante::find($id);

        $model->delete();

       session(['success' => "TIPO DE REPRESENTANTES:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('TipoRepresentantes.index'));

    }

    public function export(Request $request)
    {
        $query = TipoRepresentante::query();
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }
        $allowedSorts = ['nome'];
        $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'nome';
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $data = $query->orderBy($sort, $dir)->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tipo-representantes.csv"',
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
        }, 'tipo-representantes.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['nome','sort','dir']);
        return Excel::download(new TipoRepresentanteExport($filters), 'tipo-representantes.xlsx');
    }
}
