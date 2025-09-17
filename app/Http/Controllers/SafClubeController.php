<?php

namespace App\Http\Controllers;

use App\Http\Requests\SafClubeRequest;
use App\Models\SafClube;
use Illuminate\Http\Request;

class SafClubeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:SAF_CLUBES - LISTAR'])->only('index');
        $this->middleware(['permission:SAF_CLUBES - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:SAF_CLUBES - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:SAF_CLUBES - VER'])->only(['show']);
        $this->middleware(['permission:SAF_CLUBES - EXCLUIR'])->only('destroy');
    }

    public function index(Request $request)
    {
        $allowedSorts = ['nome','cidade','uf','pais'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // per_page com persistência em sessão
        $defaultPerPage = (int) ($request->session()->get('saf_clubes.per_page', 20));
        if ($defaultPerPage < 5 || $defaultPerPage > 100) { $defaultPerPage = 20; }
        $perPage = (int) $request->query('per_page', $defaultPerPage);
        if ($perPage < 5) { $perPage = 5; }
        if ($perPage > 100) { $perPage = 100; }
        $request->session()->put('saf_clubes.per_page', $perPage);

        $q = trim((string) $request->query('q', ''));
        $query = SafClube::query();
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('cidade', 'like', "%{$q}%")
                  ->orWhere('uf', 'like', "%{$q}%")
                  ->orWhere('pais', 'like', "%{$q}%");
            });
        }
        $model = $query->orderBy($sort, $dir)->paginate($perPage);
        return view('SafClubes.index', compact('model','sort','dir','q'));
    }

    public function create()
    {
        return view('SafClubes.create');
    }

    public function store(SafClubeRequest $request)
    {
        $dados = $request->all();
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        $existe = SafClube::where('nome', trim($dados['nome']))->first();
        if ($existe) {
            session(['error' => 'CLUBE já existe! Nada incluído.']);
            return redirect()->route('SafClubes.index');
        }

        SafClube::create($dados);
        session(['success' => 'Clube incluído com sucesso!']);
        return redirect()->route('SafClubes.index');
    }

    public function show(string $id)
    {
        $cadastro = SafClube::findOrFail($id);
        return view('SafClubes.show', compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model = SafClube::findOrFail($id);
        return view('SafClubes.edit', compact('model'));
    }

    public function update(SafClubeRequest $request, string $id)
    {
        $cadastro = SafClube::findOrFail($id);
        $dados = $request->all();
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        $existe = SafClube::where('nome', trim($dados['nome']))->where('id', '!=', $cadastro->id)->first();
        if ($existe) {
            session(['error' => 'Nome já utilizado por outro clube.']);
            return redirect()->route('SafClubes.index');
        }

        $cadastro->fill($dados);
        // Deixe Eloquent popular updated_at com frações
        $cadastro->save();
        session(['success' => 'Clube atualizado com sucesso!']);
        return redirect()->route('SafClubes.index');
    }

    public function destroy(string $id)
    {
        $cadastro = SafClube::findOrFail($id);
        $nome = $cadastro->nome;
        $cadastro->delete();
        session(['success' => "Clube {$nome} excluído com sucesso!"]);
        return redirect()->route('SafClubes.index');
    }
}
