<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmpresaCreateRequest;
use App\Models\Empresa;
use App\Models\EmpresaUsuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmpresaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:EMPRESAS - LISTAR'])->only('index');
        $this->middleware(['permission:EMPRESAS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:EMPRESAS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:EMPRESAS - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:EMPRESAS - BLOQUEAR TODAS'])->only('bloquearempresas');
        $this->middleware(['permission:EMPRESAS - DESBLOQUEAR TODAS'])->only('desbloquearempresas');
    }

    public function desbloquearempresas()
    {

        $affected = DB::table('Contabilidade.Empresas')
               ->update(['Bloqueiodataanterior' => null ]);

        return redirect(route('Empresas.index'))->with("success","Desbloqueado todas empresas com sucesso!");
    }
    public function bloquearempresas(Request $request)
    {
        $DataConvertida = Carbon::createFromFormat("Y-m-d", $request->Bloqueardataanterior)->format("d/m/Y");
        $affected = DB::table('Contabilidade.Empresas')
               ->update(['Bloqueiodataanterior' => $DataConvertida]);

        return redirect(route('Empresas.index'))->with("success","Bloqueado todas empresas com sucesso!");
    }

    public function autenticar($empresaID)
    {
        $empresa = Empresa::find($empresaID);
        if ($empresa) {
            session(['Empresa' => $empresa]);

            return redirect('/PlanoContas/dashboard');
        }else {
            return redirect(route('Empresas.index'))->with('error','Emprese não localizada');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Filtros e paginação
        $q = trim((string) $request->input('q'));
        $allowedPerPage = [10, 15, 20, 30, 50, 100];
        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        $query = Empresa::query()
            ->join('Contabilidade.EmpresasUsuarios', 'EmpresasUsuarios.EmpresaID', '=', 'Empresas.ID')
            ->where('EmpresasUsuarios.UsuarioID', Auth()->user()->id)
            // selecionar colunas da tabela principal para manter os casts do modelo
            ->select('Contabilidade.Empresas.*', 'EmpresasUsuarios.EmpresaID');

        if ($q !== '') {
            $query->where('Contabilidade.Empresas.Descricao', 'like', "%{$q}%");
        }

        $cadastros = $query
            ->orderBy('Contabilidade.Empresas.Descricao')
            ->paginate($perPage)
            ->withQueryString();

        $linhas = $cadastros->total();
        session(['error' => '' ]);
        session(['success' => '']);

        return view('Empresas.index', [
            'cadastros' => $cadastros,
            'linhas' => $linhas,
            'q' => $q,
            'perPage' => $perPage,
            'allowedPerPage' => $allowedPerPage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Empresas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmpresaCreateRequest $request)
    {
        $dados = $request->all();

        // Adiciona o campo 'Created' com o valor datetime atual no formato correto
        $dados['Created'] = now();

        // Cria e salva a nova empresa diretamente
        $empresa = Empresa::create($dados);

        // Redireciona para a lista de empresas
        return redirect(route('Empresas.index'));
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Empresa::find($id);

        return view('Empresas.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = Empresa::find($id);

        return view('Empresas.edit', compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cadastro = Empresa::find($id);

        $cadastro->fill($request->all());
        // dd($cadastro);

        $cadastro->save();


        return redirect(route('Empresas.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cadastro = Empresa::find($id);
        $cadastro->delete();
        return redirect(route('Empresas.index'));
    }
}
