<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoedaValoresCreateRequest;
use App\Models\Moeda;
use App\Models\MoedasValores;
use App\Models\MoedaValores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\CambioService;


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

        $ordem = 'desc';

        $moedasvalores= MoedasValores::limit(100)
       ->OrderBy('data','desc')->get();

       $moedas = Moeda::get();

        return view('MoedasValores.index',compact('moedasvalores','moedas','ordem'));
    }

    public function selecionarMoeda(Request $request)
    {

        $ordem = $request->ordem;

        $moedasvalores = MoedasValores::where('idmoeda', $request->moeda_id)
        ->OrderBy('data',$ordem)
        ->get();


        $moedas = Moeda::get();

        return view('MoedasValores.index',compact('moedasvalores','moedas','ordem'));
    }

    /**
     * Consulta o valor da moeda na data informada (ou o último valor anterior)
     */
    public function consultarValor(Request $request)
    {

// dd($request->all());

        $validated = $request->validate([
            'moeda_id' => 'required|exists:moedas,id',
            'data_referencia' => 'nullable|date',
            'fonte' => 'nullable|in:api,local',
        ]);


        $dataRef = $validated['data_referencia'] ?? now()->toDateString();
        $fonte = $validated['fonte'] ?? 'api';
        $moeda = Moeda::find($validated['moeda_id']);



        // Tenta fonte externa primeiro se selecionado API
        if ($fonte === 'api') {
            $cambio = new CambioService();
            $codigo = $cambio->resolverCodigoMoeda($moeda->nome);
            $externo = $cambio->cotacaoParaBRL($moeda->nome, $dataRef);

            if ($externo) {
                $dataUsada = $externo['data_utilizada'] ?? $dataRef;
                $mensagem = sprintf(
                    'Valor em %s (%s->BRL via API): %s',
                    \Carbon\Carbon::parse($dataUsada)->format('d/m/Y'),
                    $externo['codigo'],
                    number_format($externo['valor'], 4, ',', '.')
                );

                $resultado = [
                    'status' => 'success',
                    'mensagem' => $mensagem,
                    'origem' => 'api',
                    'codigo' => $externo['codigo'],
                    'valor_formatado' => number_format($externo['valor'], 4, ',', '.'),
                    'moeda_nome' => $moeda->nome,
                    'data_formatada' => \Carbon\Carbon::parse($dataUsada)->format('d/m/Y'),
                    'data_referencia' => \Carbon\Carbon::parse($dataRef)->format('d/m/Y'),
                    'data_utilizada' => \Carbon\Carbon::parse($dataUsada)->format('d/m/Y'),
                    'provider' => $externo['provider'] ?? 'exchangerate.host',
                ];



                return view('MoedasValores.consultar', compact('resultado', 'moeda', 'dataRef', 'fonte'));
            }
            Log::warning('API de câmbio sem retorno (externo=null)', [
                'moeda_nome' => $moeda->nome,
                'codigo_resolvido' => $codigo,
                'data_referencia' => $dataRef,
                'fonte' => $fonte,
            ]);
        }

        // Fallback banco local
        $registro = MoedasValores::where('idmoeda', $validated['moeda_id'])
            ->whereDate('data', '<=', $dataRef)
            ->orderBy('data', 'desc')
            ->first();

        if (!$registro) {
            // Nenhum valor encontrado até a data informada
            $resultado = [
                'status' => 'error',
                'mensagem' => 'Nenhum valor encontrado para a moeda até a data informada.',
                'origem' => 'local',
                'moeda_nome' => $moeda->nome,
                'data_formatada' => \Carbon\Carbon::parse($dataRef)->format('d/m/Y'),
            ];
            return view('MoedasValores.consultar', compact('resultado', 'moeda', 'dataRef', 'fonte'));
        }

        // Passa mensagem com o valor encontrado
        $mensagem = sprintf(
            'Valor em %s (%s - base local): %s',
            optional($registro->data)->format('d/m/Y'),
            $registro->ValoresComMoeda->nome ?? 'Moeda',
            number_format($registro->valor, 4, ',', '.')
        );

        $resultado = [
            'status' => 'success',
            'mensagem' => $mensagem,
            'origem' => 'local',
            'valor_formatado' => number_format($registro->valor, 4, ',', '.'),
            'moeda_nome' => $registro->ValoresComMoeda->nome ?? $moeda->nome,
            'data_formatada' => optional($registro->data)->format('d/m/Y'),
        ];

        return view('MoedasValores.consultar', compact('resultado', 'moeda', 'dataRef', 'fonte'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $Moedas = Moeda::get();

        return view('MoedasValores.create',  compact('Moedas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MoedaValoresCreateRequest $request)
    {
        $moedasvalores = $request->all();

        $moedasvalores['valor'] = str_replace(",",".",str_replace('.','',$moedasvalores ['valor']));
//  dd($moedasvalores);
        MoedasValores::create($moedasvalores);

        return redirect(route('MoedasValores.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $moedasvalores = MoedasValores::find($id);

        $Moedas = Moeda::get();
        return view('MoedasValores.show',compact('moedasvalores', 'Moedas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $moedasvalores= MoedasValores::find($id);

        $Moedas = Moeda::get();
        return view('MoedasValores.edit',compact('moedasvalores', 'Moedas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $moedasvalores = MoedasValores::find($id);

        $moedasvalores->fill($request->all()) ;

        $moedasvalores['valor'] = str_replace(",",".",str_replace('.','',$moedasvalores ['valor']));

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
