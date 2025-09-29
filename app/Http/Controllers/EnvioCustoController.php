<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\EnvioCusto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EnvioCustoController extends Controller
{
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
