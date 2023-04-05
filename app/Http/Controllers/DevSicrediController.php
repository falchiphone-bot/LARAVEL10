<?php

namespace App\Http\Controllers;

use App\Http\Requests\DevSicrediCreateRequest;
use App\Models\DevSicredi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class DevSicrediController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:DevSicredi - LISTAR'])->only('index');
        $this->middleware(['permission:DevSicredi - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:DevSicredi - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:DevSicredi - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:DevSicredi - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */


    public function index()
    {
       $DevSicredi= DevSicredi::get();


        return view('DevSicredi.index',compact('DevSicredi'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('DevSicredi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DevSicrediCreateRequest $request)
    {
        $DevSicredi= $request->all();
        //dd($dados);

        DevSicredi::create($DevSicredi);

        return redirect(route('DevSicredi.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = DevSicredi::find($id);
        return view('DevSicredi.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $DevSicredi= DevSicredi::find($id);
        // dd($cadastro);

        return view('DevSicredi.edit',compact('DevSicredi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = DevSicredi::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('DevSicredi.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $DevSicredi = DevSicredi::find($id);


        $DevSicredi->delete();
        return redirect(route('DevSicredi.index'));

    }
}
