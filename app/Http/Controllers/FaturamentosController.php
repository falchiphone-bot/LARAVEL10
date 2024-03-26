<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaturamentoCreateRequest;
use App\Http\Requests\FaturamentosCreateRequest;
use App\Models;
use App\Models\Empresa;
use App\Models\Faturamentos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class FaturamentosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FATURAMENTOS - LISTAR'])->only('index');
        $this->middleware(['permission:FATURAMENTOS - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:FATURAMENTOS - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:FATURAMENTOS - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:FATURAMENTOS - EXCLUIR'])->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
       $faturamentos= Faturamentos::OrderBy('data')->get();

       $empresas = Empresa::OrderBy('Descricao')->get();

        return view('Faturamentos.index',compact('faturamentos','empresas'));
    }

    public function selecaoperiodoempresa(Request  $request )
    {

                $data_vencimento_inicial = $request->data_vencimento_inicial ;
                $data_vencimento_final = $request->data_vencimento_final; ;

                // $empresas = Empresa::
                // Where('X',0)->OrderBy('Descricao')->get();
                $empresas = Empresa::OrderBy('Descricao')->get();

                $faturamentos= Faturamentos::
                where('data','>=',$data_vencimento_inicial)
                ->where('data','<=',$data_vencimento_final)
                ->where('EmpresaID',$request->EmpresaID)
                ->OrderBy('data')->get();


                return view('Faturamentos.index',compact('faturamentos','empresas'));

     }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $empresas = Empresa::get();


        return view('Faturamentos.create',  compact('empresas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaturamentoCreateRequest $request)
    {

        $faturamentos= $request->all();
        $faturamentos['ValorFaturamento'] = str_replace(",",".",str_replace('.','',$faturamentos['ValorFaturamento']));
        $faturamentos['ValorImposto'] = str_replace(",",".",str_replace('.','',$faturamentos['ValorImposto']));

        $faturamentos['PercentualLucroLiquido'] = 32.00;

        $faturamentos['PercentualImposto'] = ($faturamentos['ValorImposto']/$faturamentos['ValorFaturamento'])*100;

        $faturamentos['ValorBaseLucroLiquido'] = ($faturamentos['ValorFaturamento'] - $faturamentos['ValorImposto']);
        $faturamentos['LucroLiquido'] = ($faturamentos['ValorBaseLucroLiquido'] *  $faturamentos['PercentualLucroLiquido'] )/100;


        $faturamentos['LancadoPor'] = Auth()->user()->email;

        faturamentos::create($faturamentos);


        return redirect(route('Faturamentos.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $faturamentos = faturamentos::find($id);

        $empresas = Empresa::get();
        // dd($empresas->first());
        return view('Faturamentos.show',compact('faturamentos',"empresas"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $faturamentos= faturamentos::find($id);

        $empresas = Empresa::get();
        return view('Faturamentos.edit',compact('faturamentos','empresas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $faturamentos = faturamentos::find($id);

        $faturamentos->fill($request->all()) ;
        $faturamentos['ValorFaturamento'] = str_replace(",",".",str_replace('.','',$faturamentos['ValorFaturamento']));
        $faturamentos['ValorImposto'] = str_replace(",",".",str_replace('.','',$faturamentos['ValorImposto']));

        $faturamentos['PercentualLucroLiquido'] = 32.00;

        $faturamentos['PercentualImposto'] = ($faturamentos['ValorImposto']/$faturamentos['ValorFaturamento'])*100;

        $faturamentos['ValorBaseLucroLiquido'] = ($faturamentos['ValorFaturamento'] - $faturamentos['ValorImposto']);
        $faturamentos['LucroLiquido'] = ($faturamentos['ValorBaseLucroLiquido'] *  $faturamentos['PercentualLucroLiquido'] )/100;
        $faturamentos->save();


        return redirect(route('Faturamentos.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $faturamentos = faturamentos::find($id);


        $faturamentos->delete();
        return redirect(route('Faturamentos.index'));

    }
}
