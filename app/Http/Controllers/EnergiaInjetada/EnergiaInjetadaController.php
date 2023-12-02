<?php

namespace App\Http\Controllers\EnergiaInjetada;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\EnergiaInjetadaCreateRequest;
use App\Models\EnergiaInjetada;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EnergiaInjetadaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // $this->middleware(['permission:ENERGIAINJETADA - DASHBOARD'])->only('dashboard');
        $this->middleware(['permission:ENERGIAINJETADA - LISTAR'])->only('index');
        $this->middleware(['permission:ENERGIAINJETADA - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:ENERGIAINJETADA - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:ENERGIAINJETADA - VER'])->only(['edit', 'show']);
        $this->middleware(['permission:ENERGIAINJETADA - EXCLUIR'])->only('destroy');
    }

    public function dashboard()
    {
        return view('EnergiaInjetada.dashboard');
    }

    public function index()
    {
        $models = EnergiaInjetada::orderBy('created_at')->get();
        return view('EnergiaInjetada.index', compact('models'));
    }

    public function create()
    {
        return view('EnergiaInjetada.create');
    }

    public function store(EnergiaInjetadaCreateRequest $request)
    {
        $request->validate([
            'nome' => 'required|unique:energia_injetada,nome',
        ]);

        $nome = strtoupper(trim($request->nome));

        $existecadastro = EnergiaInjetada::where('nome', $nome)->first();

        if ($existecadastro) {
            session()->flash('error', "NOME: $nome já existe! NADA INCLUÍDO!");
            return redirect(route('EnergiaInjetada.index'));
        }

        EnergiaInjetada::create(['nome' => $nome]);

        session()->flash('success', "TIPO DE ESPORTE: $nome INCLUÍDO COM SUCESSO!");
        return redirect(route('EnergiaInjetada.index'));
    }

    public function show(string $id)
    {
        $cadastro = EnergiaInjetada::find($id);
        return view('EnergiaInjetada.show', compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model = EnergiaInjetada::find($id);
        return view('EnergiaInjetada.edit', compact('model'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nome' => 'required|unique:energia_injetada,nome,' . $id,
        ]);

        $nome = strtoupper(trim($request->nome));

        $existecadastro = EnergiaInjetada::where('nome', $nome)->first();

        if ($existecadastro) {
            session()->flash('error', "NOME: $nome já existe ou não precisa ser alterado!");
            return redirect(route('EnergiaInjetada.index'));
        }

        $cadastro = EnergiaInjetada::find($id);
        $cadastro->fill(['nome' => $nome]);
        $cadastro->save();

        return redirect(route('EnergiaInjetada.index'));
    }

    public function destroy(Request $request, string $id)
    {
        $posicoes = Posicoes::where('tipo_esporte', $id)->get();

        if ($posicoes->count() > 0) {
            session()->flash('error', "TIPO DE ESPORTE selecionado! Não pode ser excluído pois está sendo usado. Retornado à situação anterior.");
            return redirect(route('EnergiaInjetada.index'));
        }

        $model = EnergiaInjetada::find($id);
        $model->delete();

        session()->flash('success', "TIPO DE ESPORTE: $model->nome EXCLUÍDO COM SUCESSO!");
        return redirect(route('EnergiaInjetada.index'));
    }
}
