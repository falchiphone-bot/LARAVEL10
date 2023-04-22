<?php

namespace App\Http\Livewire\Conta;

use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\SolicitacaoExclusao;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Extrato extends Component
{
    //mudança de empresas e contas
    public $selEmpresa;
    public $selConta;

    //models carregadas
    public $Conta;
    public $Lancamentos;
    public $Empresa;

    //criando lista de exclusão
    public $listaExclusao = [];
    public $listaSoma = [];

    //configurações de pesquisa
    public $De;
    public $Ate;
    public $Descricao;
    public $DescricaoApartirDe;
    public $Conferido;
    public $Notificacao;
    public $DataBloqueio;
    public $data_bloqueio_conta;
    public $data_bloqueio_empresa;

    public $exibicao_pesquisa;
    public $editar_lancamento = false;

    public function editarLancamento($lancamento_id)
    {
        $this->editar_lancamento = $lancamento_id;
        $this->dispatchBrowserEvent('abrir-modal');
    }

    protected $listeners = ['selectedSelEmpresaItem', 'selectedSelContaItem'];
    //gerenciamento select2
    public function selectedSelEmpresaItem($item)
    {
        if ($item) {
            $this->selEmpresa = $item;
            $this->Empresa = Empresa::find($item);
            $this->data_bloqueio_empresa = $this->Empresa->Bloqueiodataanterior?->format('Y-m-d');
            $this->selConta = null;
        } else {
            $this->selEmpresa = null;
            $this->selConta = null;
        }
        $this->updated();
    }
    public function selectedSelContaItem($item)
    {
        if ($item) {
            $this->selConta = $item;
            cache(['extrato_ContaID' => $item]);
            $this->Conta = Conta::find($item);
            $this->data_bloqueio_conta = $this->Conta->Bloqueiodataanterior?->format('Y-m-d');
        } else {
            $this->selConta = null;
        }
        $this->updated();
    }
    public function hydrate()
    {
        $this->emit('select2');
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function mount($contaID)
    {
        cache(['extrato_ContaID' => $contaID]);
        $this->De = cache('Extrato_De') ?? date('Y-m-d');
        $this->Ate = cache('Extrato_Ate') ?? date('Y-m-d');

        $this->Conta = Conta::find($contaID);
        $this->selEmpresa = $this->Conta->EmpresaID;
        $this->Empresa = Empresa::find($this->selEmpresa);
        $this->data_bloqueio_conta = $this->Conta->Bloqueiodataanterior?->format('Y-m-d');
        $this->data_bloqueio_empresa = $this->Empresa->Bloqueiodataanterior?->format('Y-m-d');
        $this->selConta = $this->Conta->ID;

        $de = Carbon::createFromDate($this->De)->format('d/m/Y 00:00:00');
        $ate = Carbon::createFromDate($this->Ate)->format('d/m/Y 23:59:59');

        $lancamentos = Lancamento::where(function ($query) use ($contaID) {
            return $query->where('ContaDebitoID', $contaID)->orWhere('ContaCreditoID', $contaID);
        })
        ->whereDoesntHave('SolicitacaoExclusao')
        ->where(function ($where) use ($de,$ate) {
            return $where->where('DataContabilidade','>=',$de)->where('DataContabilidade','<=',$ate);
        });

        $this->Lancamentos = $lancamentos->orderBy('DataContabilidade')->get();
    }

    public function updateDataBloqueioConta()
    {
        if (empty($this->data_bloqueio_conta)) {
            $this->data_bloqueio_conta = null;
        }
        $this->Conta->Bloqueiodataanterior = $this->data_bloqueio_conta;
        $this->Conta->save();
    }
    public function updateDataBloqueioEmpresa()
    {
        if (empty($this->data_bloqueio_empresa)) {
            $this->data_bloqueio_empresa = null;
        }
        $this->Empresa->Bloqueiodataanterior = $this->data_bloqueio_empresa;
        $this->Empresa->save();
    }
    public function temBloqueio($lancamento_id, $dataLancamento)
    {
        $dataLancamento = Carbon::createFromDate($dataLancamento);
        $data_conta = $this->Conta->Bloqueiodataanterior;

        if ($data_conta) {
            if ($data_conta->greaterThanOrEqualTo($dataLancamento)) {
                $this->addError('data_bloqueio', 'Não é possivel alterar esse lançamento para essa data, pois há um bloqueio de data na Conta - ID: '.$lancamento_id);
                return true;
            }
        }
        $data_empresa = $this->Empresa->Bloqueiodataanterior;
        if ($data_empresa) {
            if (Carbon::createFromDate($data_empresa)->greaterThanOrEqualTo($dataLancamento)) {
                $this->addError('data_bloqueio', 'Não é possivel alterar esse lançamento para essa data, pois há um bloqueio de data na Empresa - ID: '.$lancamento_id);
                return true;
            }
        }
        $lancamento = Lancamento::find($lancamento_id);
        if ($lancamento->ContaCreditoID == $this->Conta->ID) {
            $data_conta_partida = $lancamento->ContaDebito->Bloqueiodataanterior;
            $descricao = $lancamento->ContaDebito->PlanoConta->Descricao;
        } else {
            $data_conta_partida = $lancamento->ContaCredito->Bloqueiodataanterior;
            $descricao = $lancamento->ContaCredito->PlanoConta->Descricao;
        }
        if ($data_conta_partida) {
            if (Carbon::createFromDate($data_conta_partida)->greaterThanOrEqualTo($dataLancamento)) {
                $this->addError('data_bloqueio', 'Não é possivel alterar esse lançamento para essa data, pois há um bloqueio de data na Conta Partida: '.$descricao);
                return true;
            }
        }
        return false;
    }

    public function search()
    {
        $contaID = cache('extrato_ContaID') ?? $this->selConta;
        if ($contaID) {
            $lancamentos = Lancamento::where(function ($query) use ($contaID) {
                return $query->where('Lancamentos.ContaDebitoID', $contaID)->orWhere('Lancamentos.ContaCreditoID', $contaID);
            });

            if ($this->De) {
                $de = Carbon::createFromFormat('Y-m-d', $this->De)->format('d/m/Y 00:00:00');
                $lancamentos->where('DataContabilidade', '>=', $de);
                cache(['Extrato_De' => $this->De]);
            }
            if ($this->Ate) {
                $ate = Carbon::createFromFormat('Y-m-d', $this->Ate)->format('d/m/Y 23:59:59');
                $lancamentos->where('DataContabilidade', '<=', $ate);
                cache(['Extrato_Ate' => $this->Ate]);
            }
            if ($this->Descricao) {
                $lancamentos
                ->where(function ($q){
                    return $q->where('Lancamentos.Descricao', 'like', "%$this->Descricao%")
                    ->orWhere('Historicos.Descricao', 'like', "%$this->Descricao%");
                });
            }
            if ($this->Conferido != '') {
                $lancamentos->where('conferido', $this->Conferido);
            }
            if ($this->Notificacao != '') {
                $lancamentos->where('notificacao', $this->Notificacao);
            }
            $this->Lancamentos = $lancamentos->orderBy('DataContabilidade')
            ->whereDoesntHave('SolicitacaoExclusao')
            ->leftjoin('Contabilidade.Historicos','Historicos.ID','HistoricoID')
            ->get(["Lancamentos.ID",'Lancamentos.Valor','DataContabilidade',
            'Lancamentos.ContaCreditoID','Lancamentos.ContaDebitoID','Lancamentos.Descricao','Historicos.Descricao as HistoricoDescricao']);

        } else {
            $this->Lancamentos = null;
        }
    }

    public function confirmarLancamento($lancamento_id)
    {
        $lancamento = Lancamento::find($lancamento_id);
        if ($lancamento->Conferido) {
            $lancamento->Conferido = 0;
        } else {
            $lancamento->Conferido = 1;
        }
        $lancamento->save();
        $this->updated();
    }

    public function alterarDataVencidoRapido($lancamento_id, $acao)
    {
        $lancamento = Lancamento::find($lancamento_id);

        if ($this->temBloqueio($lancamento_id,$lancamento->DataContabilidade)) {
            return false;
        }
        $hoje = Carbon::now();
        if ($acao == 'ontem') {
            $novaData = $hoje->subDay();
        } elseif ($acao == 'hoje') {
            $novaData = $hoje;
        } elseif ($acao == 'amanha') {
            $novaData = $hoje->addDay();
        } else {
            $this->addError('alteraDataVencimenotRapido', 'Nenhuma ação selecionada');
        }

        $lancamento->DataContabilidade = $novaData->format('d/m/Y');
        $lancamento->save();
        $this->search();
    }

    public function incluirExclusao($lancamento_id,$data)
    {
        if ($this->temBloqueio($lancamento_id,$data)) {
            return false;
        }

        if (in_array($lancamento_id, $this->listaExclusao)) {
            // Remove o ID do lançamento se ele já estiver na lista
            $this->listaExclusao = array_diff($this->listaExclusao, [$lancamento_id]);
        } else {
            // Adiciona o ID do lançamento à lista se ele ainda não estiver presente
            $this->listaExclusao[] = $lancamento_id;
        }
        $this->emit('$refresh');

        // $this->dispatchBrowserEvent('update-button-delete', ['lancamento_id' => $lancamento_id,'array' => $this->listaExclusao])
    }

    public function somarLancamento($lancamento_id)
    {
        if (in_array($lancamento_id, $this->listaSoma)) {
            // Remove o ID do lançamento se ele já estiver na lista
            $this->listaSoma = array_diff($this->listaSoma, [$lancamento_id]);
        } else {
            // Adiciona o ID do lançamento à lista se ele ainda não estiver presente
            $this->listaSoma[] = $lancamento_id;
        }
        $this->emit('$refresh');
    }

    public function checkExclusao($lancamento_id)
    {
        return in_array($lancamento_id, $this->listaExclusao);
    }
    public function processarExclussao()
    {
        foreach ($this->listaExclusao as $lancamento_id) {
            SolicitacaoExclusao::create([
                'Tipo' => 2,
                'Descricao' => 'Lançamento',
                'UsuarioID' => Auth::user()->id,
                'Status' => '0',
                'Created' => date('d/m/Y H:i:s'),
                'Table' => 'lancamentos',
                'TableID' => $lancamento_id,
            ]);
            $this->listaExclusao = array_diff($this->listaExclusao, [$lancamento_id]);
            // $this->dispatchBrowserEvent('remove-line-exclusao', ['lancamento_id' => $lancamento_id]);
        }
        $this->search();
    }

    public function render()
    {
        $de = Carbon::createFromDate($this->De)->format('d/m/Y');
        $contaID = $this->selConta;
        $totalCredito = Lancamento::where(function ($q) use ($de,$contaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $de);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($de,$contaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $de);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoAnterior = $totalDebito - $totalCredito;

        $empresas = Empresa::whereHas('EmpresaUsuario', function ($query) {
            return $query->where('UsuarioID', Auth::user()->id);
        })
            ->orderBy('Descricao')
            ->pluck('Descricao', 'ID');
        $contas = Conta::where('EmpresaID', $this->selEmpresa)
            ->where('Grau', 5)
            ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', 'Planocontas_id')
            ->orderBy('PlanoContas.Descricao')
            ->pluck('PlanoContas.Descricao', 'Contas.ID');

        return view('livewire.conta.extrato', compact('empresas', 'contas', 'saldoAnterior'));
    }
}
