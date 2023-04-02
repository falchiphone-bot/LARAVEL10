<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionCreateRequest;
use App\Models\Permissions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $cadastros = Permission::OrderBy('name')->get();
       $linhas = count($cadastros);

       return view('Permissions.index',compact('cadastros', 'linhas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermissionCreateRequest $request)
    {
        $dados = $request->all();
        //dd($dados);

        Permission::create($dados);

        return redirect(route('Permissoes.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Permission::find($id);

        return view('Permissoes.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = Permission::find($id);
        // dd($cadastro);

        return view('Permissions.edit',compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Permission::find($id);

        $cadastro->fill($request->all()) ;
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());

        return redirect(route('Permissoes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::find($id);
        $users = User::permission($permission->name)->get();
        if ($users->count() > 0) {
            return back()->with('status','Permissão em uso por '.$users->count().' usuarios.');
        }
        $roles = Role::whereHas('permissions', function ($query) use ($permission) {
            $query->where('id', $permission->id);
        })->get();
        if ($roles->count() > 0) {
            return back()->with('status','Permissão em uso por '.$roles->count().' funções.');
        }

        $permission->delete();
        return redirect(route('Permissoes.index'));

    }
}
