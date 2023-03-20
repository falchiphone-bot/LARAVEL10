<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role_has_PermissionCreateRequest;
use App\Models\Role_hasPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Role_has_PermissionController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $cadastros = Role_hasPermissions::get();


        return view('Role_has_Permissions.index',compact('cadastros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Role_has_Permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Role_has_PermissionCreateRequest $request)
    {
        $dados = $request->all();
        //dd($dados);

        Role_hasPermissions::create($dados);

        return redirect(route('Role_hasPermissoes.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Role_hasPermissions::find($id);

        return view('Role_has_Permissoes.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $cadastro = Role_hasPermissions::where('permission_id', $id)->get();

        // dd($cadastro);

        return view('Role_has_Permissions.edit',compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Role_hasPermissions::where('permission_id', $id)->get();

        $cadastro->fill($request->all()) ;
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());

        return redirect(route('TemFuncoes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cadastro = Role_hasPermissions::find($id);
        $cadastro->delete();
        return redirect(route('TemFuncoes.index'));

    }
}
