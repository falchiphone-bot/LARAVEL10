<?php

namespace App\Http\Controllers;

use App\Http\Requests\Irmaos_Emaus_EntradaSaidaRequest;
use App\Http\Requests\Irmaos_Emaus_FichaControleCreateRequest;
use App\Http\Requests\Irmaos_Emaus_RelatorioPiaRequest;
use App\Models\Irmaos_Emaus_EntradaSaida;
use App\Models\Irmaos_Emaus_FichaControle;
use App\Models\Irmaos_Emaus_RelatorioPia;
use App\Models\Irmaos_EmausPia;
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
        $this->middleware(['permission:IRMAOS_EMAUS_FICHA_CONTROLE - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:IRMAOS_EMAUS_FICHA_CONTROLE - ENVIAR_ARQUIVOS'])->only('enviarArquivos');
    }

    /**
     * Display a listing of the resource.
     */



    // public function index()
    // {
    //    $model= Irmaos_Emaus_FichaControle::OrderBy('Nome')->get();

    //     return view('Irmaos_Emaus_FichaControle.index',compact('model'));
    // }


 public function index(Request $request)
{
    $perPage = (int) $request->input('per_page', 5); // Valor padrão 5 linhas por página
    $sortBy = $request->input('sort_by', 'created_at');
    $sortDir = $request->input('sort_dir', 'desc');

    $query = Irmaos_Emaus_FichaControle::query();

    // Join para ordenar por nome do serviço
    if ($sortBy === 'Irmaos_EmausServicos.nomeServico') {
        $query->leftJoin('Irmaos_EmausServicos', 'Irmaos_Emaus_FichaControle.idServicos', '=', 'Irmaos_EmausServicos.id')
              ->orderBy('Irmaos_EmausServicos.nomeServico', $sortDir)
              ->select('Irmaos_Emaus_FichaControle.*');
    } elseif (in_array($sortBy, ['Nome', 'created_at', 'updated_at', 'user_created', 'user_updated'])) {
        $query->orderBy($sortBy, $sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    // Filtro por período (created_at)
    $dateStart = $request->input('date_start'); // formato esperado: Y-m-d
    $dateEnd = $request->input('date_end');     // formato esperado: Y-m-d

    try {
        if (!empty($dateStart)) {
            $start = Carbon::createFromFormat('Y-m-d', trim($dateStart))->startOfDay();
            $query->where('Irmaos_Emaus_FichaControle.created_at', '>=', $start);
        }
    } catch (\Exception $e) {
        // ignora data inválida silenciosamente para não quebrar a listagem
    }

    try {
        if (!empty($dateEnd)) {
            $end = Carbon::createFromFormat('Y-m-d', trim($dateEnd))->endOfDay();
            $query->where('Irmaos_Emaus_FichaControle.created_at', '<=', $end);
        }
    } catch (\Exception $e) {
        // ignora data inválida silenciosamente para não quebrar a listagem
    }

    // Filtro de busca
    if ($request->filled('search')) {
        $search = $request->input('search');

        // Tenta interpretar a busca como data para filtrar por Nascimento
        $dateYmd = null;
        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y', 'd.m.Y'] as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, trim($search));
                if ($d) { $dateYmd = $d->format('Y-m-d'); break; }
            } catch (\Exception $e) { /* ignora */ }
        }

        $query->where(function($q) use ($search) {
            $q->where('Irmaos_Emaus_FichaControle.Nome', 'like', "%{$search}%")
              // Serviço (join via relacionamento)
              ->orWhereHas('Irmaos_EmausServicos', function($q2) use ($search) {
                  $q2->where('nomeServico', 'like', "%{$search}%");
              })
              // Campos adicionais solicitados
              ->orWhere('Irmaos_Emaus_FichaControle.CidadeNaturalidade', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.UF_Naturalidade', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.Mae', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.Pai', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.Rg', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.Cpf', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.Nis', 'like', "%{$search}%");
        });

        if ($dateYmd) {
            $query->orWhereDate('Irmaos_Emaus_FichaControle.Nascimento', '=', $dateYmd);
        }
    }

    $model = $query->with('Irmaos_EmausServicos')->paginate($perPage)->appends($request->all());

    return view('Irmaos_Emaus_FichaControle.index', compact('model', 'perPage', 'sortBy', 'sortDir'));
}





    /**
     * Show the form for creating a new resource.
     */
    public function RelatorioPia(int $id)
    {
        $FichaControle = Irmaos_Emaus_FichaControle::find($id);
         $Irmaos_EmausPia = Irmaos_EmausPia::orderBy('nomePia')->pluck('nomePia', 'id');
        $idFichaControle = $FichaControle->id;
        return view('Irmaos_Emaus_FichaControle.createRelatorioPia',compact('FichaControle',
        'Irmaos_EmausPia', 'idFichaControle'));
    }
    public function GravaRelatorioPia(Irmaos_Emaus_RelatorioPiaRequest $request)
    {
        $RelatorioPia = $request->all();

        $RelatorioPia['user_created'] = auth()->user()->email;

        Irmaos_Emaus_RelatorioPia::create($RelatorioPia);

        // $rl = Irmaos_Emaus_RelatorioPia::find($RelatorioPia['idFichaControle']);
        // dd($rl);
        return redirect(route('Irmaos_Emaus_FichaControle.ListaRelatorioPia', $id = $RelatorioPia['idFichaControle']));
    }
    public function ListaRelatorioPia(string $id)
    {
       $model= Irmaos_Emaus_RelatorioPia::Where('idFichaControle', $id)
            ->select('id', 'idIrmaos_EmausPia','idFichaControle', 'Data', 'Anotacoes', 'user_created', 'created_at')
       ->OrderBy('id')->get();

        $FichaControle = Irmaos_Emaus_FichaControle::find($id);

        $nomeFichaControle = $FichaControle->Nome;
        $idFichaControle = $FichaControle->id;

         $Irmaos_EmausPia = Irmaos_EmausPia::orderBy('nomePia')->pluck('nomePia', 'id');

                 return view('Irmaos_Emaus_FichaControle.ListaRelatorioPia',compact('model', 'FichaControle',
        'nomeFichaControle', 'idFichaControle', 'Irmaos_EmausPia'));

    }

    public function ListaRelatorioPiaTopico(Request $request, $idFichaControle)
    {


        $id = $request->idIrmaos_EmausPia;

    // $idFichaControle já recebido como parâmetro


        $model= Irmaos_Emaus_RelatorioPia::Where('idFichaControle', $idFichaControle)
         ->Where('idIrmaos_EmausPia', $id)
            ->select('id', 'idIrmaos_EmausPia','idFichaControle', 'Data', 'Anotacoes', 'user_created', 'created_at')
       ->OrderBy('id')->get();

        $FichaControle = Irmaos_Emaus_FichaControle::find($idFichaControle);

        $nomeFichaControle = $FichaControle->Nome;
        $idFichaControle = $FichaControle->id;

         $Irmaos_EmausPia = Irmaos_EmausPia::orderBy('nomePia')->pluck('nomePia', 'id');
         $Irmaos_EmausPiaNome = Irmaos_EmausPia::find($id);
         $Irmaos_EmausPiaNome = $Irmaos_EmausPiaNome->nomePia;


// dd($request->all(), $idFichaControle, $id);
                 return view('Irmaos_Emaus_FichaControle.ListaRelatorioPia',compact('model', 'FichaControle',
        'nomeFichaControle', 'idFichaControle', 'Irmaos_EmausPia',  'Irmaos_EmausPiaNome'));
    }




    public function EntradaSaida(int $id)
    {
        $FichaControle = Irmaos_Emaus_FichaControle::find($id);
        $idFichaControle = $FichaControle->id;
        return view('Irmaos_Emaus_FichaControle.createEntradaSaida',compact('FichaControle', 'idFichaControle'));
    }
    public function GravaEntradaSaida(Irmaos_Emaus_EntradaSaidaRequest $request)
    {
        $EntradaSaida = $request->all();

        $EntradaSaida['user_created'] = auth()->user()->email;
        Irmaos_Emaus_EntradaSaida::create($EntradaSaida);

        return redirect(route('Irmaos_Emaus_FichaControle.ListaEntradaSaida', $id = $EntradaSaida['idFichaControle']));
    }
    public function ListaEntradaSaida(string $id)
    {
       $model= Irmaos_Emaus_EntradaSaida::Where('idFichaControle', $id)
            ->select('id', 'idFichaControle', 'TipoEntradaSaida', 'DataEntradaSaida', 'Anotacoes', 'user_created', 'created_at')
       ->OrderBy('id')->get();

        $FichaControle = Irmaos_Emaus_FichaControle::find($id);
        $nomeFichaControle = $FichaControle->Nome;
        $idFichaControle = $FichaControle->id;
        return view('Irmaos_Emaus_FichaControle.ListaEntradaSaida',compact('model', 'FichaControle', 'nomeFichaControle',
         'idFichaControle'));

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

    public function showenviarArquivos(string $id)
    {
        $cadastro = Irmaos_Emaus_FichaControle::find($id);

       $Irmaos_EmausServicos = Irmaos_EmausServicos::pluck('nomeServico', 'id');


        return view('Irmaos_Emaus_FichaControle.showEnviarArquivos',compact('cadastro', 'Irmaos_EmausServicos'));
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
