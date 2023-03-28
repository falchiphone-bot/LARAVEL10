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
        $this->middleware(['permission:EMPRESAS - LISTAR'])->only('index');
        $this->middleware(['permission:EMPRESAS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:EMPRESAS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:EMPRESAS - EXCLUIR'])->only('destroy');
    }

    public function desbloquearempresas()
    {

        $affected = DB::table('Contabilidade.Empresas')
               ->update(['Bloqueiodataanterior' => null ]);



        return redirect(route('Empresas.index'))->with("success","Debloqueado todas empresas com sucesso!");
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
    public function index()
    {
        $cadastros = Empresa::join('Contabilidade.EmpresasUsuarios', 'EmpresasUsuarios.EmpresaID', '=', 'Empresas.ID')
            ->where('EmpresasUsuarios.UsuarioID', Auth()->user()->id)
            ->get();

        $linhas = count($cadastros);

        return view('Empresas.index', compact('cadastros', 'linhas'));
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
        //dd($dados);

        Empresa::create($dados);

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
        // dd($cadastro);

        return view('Empresas.edit', compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cadastro = Empresa::find($id);

        $cadastro->fill($request->all());
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());

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
