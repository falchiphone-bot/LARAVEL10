<?php

namespace App\Http\Controllers;

use App\Http\Requests\SafAnoRequest;
use App\Models\SafAno;

class SafAnoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:SAF_ANOS - LISTAR'])->only('index');
        $this->middleware(['permission:SAF_ANOS - INCLUIR'])->only(['create','store']);
        $this->middleware(['permission:SAF_ANOS - EDITAR'])->only(['edit','update']);
        $this->middleware(['permission:SAF_ANOS - VER'])->only(['show']);
        $this->middleware(['permission:SAF_ANOS - EXCLUIR'])->only(['destroy']);
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $allowedSorts = ['ano','created_at','updated_at'];
        $sortInput = (string) $request->query('sort', 'ano');
        $sort = in_array($sortInput, $allowedSorts, true) ? $sortInput : 'ano';
        $dir = strtolower((string) $request->query('dir','asc')) === 'desc' ? 'desc' : 'asc';
        $q = trim((string) $request->query('q',''));

        $defaultPerPage = (int) ($request->session()->get('saf_anos.per_page', 20));
        if ($defaultPerPage < 5 || $defaultPerPage > 100) { $defaultPerPage = 20; }
        $perPage = (int) $request->query('per_page', $defaultPerPage);
        if ($perPage < 5) { $perPage = 5; }
        if ($perPage > 100) { $perPage = 100; }
        $request->session()->put('saf_anos.per_page', $perPage);

        $query = SafAno::query();
        if ($q !== '') {
            $query->where('ano', 'like', "%{$q}%");
        }
        $model = $query->orderBy($sort, $dir)->paginate($perPage)->appends($request->query());
        return view('SafAnos.index', compact('model','sort','dir','q','perPage'));
    }

    public function create()
    {
        return view('SafAnos.create');
    }

    public function store(SafAnoRequest $request)
    {
        $ano = (int) $request->input('ano');
        if (SafAno::where('ano', $ano)->exists()) {
            session(['error' => 'Ano já cadastrado.']);
            return redirect()->route('SafAnos.index');
        }
        SafAno::create(['ano' => $ano]);
        session(['success' => 'Ano incluído com sucesso!']);
        return redirect()->route('SafAnos.index');
    }

    public function show(string $id)
    {
        $cadastro = SafAno::findOrFail($id);
        return view('SafAnos.show', compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model = SafAno::findOrFail($id);
        return view('SafAnos.edit', compact('model'));
    }

    public function update(SafAnoRequest $request, string $id)
    {
        $cadastro = SafAno::findOrFail($id);
        $ano = (int) $request->input('ano');
        if (SafAno::where('ano', $ano)->where('id','!=',$cadastro->id)->exists()) {
            session(['error' => 'Ano já utilizado.']);
            return redirect()->route('SafAnos.index');
        }
        $cadastro->ano = $ano;
        $cadastro->save();
        session(['success' => 'Ano atualizado com sucesso!']);
        return redirect()->route('SafAnos.index');
    }

    public function destroy(string $id)
    {
        $cadastro = SafAno::findOrFail($id);
        $valor = $cadastro->ano;
        $cadastro->delete();
        session(['success' => "Ano {$valor} excluído com sucesso!"]);
        return redirect()->route('SafAnos.index');
    }
}
