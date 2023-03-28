<?php

namespace App\Http\Controllers;

use App\Http\Requests\Model_has_PermissionCreateRequest;
use App\Http\Requests\Model_has_RoleCreateRequest;
use App\Http\Requests\UserCreateRequest;
use App\Models\Empresa;
use App\Models\User;
use App\Models\EmpresaUsuario;
use Faker\Guesser\Name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Testing\Constraints\SeeInOrder;
use Spatie\Permission\Contracts\Permission as ContractsPermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        // $this->middleware( ["permission:USUARIOS - LISTAR"])->only("index");
        // $this->middleware( ["permission:USUARIOS - INCLUIR"])->only(["create","store"]);
        // $this->middleware( ["permission:USUARIOS - EDITAR"])->only(["edit","update"]);
        // $this->middleware( ["permission:USUARIOS - EXCLUIR"])->only("destroy");
    }
    /**public function __construct()
    {
        $this->middleware('auth');
    }**/

    public function salvarEmpresa(Request $request, $idusuario)
    {
        EmpresaUsuario::where('UsuarioID', $idusuario)->delete();
        foreach ($request->empresa as $empresaID) {
            $novo = new EmpresaUsuario();
            $novo->UsuarioID = $idusuario;
            $novo->EmpresaID = $empresaID;
            $novo->save();
        }
        return redirect('/Usuarios/' . $idusuario)->with('success', 'Empresas atualizadas');
    }
    public function salvarfuncao(Request $request, $idusuario)
    {
        $usuario = User::find($idusuario);
        $usuario->syncRoles($request->funcao);
        return redirect('/Usuarios/' . $idusuario)->with('success', 'Função atualizada');
    }

    public function salvarpermissao(Request $request, $idusuario)
    {
        $usuario = User::find($idusuario);
        $usuario->syncPermissions($request->permissao);
        //$user->syncPermissions(['edit articles', 'delete articles']);
        return redirect('/Usuarios/' . $idusuario)->with('success', 'Permissão atualizadas');
    }

    public function index()
    {
        $cadastros = User::get();
        $linhas = count($cadastros);

        return view('Users.index', compact('cadastros', 'linhas'));
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

        return redirect(route('Usuarios.index'))->with('success', 'Salvo com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = User::find($id);
        $permissoes = Permission::pluck('name', 'id');
        $funcoes = Role::pluck('name', 'id');
        $empresas = Empresa::orderBy('Descricao')->pluck('Descricao', 'ID');
        $empresaUsuarios = EmpresaUsuario::where('UsuarioID', $id)->get();

        return view('Users.show', compact('cadastro', 'permissoes', 'funcoes', 'empresas', 'empresaUsuarios'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = User::find($id);
        // dd($cadastro);

        return view('Users.edit', compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cadastro = User::find($id);

        $cadastro->fill($request->all());
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());

        return redirect(route('Usuarios.index'))->with('success', 'Atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        //PROTEGIDO PARA NÃO EXCLUIR CASO TIVER SENDO USADO EM ALGUMA MODEL.
        if ($user->permissions->count() > 0) {
            return back()->with('status', "Usuário {$user->name} tem permissão em uso.");
        }

        $user->delete();
        return back()->with('status', "Usuário {$user->name} EXCLUÍDO.");

        return redirect(route('Usuarios.index'))->with('success', 'Excluído com sucesso!');
    }
}
