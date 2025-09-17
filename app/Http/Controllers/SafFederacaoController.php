<?php

namespace App\Http\Controllers;

use App\Http\Requests\SafFederacaoRequest;
use App\Models\SafFederacao;
use Illuminate\Http\Request;

class SafFederacaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:SAF_FEDERACOES - LISTAR'])->only('index');
        $this->middleware(['permission:SAF_FEDERACOES - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:SAF_FEDERACOES - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:SAF_FEDERACOES - VER'])->only(['show']);
        $this->middleware(['permission:SAF_FEDERACOES - EXCLUIR'])->only('destroy');
    }

    public function index(Request $request)
    {
        $allowedSorts = ['nome','cidade','uf','pais'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $defaultPerPage = (int) ($request->session()->get('saf_federacoes.per_page', 20));
        if ($defaultPerPage < 5 || $defaultPerPage > 100) { $defaultPerPage = 20; }
        $perPage = (int) $request->query('per_page', $defaultPerPage);
        if ($perPage < 5) { $perPage = 5; }
        if ($perPage > 100) { $perPage = 100; }
        $request->session()->put('saf_federacoes.per_page', $perPage);

        $q = trim((string) $request->query('q', ''));
        $query = SafFederacao::query();
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('cidade', 'like', "%{$q}%")
                  ->orWhere('uf', 'like', "%{$q}%")
                  ->orWhere('pais', 'like', "%{$q}%");
            });
        }
        $model = $query->orderBy($sort, $dir)->paginate($perPage);
        return view('SafFederacoes.index', compact('model','sort','dir','q'));
    }

    public function create()
    {
        return view('SafFederacoes.create');
    }

    public function store(SafFederacaoRequest $request)
    {
        $dados = $request->all();
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        $existe = SafFederacao::where('nome', trim($dados['nome']))->first();
        if ($existe) {
            session(['error' => 'FEDERAÇÃO já existe! Nada incluído.']);
            return redirect()->route('SafFederacoes.index');
        }

        SafFederacao::create($dados);
        session(['success' => 'Federação incluída com sucesso!']);
        return redirect()->route('SafFederacoes.index');
    }

    public function show(string $id)
    {
        $cadastro = SafFederacao::findOrFail($id);
        return view('SafFederacoes.show', compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model = SafFederacao::findOrFail($id);
        return view('SafFederacoes.edit', compact('model'));
    }

    public function update(SafFederacaoRequest $request, string $id)
    {
        $cadastro = SafFederacao::findOrFail($id);
        $dados = $request->all();
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        $existe = SafFederacao::where('nome', trim($dados['nome']))->where('id', '!=', $cadastro->id)->first();
        if ($existe) {
            session(['error' => 'Nome já utilizado por outra federação.']);
            return redirect()->route('SafFederacoes.index');
        }

        $cadastro->fill($dados);
        $cadastro->save();
        session(['success' => 'Federação atualizada com sucesso!']);
        return redirect()->route('SafFederacoes.index');
    }

    public function destroy(string $id)
    {
        $cadastro = SafFederacao::findOrFail($id);
        $nome = $cadastro->nome;
        $cadastro->delete();
        session(['success' => "Federação {$nome} excluída com sucesso!"]);
        return redirect()->route('SafFederacoes.index');
    }
}
