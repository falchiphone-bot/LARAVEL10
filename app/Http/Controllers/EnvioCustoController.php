<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\EnvioCusto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EnvioCustoController extends Controller
{
    /**
     * Gera PDF de custos agrupados por faixa salarial SAF.
     */
    public function pdfFaixa(Request $request)
    {
        // Novo requisito: PDF simples listando faixas salariais com valor mínimo, inspirado no layout de custos.
        $faixas = \App\Models\SafFaixaSalarial::orderBy('nome')->get();
        $totalMinimos = $faixas->sum(function($f){ return (float)$f->valor_minimo; });
        $pdf = \PDF::loadView('Envios.custos.pdf_faixa_minimo', [
            'faixas' => $faixas,
            'totalMinimos' => $totalMinimos,
        ]);
        return $pdf->download('faixas-salariais-valores-minimos.pdf');
    }

    /**
     * Novo PDF: faixas salariais vinculadas aos envios de um representante em um período,
     * seguindo o layout do PDF de custos por representante.
     */
    public function pdfFaixasFiltro(Request $request)
    {
        $request->validate([
            'representante_id' => 'required|exists:representantes,id',
            'data_ini' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_ini',
        ]);

        $rep = \App\Models\Representantes::findOrFail($request->representante_id);
        // Filtra envios do representante pelo created_at (assumido como critério de período)
        $envios = \App\Models\Envio::with('safFaixasSalariais')
            ->where('representante_id', $rep->id)
            ->whereBetween('created_at', [$request->data_ini.' 00:00:00', $request->data_fim.' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        $totalGeralMin = 0; $totalGeralMax = 0; $totalLinhas = 0;
        foreach ($envios as $e) {
            $totalGeralMin += $e->safFaixasSalariais->sum('valor_minimo');
            $totalGeralMax += $e->safFaixasSalariais->sum('valor_maximo');
            $totalLinhas += $e->safFaixasSalariais->count();
        }

        $pdf = \PDF::loadView('Envios.custos.pdf_filtro_faixas', [
            'rep' => $rep,
            'envios' => $envios,
            'request' => $request,
            'totalGeralMin' => $totalGeralMin,
            'totalGeralMax' => $totalGeralMax,
            'totalLinhas' => $totalLinhas,
        ]);
        return $pdf->download('faixas-representante-'.$rep->id.'-'.$request->data_ini.'-a-'.$request->data_fim.'.pdf');
    }

    /**
     * PDF de faixas salariais sem valor mínimo (nulo ou zero) por representante e período.
     */
    // Relatório: ENVIOs sem qualquer faixa salarial vinculada
    public function pdfEnviosSemFaixa(Request $request)
    {
        $request->validate([
            'representante_id' => 'nullable|exists:representantes,id',
            'data_ini' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_ini',
        ]);
        $rep = $request->representante_id ? \App\Models\Representantes::findOrFail($request->representante_id) : null;
        $ini = \Carbon\Carbon::parse($request->data_ini)->startOfDay();
        $fim = \Carbon\Carbon::parse($request->data_fim)->endOfDay();

        // Estratégia 1: whereDoesntHave (mais expressiva)
        $query1 = Envio::query()
            ->when($rep, fn($q) => $q->where('representante_id', $rep->id))
            ->whereBetween('created_at', [$ini, $fim])
            ->whereDoesntHave('safFaixasSalariais');
        $envios = $query1->orderBy('created_at')->get();

        // Estratégia 2 (fallback): LEFT JOIN + whereNull (caso algum provider SQL trate microsegundos de modo diferente)
        if ($envios->isEmpty()) {
            $query2 = Envio::leftJoin('envio_saf_faixa_salarial as piv', 'piv.envio_id', '=', 'envios.id')
                ->when($rep, fn($q) => $q->where('envios.representante_id', $rep->id))
                ->whereBetween('envios.created_at', [$ini, $fim])
                ->whereNull('piv.id')
                ->select('envios.*');
            $envios = $query2->orderBy('envios.created_at')->get();
        }

        // Estratégia 3 (defensiva): buscar todos e filtrar em memória
        if ($envios->isEmpty()) {
            $todos = Envio::with('safFaixasSalariais')
                ->when($rep, fn($q) => $q->where('representante_id', $rep->id))
                ->whereBetween('created_at', [$ini, $fim])
                ->get();
            $envios = $todos->filter(fn($e) => $e->safFaixasSalariais->isEmpty())->values();
        }

        // Log diagnóstico (apenas se app.debug = true)
        if (config('app.debug')) {
            try {
                $idsSemFaixa = $envios->pluck('id')->all();
                $candidatos = Envio::withCount('safFaixasSalariais')
                    ->when($rep, fn($q) => $q->where('representante_id', $rep->id))
                    ->whereBetween('created_at', [$ini, $fim])
                    ->get()
                    ->map(fn($e) => [
                        'id' => $e->id,
                        'created_at' => (string)$e->created_at,
                        'faixas_count' => $e->saf_faixas_salariais_count,
                    ]);
                // Debug específico solicitado: ids 16,18,19
                $debugIds = [16,18,19];
                $debugData = Envio::withCount('safFaixasSalariais')
                    ->whereIn('id', $debugIds)
                    ->get()
                    ->map(fn($e) => [
                        'id'=>$e->id,
                        'rep'=>$e->representante_id,
                        'created_at'=>(string)$e->created_at,
                        'faixas_count'=>$e->saf_faixas_salariais_count,
                    ]);
                Log::info('pdfEnviosSemFaixa diagnóstico', [
                    'rep_id' => $rep?->id,
                    'periodo' => [$ini->toDateTimeString(), $fim->toDateTimeString()],
                    'retornados' => $idsSemFaixa,
                    'candidatos' => $candidatos,
                    'debug_ids' => $debugData,
                ]);
            } catch (\Throwable $t) {
                Log::warning('Falha log diagnóstico pdfEnviosSemFaixa', ['err'=>$t->getMessage()]);
            }
        }

        $totalLinhas = $envios->count();

        $pdf = \PDF::loadView('Envios.custos.pdf_filtro_envios_sem_faixa', [
            'rep' => $rep,
            'envios' => $envios,
            'request' => $request,
            'totalLinhas' => $totalLinhas,
        ]);
    return $pdf->download('envios-sem-faixa-representante-'.$rep->id.'-'.$request->data_ini.'-a-'.$request->data_fim.'.pdf');
    }

    public function store(Request $request, Envio $envio)
    {
        $this->authorizeEnvio($envio);
        if (!auth()->user()->can('ENVIOS - CUSTOS - INCLUIR')) {
            abort(403, 'Você não tem permissão para incluir custos.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:150',
            'valor' => 'required|numeric|min:0',
            'data' => 'required|date',
        ]);
    // Garantir chave estrangeira mesmo se relação não propagar (edge-case binding)
        $validated['envio_id'] = $envio->getKey();
        if (empty($validated['envio_id'])) {
            // Fallback: pegar id direto da rota (param pode estar como 'Envio' ou 'envio')
            $routeId = $request->route('Envio') ?? $request->route('envio');
            if ($routeId) {
                $validated['envio_id'] = (int) $routeId;
            }
        }
        if (empty($validated['envio_id'])) {
            return back()->withErrors(['envio' => 'Não foi possível identificar o envio para registrar o custo.'])->withInput();
        }

        EnvioCusto::create($validated);

        // Recupera o ID de forma resiliente
        $envioId = $envio?->getKey() ?: ($validated['envio_id'] ?? ($request->route('Envio') ?? $request->route('envio')));
        if (!$envioId) {
            return redirect('/Envios')->with('warning', 'Custo criado, mas não foi possível redirecionar para edição (ID não resolvido).');
        }
        return redirect()->route('Envios.edit', ['Envio' => $envioId])->with('success', 'Custo adicionado.');
    }

    public function destroy(Request $request, $Envio, $custo)
    {
        $envio = Envio::findOrFail($Envio);
        $this->authorizeEnvio($envio);
        if (!auth()->user()->can('ENVIOS - CUSTOS - EXCLUIR')) {
            abort(403, 'Você não tem permissão para excluir custos.');
        }

        \Log::info('EnvioCustoController@destroy', [
            'envio_id' => $envio->id,
            'custo_param' => $custo,
            'route_params' => $request->route()->parameters(),
        ]);

        // Garantir sempre buscar pelo envio_id e id
        $custoModel = EnvioCusto::where('envio_id', $envio->id)
            ->where('id', $custo)
            ->first();

        $envioId = $envio->getKey();
        if (!$custoModel) {
            if ($envioId) {
                return redirect()->route('Envios.edit', ['Envio' => $envioId])
                    ->with('warning', 'Custo não encontrado ou já removido.');
            }
            return redirect('/Envios')->with('warning', 'Custo não encontrado e ID do envio não resolvido.');
        }

        $custoModel->delete();
        $envio->unsetRelation('custos');

        if ($request->expectsJson() || $request->ajax() || str_contains($request->header('Accept',''), 'application/json')) {
            $total = $envio->custos()->sum('valor');
            return response()->json([
                'ok' => true,
                'removed_id' => $custoModel->getKey(),
                'total_raw' => $total,
                'total_formatted' => number_format($total, 2, ',', '.'),
                'message' => 'Custo removido.'
            ]);
        }

        if ($envioId) {
            return redirect()->route('Envios.edit', ['Envio' => $envioId])
                ->with('success', 'Custo removido.');
        }
        return redirect('/Envios')->with('success', 'Custo removido, retorno genérico (ID não resolvido).');
    }

    public function update(Request $request, $Envio, $custo)
    {
        $envio = \App\Models\Envio::findOrFail($Envio);
        $this->authorizeEnvio($envio);
        if (!auth()->user()->can('ENVIOS - CUSTOS - EDITAR')) {
            abort(403, 'Você não tem permissão para editar custos.');
        }

        $custoModel = \App\Models\EnvioCusto::where('envio_id', $envio->id)->whereKey($custo)->firstOrFail();
        $data = $request->validate([
            'nome' => 'required|string|max:150',
            'valor' => 'required|numeric|min:0',
            'data' => 'required|date'
        ]);
        $custoModel->fill($data)->save();
        return redirect()->route('Envios.edit',['Envio'=>$envio->getKey()])->with('success','Custo atualizado.');
    }

    public function edit($Envio, $custo)
    {
        $envio = \App\Models\Envio::findOrFail($Envio);
        $custoModel = \App\Models\EnvioCusto::where('envio_id', $envio->id)
            ->where('id', $custo)
            ->firstOrFail();

        return view('Envios.custos.edit', [
            'envio' => $envio,
            'custo' => $custoModel
        ]);
    }

    public function pdf($Envio)
    {
        $envio = \App\Models\Envio::with('custos')->findOrFail($Envio);
        $custos = $envio->custos;
        $total = $custos->sum('valor');
        $pdf = \PDF::loadView('Envios.custos.pdf', compact('envio', 'custos', 'total'));
        return $pdf->download('custos-envio-' . $envio->id . '.pdf');
    }

    public function pdfFiltro(Request $request)
    {
        $request->validate([
            'representante_id' => 'required|exists:representantes,id',
            'data_ini' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_ini',
        ]);
        $rep = \App\Models\Representantes::findOrFail($request->representante_id);
        $custos = \App\Models\EnvioCusto::with('envio')
            ->whereHas('envio', function($q) use ($rep) {
                $q->where('representante_id', $rep->id);
            })
            ->whereBetween('data', [$request->data_ini, $request->data_fim])
            ->orderBy('data')
            ->get();

        $totalGeral = $custos->sum('valor');
        $pdf = \PDF::loadView('Envios.custos.pdf_filtro', compact('rep', 'custos', 'totalGeral', 'request'));
        return $pdf->download('custos-representante-'.$rep->id.'-'.$request->data_ini.'-a-'.$request->data_fim.'.pdf');
    }

    protected function authorizeEnvio(Envio $envio)
    {
        // Usa mesma lógica de edição de envio: permissões ou ser dono
        if (!Auth::user()) abort(403);

        $user = Auth::user();

        $canEdit = $user->can('ENVIOS - EDITAR') || $user->hasAnyRole('Super Admin','Administrador');
        if (!$canEdit && $envio->user_id !== $user->id) {
            abort(403);
        }
    }
}
