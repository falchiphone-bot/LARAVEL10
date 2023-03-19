<?php

namespace App\Http\Controllers;

use App\Http\Requests\Model_has_RoleCreateRequest;
use App\Models\Model_hasRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Model_has_RoleController extends Controller
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
       $cadastros = Model_hasRoles::get();


        return view('Model_has_Role.index',compact('cadastros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Model_has_Role.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Model_has_RoleCreateRequest $request)
    {
        $dados = $request->all();
        //dd($dados);

        Model_has_Roles::create($dados);

        return redirect(route('ModelodeFuncoes.index'));

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

        $cadastro = Role_hasRole::where('permission_id', $id)->get();

        $cadastro->fill($request->all()) ;
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());

        return redirect(route('ModelodeFuncoes.index'));
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
