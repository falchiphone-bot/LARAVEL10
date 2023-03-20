<?php

namespace App\Http\Controllers;

use App\Http\Requests\Model_has_PermissionCreateRequest;
use App\Models\Model_hasPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Model_has_PermissionController extends Controller
{

    public function __construct()
    {
        $this->middleware( ["permission:TEM PERMISSOES - LISTAR"])->only("index");
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $cadastros = Model_hasPermissions::get();


        return view('Model_has_Permissions.index',compact('cadastros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Model_has_Permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Model_has_PermissionCreateRequest $request)
    {
        $dados = $request->all();
        //dd($dados);

        Model_hasPermissions::create($dados);

        return redirect(route('Model_hasPermissoes.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Model_hasPermissions::find($id);

        return view('Model_has_Permissoes.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $cadastro = Model_hasPermissions::where('permission_id', $id)->get();

        // dd($cadastro);

        return view('Model_has_Permissions.edit',compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Model_hasPermissions::where('permission_id', $id)->get();

        $cadastro->fill($request->all()) ;
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());

        return redirect(route('TemPermissoes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cadastro = Model_hasPermissions::find($id);
        $cadastro->delete();
        return redirect(route('TemPermissoes.index'));

    }
}
