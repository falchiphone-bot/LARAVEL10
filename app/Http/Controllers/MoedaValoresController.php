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
use Illuminate\Support\Facades\Cache;
use App\Services\MoedaVariacaoService;


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


    public function index(Request $request, MoedaVariacaoService $variacaoService)
    {
        $ordem = $request->get('ordem', 'desc');
        $baseVariacao = $request->get('base_variacao', 'anterior');
        $perPage = (int) $request->get('per_page', 25);
        $perPage = $perPage > 0 && $perPage <= 200 ? $perPage : 25;
        $moedaId = $request->get('moeda_id');
        $page = $request->get('page', 1);

        $cacheKey = "moedasvalores:index:moeda:" . ($moedaId ?: 'all') . ":$ordem:$baseVariacao:$perPage:$page";

        $paginado = Cache::remember($cacheKey, 300, function () use ($ordem, $perPage, $moedaId) {
            $query = MoedasValores::query();
            if ($moedaId) {
                $query->where('idmoeda', $moedaId);
            }
            return $query->orderBy('data', $ordem)->paginate($perPage);
        });

        $colecao = collect($paginado->items());
        $variacaoService->atribuir($colecao, $baseVariacao);

        $moedas = Moeda::get();

        return view('MoedasValores.index', [
            'moedasvalores' => $colecao,
            'moedas' => $moedas,
            'ordem' => $ordem,
            'baseVariacao' => $baseVariacao,
            'paginacao' => $paginado,
            'perPage' => $perPage,
            'moedaSelecionada' => $moedaId,
        ]);
    }

    public function selecionarMoeda(Request $request)
    {
        // Converte POST em redirect GET para evitar problemas com resource show capturando segmentos
        $params = [
            'moeda_id' => $request->moeda_id,
            'ordem' => $request->ordem ?? 'desc',
            'base_variacao' => $request->base_variacao ?? 'anterior',
            'per_page' => $request->per_page ?? 25,
        ];
        return redirect()->route('MoedasValores.index', $params);
    }

    /**
     * Atribui a cada item da coleção a variação percentual em relação ao dia posterior
     * (ou anterior dependendo da ordenação). A variação é calculada como:
     * (valor_dia_posterior - valor_atual) / valor_atual * 100
     * Se valor_atual for 0 ou não houver dia posterior, retorna null.
     */
    // Método legado de variação removido (refatorado para serviço)

    public function clearCache()
    {
        Cache::flush();
        return redirect()->back()->with('success', 'Cache limpo com sucesso.');
    }

    public function exportCsv(Request $request, MoedaVariacaoService $variacaoService)
    {
        $ordem = $request->get('ordem', 'asc');
        $baseVariacao = $request->get('base_variacao', 'posterior');
        $moedaId = $request->get('moeda_id');

        $query = MoedasValores::query();
        if ($moedaId) {
            $query->where('idmoeda', $moedaId);
        }
        // eager load para evitar N+1
        $registros = $query->with('ValoresComMoeda')->orderBy('data', $ordem)->get();
        $variacaoService->atribuir($registros, $baseVariacao);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="moedas_valores.csv"',
        ];

        $callback = function () use ($registros, $baseVariacao) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['data', 'valor', 'moeda', 'variacao_tipo', 'variacao_percentual', 'valor_comparacao', 'data_comparacao']);
            foreach ($registros as $r) {
                fputcsv($out, [
                    optional($r->data)->format('d/m/Y'),
                    $r->valor,
                    optional($r->ValoresComMoeda)->nome ?? $r->idmoeda,
                    $r->variacao_tipo,
                    is_null($r->variacao_percentual) ? null : number_format($r->variacao_percentual, 6, '.', ''),
                    $r->variacao_valor_comparacao,
                    $r->variacao_data_comparacao ? $r->variacao_data_comparacao->format('d/m/Y') : null,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
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

                // Persistir aviso se a data utilizada for anterior a hoje
                try {
                    $isOutdated = \Carbon\Carbon::parse($dataUsada)->lt(now()->startOfDay());
                    if ($isOutdated) {
                        session()->put('moedas.cotacao_aviso', [
                            'moeda_id' => $moeda->id,
                            'moeda_nome' => $moeda->nome,
                            'data_utilizada' => \Carbon\Carbon::parse($dataUsada)->format('d/m/Y'),
                            'fonte' => 'api',
                            'provider' => $externo['provider'] ?? null,
                            'created_at' => now()->toDateTimeString(),
                        ]);
                    } else {
                        session()->forget('moedas.cotacao_aviso');
                    }
                } catch (\Throwable $e) {
                    // silencioso
                }

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
        // Persistir aviso se a data utilizada for anterior a hoje (base local)
        try {
            $dataUsadaLocal = optional($registro->data)->toDateString();
            if ($dataUsadaLocal) {
                $isOutdated = \Carbon\Carbon::parse($dataUsadaLocal)->lt(now()->startOfDay());
                if ($isOutdated) {
                    session()->put('moedas.cotacao_aviso', [
                        'moeda_id' => $moeda->id,
                        'moeda_nome' => $registro->ValoresComMoeda->nome ?? $moeda->nome,
                        'data_utilizada' => \Carbon\Carbon::parse($dataUsadaLocal)->format('d/m/Y'),
                        'fonte' => 'local',
                        'provider' => null,
                        'created_at' => now()->toDateTimeString(),
                    ]);
                } else {
                    session()->forget('moedas.cotacao_aviso');
                }
            }
        } catch (\Throwable $e) {
            // silencioso
        }

        return view('MoedasValores.consultar', compact('resultado', 'moeda', 'dataRef', 'fonte'));
    }

    /**
     * Persiste um novo registro a partir do retorno da consulta API
     */
    public function salvarDaConsulta(Request $request)
    {
        $validated = $request->validate([
            'idmoeda' => 'required|exists:moedas,id',
            'data' => 'required|date',
            'valor' => 'required|numeric',
        ]);

        // Checar duplicidade: já existe registro para a mesma moeda e data?

        $exists = MoedasValores::where('idmoeda', $validated['idmoeda'])
            ->whereDate('data', $validated['data'])
            ->exists();

        if ($exists) {
            return redirect()->route('MoedasValores.index')
                ->with('error', 'Já existe um registro para esta moeda e data.');
        }

        $payload = [
            'idmoeda' => (int) $validated['idmoeda'],
            'data' => $validated['data'],
            'valor' => (float) $validated['valor'],
        ];

        MoedasValores::create($payload);

        return redirect()->route('MoedasValores.index')
            ->with('success', 'Registro salvo com sucesso a partir da consulta da API.');
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
    // Invalida cache simples
    Cache::flush();

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
    Cache::flush();


        return redirect(route('MoedasValores.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $moedasvalores = MoedasValores::find($id);


    $moedasvalores->delete();
    Cache::flush();
        return redirect(route('MoedasValores.index'));

    }
}
