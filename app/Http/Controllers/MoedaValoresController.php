<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoedaValoresCreateRequest;
use App\Models\MoedasValores;
use App\Models\MoedaValores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class MoedaValoresController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:MOEDASVALORES - LISTAR'])->only('index');
        $this->middleware(['permission:MOEDASMOEDASVALORES - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:MOEDASMOEDASVALORES - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:MOEDASMOEDASVALORES - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:MOEDASMOEDASVALORES - EXCLUIR'])->only('destroy');
    }




    /**
     * Display a listing of the resource.
     */


    public function index()
    {
       $moedasvalores= MoedasValores::get();


        return view('MoedasValores.index',compact('moedasvalores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('MoedasValores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MoedaValoresCreateRequest $request)
    {
        $moedasvalores= $request->all();
        //dd($dados);

        MoedasValores::create($moedasvalores);

        return redirect(route('MoedasValores.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $moedasvalores = MoedasValores::find($id);
        return view('MoedasValores.show',compact('moedasvalores'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $moedasvalores= MoedasValores::find($id);
        // dd($cadastro);

        return view('MoedasValores.edit',compact('moedasvalores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $moedasvalores = MoedasValores::find($id);

        $moedasvalores->fill($request->all()) ;


        $moedasvalores->save();


        return redirect(route('MoedasValores.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $moedasvalores = MoedasValores::find($id);


        $moedasvalores->delete();
        return redirect(route('MoedasValores.index'));

    }
}
