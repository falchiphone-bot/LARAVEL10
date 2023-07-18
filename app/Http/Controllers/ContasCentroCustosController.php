<?php

namespace App\Http\Controllers;


use App\Http\Requests\CentroCustosCreateRequest;
use App\Models\CentroCustos;
use App\Models\ContasCentroCustos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ContasCentroCustosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:CONTASCENTROCUSTOS - DASHBOARD'])->only('dashboard');
        $this->middleware(['permission:CONTASCENTROCUSTOS - LISTAR'])->only('index');
        $this->middleware(['permission:CONTASCENTROCUSTOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:CONTASCENTROCUSTOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:CONTASCENTROCUSTOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:CONTASCENTROCUSTOS - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */

     public function dashboard()
     {
         return view('ContasCentroCustos.dashboard');
     }

    public function index()
    {
       $ContasCentroCustos = ContasCentroCustos::OrderBy('CentroCustoID')->get();


        return view('ContasCentroCustos.index',compact('ContasCentroCustos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ContasCentroCustos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContasCentroCustosCreateRequest $request)
    {
        $ContasCentroCustos = $request->all();
        $ContasCentroCustos['Modified'] = Carbon::now()->format('d/m/Y H:i:s');
        $ContasCentroCustos['Created'] = Carbon::now()->format('d/m/Y H:i:s');
        $ContasCentroCustos['UsuarioID'] = auth()->user()->id;
        $ContasCentroCustos['EmpresaID'] = 0;


        ContasCentroCustos::create($ContasCentroCustos);

        return redirect(route('ContasCentroCustos.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = ContasCentroCustos::find($id);
        return view('ContasCentroCustos.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ContasCentroCustos= ContasCentroCustos::find($id);
        // dd($cadastro);

        return view('ContasCentroCustos.edit',compact('ContasCentroCustos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = ContasCentroCustos::find($id);
        $DescricaoAnterior = $cadastro->Descricao;
        $cadastro->update(['Descricao'=> $request->Descricao,'Modified' => Carbon::now()->format('d-m-Y H:i:s')]);

        session(['success' => ' Registro alterado com sucesso: De '.$DescricaoAnterior.' para '.$request->Descricao]);
        return redirect(route('ContasCentroCustos.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ContasCentroCustos = ContasCentroCustos::find($id);



        $contascentrocusto = ContasCentroCustos::where('CentroCustoID',$id)->first();


        if($contascentrocusto)
        {
            session(['error' => ' Registro sendo usado! Não posso excluir! ']);
            return redirect(route('ContasCentroCustos.index'));
        }

        $ContasCentroCustos->delete();

        session(['success2' => ' Registro excluído com sucesso ']);
        return redirect(route('ContasCentroCustos.index'));

    }
}



