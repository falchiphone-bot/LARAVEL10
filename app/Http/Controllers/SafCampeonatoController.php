<?php

namespace App\Http\Controllers;

use App\Http\Requests\SafCampeonatoRequest;
use App\Models\Categorias;
use App\Models\SafCampeonato;
use App\Models\SafFederacao;

class SafCampeonatoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:SAF_CAMPEONATOS - LISTAR'])->only('index');
        $this->middleware(['permission:SAF_CAMPEONATOS - INCLUIR'])->only(['create','store']);
        $this->middleware(['permission:SAF_CAMPEONATOS - EDITAR'])->only(['edit','update']);
        $this->middleware(['permission:SAF_CAMPEONATOS - VER'])->only(['show']);
        $this->middleware(['permission:SAF_CAMPEONATOS - EXCLUIR'])->only(['destroy']);
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $allowedSorts = ['nome','cidade','uf','pais'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $defaultPerPage = (int) ($request->session()->get('saf_campeonatos.per_page', 20));
        if ($defaultPerPage < 5 || $defaultPerPage > 100) { $defaultPerPage = 20; }
        $perPage = (int) $request->query('per_page', $defaultPerPage);
        if ($perPage < 5) { $perPage = 5; }
        if ($perPage > 100) { $perPage = 100; }
        $request->session()->put('saf_campeonatos.per_page', $perPage);

        $q = trim((string) $request->query('q', ''));
        $query = SafCampeonato::with('categorias');
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('cidade', 'like', "%{$q}%")
                  ->orWhere('uf', 'like', "%{$q}%")
                  ->orWhere('pais', 'like', "%{$q}%");
            });
        }
        $model = $query->orderBy($sort, $dir)->paginate($perPage);
        return view('SafCampeonatos.index', compact('model','sort','dir','q'));
    }

    public function create()
    {
        $categorias = Categorias::orderBy('nome')->get();
        $federacoes = SafFederacao::orderBy('nome')->get();
        return view('SafCampeonatos.create', compact('categorias','federacoes'));
    }

    public function store(SafCampeonatoRequest $request)
    {
    $dados = $request->only(['nome','cidade','uf','pais','federacao_id']);
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        $existe = SafCampeonato::where('nome', trim($dados['nome']))->first();
        if ($existe) {
            session(['error' => 'CAMPEONATO já existe! Nada incluído.']);
            return redirect()->route('SafCampeonatos.index');
        }

    $campeonato = SafCampeonato::create($dados);
        $cats = $request->input('categorias', []);
        if (!empty($cats)) { $campeonato->categorias()->sync($cats); }

        session(['success' => 'Campeonato incluído com sucesso!']);
        return redirect()->route('SafCampeonatos.index');
    }

    public function show(string $id)
    {
        $cadastro = SafCampeonato::with('categorias')->findOrFail($id);
        return view('SafCampeonatos.show', compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model = SafCampeonato::with('categorias')->findOrFail($id);
        $categorias = Categorias::orderBy('nome')->get();
        $federacoes = SafFederacao::orderBy('nome')->get();
        return view('SafCampeonatos.edit', compact('model','categorias','federacoes'));
    }

    public function update(SafCampeonatoRequest $request, string $id)
    {
        $cadastro = SafCampeonato::findOrFail($id);
    $dados = $request->only(['nome','cidade','uf','pais','federacao_id']);
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        $existe = SafCampeonato::where('nome', trim($dados['nome']))->where('id','!=',$cadastro->id)->first();
        if ($existe) {
            session(['error' => 'Nome já utilizado por outro campeonato.']);
            return redirect()->route('SafCampeonatos.index');
        }

        $cadastro->fill($dados);
        $cadastro->save();

        $cats = $request->input('categorias', []);
        $cadastro->categorias()->sync($cats ?: []);

        session(['success' => 'Campeonato atualizado com sucesso!']);
        return redirect()->route('SafCampeonatos.index');
    }

    public function destroy(string $id)
    {
        $cadastro = SafCampeonato::findOrFail($id);
        $nome = $cadastro->nome;
        $cadastro->categorias()->detach();
        $cadastro->delete();
        session(['success' => "Campeonato {$nome} excluído com sucesso!"]);
        return redirect()->route('SafCampeonatos.index');
    }
}
