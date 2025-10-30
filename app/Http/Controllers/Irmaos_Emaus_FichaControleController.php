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
        // Permissões específicas para RELATÓRIO PIA (reutiliza permissões de FICHA CONTROLE)
        $this->middleware(['permission:IRMAOS_EMAUS_FICHA_CONTROLE - EDITAR'])->only(['editRelatorioPia', 'updateRelatorioPia']);
        $this->middleware(['permission:IRMAOS_EMAUS_FICHA_CONTROLE - EXCLUIR'])->only(['destroyRelatorioPia']);
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

    // Campo do período selecionável (default: created_at)
    $allowedPeriodFields = [
        'created_at', 'updated_at', 'Nascimento', 'Entrada', 'Saida', 'EntradaPrimeiraVez', 'SaidaPrimeiraVez'
    ];
    $periodField = $request->input('period_field', 'created_at');
    if (!in_array($periodField, $allowedPeriodFields, true)) {
        $periodField = 'created_at';
    }

    // Filtro por período (aplicado no campo selecionado)
    $dateStart = $request->input('date_start'); // formato esperado: Y-m-d
    $dateEnd = $request->input('date_end');     // formato esperado: Y-m-d

    try {
        $start = null;
        if (!empty($dateStart)) {
            $start = Carbon::createFromFormat('Y-m-d', trim($dateStart))->startOfDay();
        }
    } catch (\Exception $e) {
        // ignora data inválida silenciosamente para não quebrar a listagem
    }

    try {
        $end = null;
        if (!empty($dateEnd)) {
            $end = Carbon::createFromFormat('Y-m-d', trim($dateEnd))->endOfDay();
        }
    } catch (\Exception $e) {
        // ignora data inválida silenciosamente para não quebrar a listagem
    }

    // Se ambos presentes e invertidos, normaliza
    if (isset($start, $end) && $start && $end && $start->gt($end)) {
        [$start, $end] = [$end, $start];
    }

    if (isset($start) && $start) {
        $query->where("Irmaos_Emaus_FichaControle.$periodField", '>=', $start);
    }
    if (isset($end) && $end) {
        $query->where("Irmaos_Emaus_FichaControle.$periodField", '<=', $end);
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
              ->orWhere('Irmaos_Emaus_FichaControle.Nis', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.Escolaridade', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.Prontuario', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.Livro', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.Folha', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.contatos', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.endereco', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.profissao', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.beneficios', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.observacoes', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.user_created', 'like', "%{$search}%")
              ->orWhere('Irmaos_Emaus_FichaControle.user_updated', 'like', "%{$search}%");
        });

        // Busca por Nascimento: por ano, mês/ano ou data exata
        // Padrões aceitos: YYYY | MM/YYYY | YYYY-MM | DD/MM/YYYY (além de variações já tratadas acima)
        $nascYear = null; $nascMonth = null; $nascExact = null;
        $raw = trim($search);
        if (preg_match('/^\d{4}$/', $raw)) { // YYYY
            $nascYear = (int) $raw;
        } elseif (preg_match('/^(\d{2})\/(\d{4})$/', $raw, $m)) { // MM/YYYY
            $nascMonth = (int) $m[1];
            $nascYear = (int) $m[2];
        } elseif (preg_match('/^(\d{4})\-(\d{2})$/', $raw, $m)) { // YYYY-MM
            $nascYear = (int) $m[1];
            $nascMonth = (int) $m[2];
        } else {
            // Tenta datas completas em vários formatos
            foreach (['d/m/Y', 'Y-m-d', 'd-m-Y', 'd.m.Y'] as $fmt) {
                try {
                    $d = Carbon::createFromFormat($fmt, $raw);
                    if ($d) { $nascExact = $d->format('Y-m-d'); break; }
                } catch (\Exception $e) { /* ignora */ }
            }
        }

        if ($nascExact) {
            $query->orWhereDate('Irmaos_Emaus_FichaControle.Nascimento', '=', $nascExact);
        } elseif ($nascYear && $nascMonth) {
            $query->orWhere(function($q) use ($nascYear, $nascMonth) {
                $q->whereYear('Irmaos_Emaus_FichaControle.Nascimento', '=', $nascYear)
                  ->whereMonth('Irmaos_Emaus_FichaControle.Nascimento', '=', $nascMonth);
            });
        } elseif ($nascYear) {
            $query->orWhereYear('Irmaos_Emaus_FichaControle.Nascimento', '=', $nascYear);
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

    /**
     * Formulário de edição de um registro do Relatório PIA.
     */
    public function editRelatorioPia(string $id)
    {
        $model = Irmaos_Emaus_RelatorioPia::findOrFail($id);
        $FichaControle = Irmaos_Emaus_FichaControle::findOrFail($model->idFichaControle);
        $Irmaos_EmausPia = Irmaos_EmausPia::orderBy('nomePia')->pluck('nomePia', 'id');
        $idFichaControle = $FichaControle->id;

        return view('Irmaos_Emaus_FichaControle.editRelatorioPia', compact('model', 'FichaControle', 'Irmaos_EmausPia', 'idFichaControle'));
    }

    /**
     * Atualiza um registro do Relatório PIA.
     */
    public function updateRelatorioPia(Irmaos_Emaus_RelatorioPiaRequest $request, string $id)
    {
        $model = Irmaos_Emaus_RelatorioPia::findOrFail($id);
        $model->fill($request->all());
        $model->user_updated = auth()->user()->email;
        $model->save();

        return redirect()->route('Irmaos_Emaus_FichaControle.ListaRelatorioPia', $model->idFichaControle)
            ->with('success', 'Relatório PIA atualizado com sucesso.');
    }

    /**
     * Exclui um registro do Relatório PIA.
     */
    public function destroyRelatorioPia(string $id)
    {
        $model = Irmaos_Emaus_RelatorioPia::findOrFail($id);
        $idFicha = $model->idFichaControle;
        $model->delete();

        return redirect()->route('Irmaos_Emaus_FichaControle.ListaRelatorioPia', $idFicha)
            ->with('success', 'Relatório PIA excluído com sucesso.');
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
