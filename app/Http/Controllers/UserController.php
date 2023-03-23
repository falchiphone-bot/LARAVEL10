<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {


        $this->middleware( ["permission:USUARIOS - LISTAR"])->only("index");
        $this->middleware( ["permission:USUARIOS - INCLUIR"])->only(["create","store"]);
        $this->middleware( ["permission:USUARIOS - EDITAR"])->only(["edit","update"]);
        $this->middleware( ["permission:USUARIOS - EXCLUIR"])->only("destroy");

    }
    /**public function __construct()
    {
        $this->middleware('auth');
    }**/


    public function salvarfuncao(Request $request, $idusuario)
    {
        $usuario = User::find($idusuario);
        $usuario->syncRoles($request->funcao);
        return redirect("/Usuarios/".$idusuario);
    }

    public function salvarpermissao(Request $request, $idusuario)
    {
        $usuario = User::find($idusuario);
        $usuario->syncPermissions($request->permissao);
        //$user->syncPermissions(['edit articles', 'delete articles']);
        return redirect("/Usuarios/".$idusuario);
    }



    public function index()
    {
        $cadastros = User::get();
        $linhas = count($cadastros);
     
        return view('Users.index',compact('cadastros', 'linhas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserCreateRequest $request)
    {


        $dados = $request->all();
        //dd($dados);

        User::create($dados);

        return redirect(route('Usuarios.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = User::find($id);
        $permissoes = Permission::pluck('name','id');
        $funcoes = Role::pluck('name','id');

        return view('Users.show',compact('cadastro', 'permissoes','funcoes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = User::find($id);
        // dd($cadastro);

        return view('Users.edit',compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = User::find($id);

        $cadastro->fill($request->all()) ;
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());

        return redirect(route('Usuarios.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cadastro = User::find($id);
        $cadastro->delete();
        return redirect(route('Usuarios.index'));

    }
}
