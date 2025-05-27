<?php

namespace App\Http\Controllers;

use App\Http\Requests\Irmaos_Emaus_EntradaSaidaRequest;
use App\Http\Requests\Irmaos_Emaus_FichaControleCreateRequest;
use App\Models\Irmaos_Emaus_EntradaSaida;
use App\Models\Irmaos_Emaus_FichaControle;
use App\Models\Irmaos_EmausEntradaSaida;
use App\Models\Irmaos_EmausServicos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class Irmaos_Emaus_FichaControleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR'])->only('index');
        $this->middleware(['permission:IRMAOS_EMAUS_FICHA_CONTROLE - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:IRMAOS_EMAUS_FICHA_CONTROLE - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:IRMAOS_EMAUS_FICHA_CONTROLE - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:IRMAOS_EMAUS_FICHA_CONTROLE - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $model= Irmaos_Emaus_FichaControle::OrderBy('Nome')->get();

        return view('Irmaos_Emaus_FichaControle.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function EntradaSaida(int $id)
    {
        $FichaControle = Irmaos_Emaus_FichaControle::find($id);
        return view('Irmaos_Emaus_FichaControle.createEntradaSaida',compact('FichaControle'));
    }
    public function GravaEntradaSaida(Irmaos_Emaus_EntradaSaidaRequest $request)
    {

    //    dd($request);

        $EntradaSaida = $request->all();


        $EntradaSaida['user_created'] = auth()->user()->email;
        // $EntradaSaida['Empresa'] = 1039;

// dd($EntradaSaida);
        Irmaos_Emaus_EntradaSaida::create($EntradaSaida);

        return redirect(route('Irmaos_Emaus_FichaControle.ListaEntradaSaida', $id = $EntradaSaida['idFichaControle']));

    }
    public function ListaEntradaSaida(string $id)
    {
       $model= Irmaos_Emaus_EntradaSaida::Where('idFichaControle', $id)
            ->select('id', 'idFichaControle', 'TipoEntradaSaida', 'DataEntradaSaida', 'Anotacoes', 'user_created', 'created_at')
       ->OrderBy('id')->get();

        $FichaControle = Irmaos_Emaus_FichaControle::find($id);
        return view('Irmaos_Emaus_FichaControle.ListaEntradaSaida',compact('model', 'FichaControle'));

    }


    public function create()
    {

        $Irmaos_EmausServicos = Irmaos_EmausServicos::pluck('nomeServico', 'id');

        return view('Irmaos_Emaus_FichaControle.create',compact('Irmaos_EmausServicos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Irmaos_Emaus_FichaControleCreateRequest $request)
    {

    //    dd($request);

        $FichaControle = $request->all();




        $FichaControle['user_created'] = auth()->user()->email;
        $FichaControle['Empresa'] = 1039;


        Irmaos_Emaus_FichaControle::create($FichaControle);

        return redirect(route('Irmaos_Emaus_FichaControle.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Irmaos_Emaus_FichaControle::find($id);

       $Irmaos_EmausServicos = Irmaos_EmausServicos::pluck('nomeServico', 'id');

        return view('Irmaos_Emaus_FichaControle.show',compact('cadastro', 'Irmaos_EmausServicos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= Irmaos_Emaus_FichaControle::find($id);

        $Irmaos_EmausServicos = Irmaos_EmausServicos::pluck('nomeServico', 'id');
        // dd($Irmaos_EmausServicos);

        return view('Irmaos_Emaus_FichaControle.edit',compact('model', 'Irmaos_EmausServicos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Irmaos_Emaus_FichaControleCreateRequest $request, string $id)
    {

        $FichaControle = Irmaos_Emaus_FichaControle::find($id);

        $FichaControle->fill($request->all()) ;


        $FichaControle->user_updated = auth()->user()->email;



        $FichaControle->save();


        return redirect(route('Irmaos_Emaus_FichaControle.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $servicos= Irmaos_Emaus_FichaControle::find($id);


        $servicos->delete();
        return redirect(route('Irmaos_Emaus_FichaControle.index'));

    }
}
