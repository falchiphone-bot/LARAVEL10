<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleCreateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function salvarpermissao(Request $request, $idFuncao)
    {
        $funcao = Role::find($idFuncao);
        $funcao->syncPermissions($request->permissao);
        //$user->syncPermissions(['edit articles', 'delete articles']);
        return redirect("/Funcoes/".$idFuncao);
    }


    public function index()
    {
       $cadastros = Role::get();
       $linhas = count($cadastros);

        return view('Roles.index',compact('cadastros', 'linhas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleCreateRequest $request)
    {
        $dados = $request->all();
        //dd($dados);

        Role::create($dados);

        return redirect(route('Funcoes.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Role::find($id);
        $permissoes = Permission::orderBy('name')->pluck('name','id');
        return view('Roles.show',compact('cadastro','permissoes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = Role::find($id);
        // dd($cadastro);

        return view('Roles.edit',compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Role::find($id);

        $cadastro->fill($request->all()) ;
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());

        return redirect(route('Funcoes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);
        if ($role->permissions->count() > 0) {
            return back()->with('status','Essa funão tem permissões vinculadas.');
        }
        if ($role->users->count() > 0) {
            return back()->with('status','Essa funão tem usuários vinculados.');
        }

        $role->delete();
        return redirect(route('Funcoes.index'));

    }
}
