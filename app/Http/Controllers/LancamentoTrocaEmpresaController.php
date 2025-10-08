<?php

namespace App\Http\Controllers;

use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Lancamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LancamentoTrocaEmpresaController extends Controller
{
    /**
     * Retorna contas Grau 5 de uma empresa para preencher selects (fallback sem Livewire)
     */
    public function contas(Empresa $empresa)
    {
        // Verifica se usuário realmente tem acesso a esta empresa (mesma lógica do componente)
        $usuarioId = Auth::id();
        $allowed = Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
            ->where('EmpresasUsuarios.UsuarioID',$usuarioId)
            ->where('Empresas.ID',$empresa->ID)
            ->exists();
        if(!$allowed){
            return response()->json(['message' => 'Empresa não autorizada'], 403);
        }
        $idsParam = request('ids');
        if($idsParam){
            $ids = collect(explode(',', $idsParam))
                ->map(fn($v)=> (int)trim($v))
                ->filter(fn($v)=> $v>0)
                ->unique()
                ->take(200) // segurança
                ->values();
            $contas = Conta::where('EmpresaID',$empresa->ID)
                ->whereIn('Contas.ID',$ids)
                ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
                ->orderBy('PlanoContas.Descricao')
                ->pluck('PlanoContas.Descricao','Contas.ID');
            return response()->json(['data'=>$contas,'mode'=>'ids']);
        }
        $q = request('q');
        $query = Conta::where('EmpresaID',$empresa->ID)->where('Grau',5)
            ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id');
        if($q){
            $query->where('PlanoContas.Descricao','like','%'.trim($q).'%');
        }
        $contas = $query->orderBy('PlanoContas.Descricao')
            ->limit(100) // limita para performance em buscas
            ->pluck('PlanoContas.Descricao','Contas.ID');
        return response()->json(['data' => $contas,'mode'=>'search']);
    }

    /**
     * Processa a troca de empresa sem Livewire.
     */
    public function store(Request $request, Lancamento $lancamento)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'novaempresa' => 'required|integer',
            'novacontadebito' => 'required|integer',
            'novacontacredito' => 'required|integer',
        ]);
        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();
        }

        if($this->temBloqueio($lancamento)){
            return Redirect::back()->withErrors(['data_bloqueio' => 'Lançamento está em período bloqueado'])->withInput();
        }

        $lancamento->EmpresaID = (int)$data['novaempresa'];
        $lancamento->ContaDebitoID = (int)$data['novacontadebito'];
        $lancamento->ContaCreditoID = (int)$data['novacontacredito'];
        if($lancamento->save()){
            session()->flash('message','Lançamento transferido (modo simples)');
        } else {
            return Redirect::back()->withErrors(['save' => 'Erro ao salvar lançamento'])->withInput();
        }
        return Redirect::back()->with('currentTab','troca-empresa');
    }

    private function temBloqueio(Lancamento $lancamento): bool
    {
        $dataLancamento = Carbon::createFromDate($lancamento->DataContabilidade);
        $contaDebito = $lancamento->ContaDebito; // relações presumidas carregáveis lazy
        if($contaDebito && $contaDebito->Bloqueiodataanterior && $contaDebito->Bloqueiodataanterior->greaterThanOrEqualTo($dataLancamento)){
            return true;
        }
        $empresa = Empresa::find($lancamento->EmpresaID);
        if($empresa && $empresa->Bloqueiodataanterior && $empresa->Bloqueiodataanterior->greaterThanOrEqualTo($dataLancamento)){
            return true;
        }
        $contaCredito = $lancamento->ContaCredito;
        if($contaCredito && $contaCredito->Bloqueiodataanterior && $contaCredito->Bloqueiodataanterior->greaterThanOrEqualTo($dataLancamento)){
            return true;
        }
        return false;
    }
}
