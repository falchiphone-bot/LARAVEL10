<?php

namespace App\Http\Controllers;

use App\Http\Requests\FuncaoProfissionalRequest;
use App\Models\FuncaoProfissional;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class FuncaoProfissionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FUNCAOPROFISSIONAL - LISTAR'])->only('index');
        $this->middleware(['permission:FUNCAOPROFISSIONAL - INCLUIR'])->only(['create', 'store']);
    $this->middleware(['permission:FUNCAOPROFISSIONAL - EDITAR'])->only(['edit', 'update']);
    $this->middleware(['permission:FUNCAOPROFISSIONAL - VER'])->only(['show']);
        $this->middleware(['permission:FUNCAOPROFISSIONAL - EXCLUIR'])->only('destroy');
    }

    public function index(Request $request)
    {
        $allowedSorts = ['nome'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $defaultPerPage = (int) ($request->session()->get('funcaoprofissional.per_page', 20));
        if ($defaultPerPage < 5 || $defaultPerPage > 100) { $defaultPerPage = 20; }
        $perPage = (int) $request->query('per_page', $defaultPerPage);
        if ($perPage < 5) { $perPage = 5; }
        if ($perPage > 100) { $perPage = 100; }
        $request->session()->put('funcaoprofissional.per_page', $perPage);

        $q = trim((string) $request->query('q', ''));
        $query = FuncaoProfissional::query();
        if ($q !== '') {
            $query->where('nome', 'like', "%{$q}%");
        }
        $model = $query->orderBy($sort, $dir)->paginate($perPage);

        return view('FuncaoProfissional.index', compact('model','sort','dir','q'));
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
