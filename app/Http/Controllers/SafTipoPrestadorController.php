<?php

namespace App\Http\Controllers;

use App\Http\Requests\SafTipoPrestadorRequest;
use App\Models\SafTipoPrestador;
use App\Models\FuncaoProfissional;
use Illuminate\Http\Request;

class SafTipoPrestadorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:SAF_TIPOS_PRESTADORES - LISTAR'])->only('index');
        $this->middleware(['permission:SAF_TIPOS_PRESTADORES - INCLUIR'])->only(['create','store']);
        $this->middleware(['permission:SAF_TIPOS_PRESTADORES - EDITAR'])->only(['edit','update']);
        $this->middleware(['permission:SAF_TIPOS_PRESTADORES - VER'])->only(['show']);
        $this->middleware(['permission:SAF_TIPOS_PRESTADORES - EXCLUIR'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $allowedSorts = ['nome','cidade','uf','pais','funcao'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $defaultPerPage = (int) ($request->session()->get('saf_tipos_prestadores.per_page', 20));
        if ($defaultPerPage < 5 || $defaultPerPage > 100) { $defaultPerPage = 20; }
        $perPage = (int) $request->query('per_page', $defaultPerPage);
        if ($perPage < 5) { $perPage = 5; }
        if ($perPage > 100) { $perPage = 100; }
        $request->session()->put('saf_tipos_prestadores.per_page', $perPage);

        $q = trim((string) $request->query('q', ''));
        $funcaoId = $request->query('funcao_profissional_id');
        $query = SafTipoPrestador::query()->with('funcaoProfissional');
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('cidade', 'like', "%{$q}%")
                  ->orWhere('uf', 'like', "%{$q}%")
                  ->orWhere('pais', 'like', "%{$q}%");
            });
        }
        if (!empty($funcaoId)) {
            $query->where('funcao_profissional_id', $funcaoId);
        }

        if ($sort === 'funcao') {
            $query->leftJoin('FuncaoProfissional as fp', 'fp.id', '=', 'saf_tipos_prestadores.funcao_profissional_id')
                  ->select('saf_tipos_prestadores.*')
                  ->orderBy('fp.nome', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $model = $query->paginate($perPage);
        $funcoes = FuncaoProfissional::orderBy('nome')->pluck('nome','id');
        return view('SafTiposPrestadores.index', compact('model','sort','dir','q','funcoes','funcaoId'));
    }

    public function create()
    {
        $funcoes = FuncaoProfissional::orderBy('nome')->pluck('nome','id');
        return view('SafTiposPrestadores.create', compact('funcoes'));
    }

    public function store(SafTipoPrestadorRequest $request)
    {
    $dados = $request->only(['nome','cidade','uf','pais','funcao_profissional_id']);
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        $existe = SafTipoPrestador::where('nome', trim($dados['nome']))->first();
        if ($existe) {
            session(['error' => 'Tipo de prestador já existe! Nada incluído.']);
            return redirect()->route('SafTiposPrestadores.index');
        }

        SafTipoPrestador::create($dados);
        session(['success' => 'Tipo de prestador incluído com sucesso!']);
        return redirect()->route('SafTiposPrestadores.index');
    }

    public function show(string $id)
    {
        $cadastro = SafTipoPrestador::findOrFail($id);
        return view('SafTiposPrestadores.show', compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model = SafTipoPrestador::findOrFail($id);
        $funcoes = FuncaoProfissional::orderBy('nome')->pluck('nome','id');
        return view('SafTiposPrestadores.edit', compact('model','funcoes'));
    }

    public function update(SafTipoPrestadorRequest $request, string $id)
    {
        $cadastro = SafTipoPrestador::findOrFail($id);
    $dados = $request->only(['nome','cidade','uf','pais','funcao_profissional_id']);
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        $existe = SafTipoPrestador::where('nome', trim($dados['nome']))->where('id', '!=', $cadastro->id)->first();
        if ($existe) {
            session(['error' => 'Nome já utilizado por outro tipo de prestador.']);
            return redirect()->route('SafTiposPrestadores.index');
        }

        $cadastro->fill($dados);
        $cadastro->save();
        session(['success' => 'Tipo de prestador atualizado com sucesso!']);
        return redirect()->route('SafTiposPrestadores.index');
    }

    public function destroy(string $id)
    {
        $cadastro = SafTipoPrestador::findOrFail($id);
        $nome = $cadastro->nome;
        $cadastro->delete();
        session(['success' => "Tipo de prestador {$nome} excluído com sucesso!"]);
        return redirect()->route('SafTiposPrestadores.index');
    }
}
