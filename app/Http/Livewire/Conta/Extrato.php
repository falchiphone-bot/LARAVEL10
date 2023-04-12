<?php

namespace App\Http\Livewire\Conta;

use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Lancamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Extrato extends Component
{
    //mudança de empresas e contas
    public $selEmpresa;
    public $selConta;

    public $Conta;
    public $Lancamentos;

    //criando lista de exclusão
    public $listaExclusao = [];

    //configurações de pesquisa
    public $De;
    public $Ate;
    public $Descricao;
    public $DescricaoApartirDe;
    public $Conferido;
    public $Notificacao;
    public $DataBloqueio;
    public $data_bloqueio;

    public $exibicao_pesquisa;

    protected $listeners = ['selectedSelEmpresaItem','selectedSelContaItem'];
    //gerenciamento select2
    public function selectedSelEmpresaItem($item)
    {
        if ($item) {
            $this->selEmpresa = $item;
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
        $this->De = cache('Extrato_De') ?? date('Y-m-d');
        $this->Ate = cache('Extrato_Ate') ?? date('Y-m-d');
        $this->Conta = Conta::find($contaID);
        $this->data_bloqueio = $this->Conta->Bloqueiodataanterior?->format('Y-m-d');
        $this->selEmpresa = $this->Conta->EmpresaID;
        $this->selConta = $this->Conta->ID;

        $de = Carbon::createFromDate($this->De)->format('d/m/Y');
        $ate = Carbon::createFromDate($this->Ate)->format('d/m/Y');

        $lancamentos = Lancamento::where(function ($query) use ($contaID) {
            return $query->where('ContaDebitoID', $contaID)->orWhere('ContaCreditoID', $contaID);
        })->whereBetween('DataContabilidade',[$de,$ate]);

        $this->Lancamentos = $lancamentos->orderBy('DataContabilidade')->get();
    }

    public function updateBloqueiodataanterior()
    {
        if (empty($this->data_bloqueio)) {
            $this->data_bloqueio = null;
        }
        $this->Conta->Bloqueiodataanterior = $this->data_bloqueio;
        $this->Conta->save();
    }

    public function updated()
    {
        $contaID = $this->selConta;
        if ($contaID) {
            $lancamentos = Lancamento::where(function ($query) use ($contaID) {
                return $query->where('ContaDebitoID', $contaID)->orWhere('ContaCreditoID', $contaID);
            });

            if ($this->De) {
                $de = Carbon::createFromFormat('Y-m-d', $this->De)->format('d/m/Y');
                $lancamentos->where('DataContabilidade', '>=', $de);
                cache(['Extrato_De'=>$this->De]);
            }
            if ($this->Ate) {
                $ate = Carbon::createFromFormat('Y-m-d', $this->Ate)->format('d/m/Y');
                $lancamentos->where('DataContabilidade', '<=', $ate);
                cache(['Extrato_Ate'=>$this->Ate]);
            }
            if ($this->Descricao) {
                $lancamentos->where('Descricao', 'like', "%$this->Descricao%");
            }
            if ($this->Conferido != '') {
                $lancamentos->where('conferido', $this->Conferido);
            }
            if ($this->Notificacao != '') {
                $lancamentos->where('notificacao', $this->Notificacao);
            }
            $this->Lancamentos = $lancamentos->orderBy('DataContabilidade')->get();
        }else {
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
    public function alterarDataVencidoRapido($lancamento_id,$acao)
    {
        $lancamento = Lancamento::find($lancamento_id);
        $hoje = Carbon::now();
        if ($acao == 'ontem') {
            $lancamento->DataContabilidade = $hoje->subDay()->format('d/m/Y');
        }elseif ($acao == 'hoje') {
            $lancamento->DataContabilidade = $hoje->format('d/m/Y');
        }elseif ($acao == 'amanha') {
            $lancamento->DataContabilidade = $hoje->addDay()->format('d/m/Y');
        }else{
            $this->addError('alteraDataVencimenotRapido','Nenhuma ação selecionada');
        }
        $lancamento->save();
        $this->updated();
    }

    public function incluirExclusao($lancamento_id)
    {

        if (in_array($lancamento_id, $this->listaExclusao)) {
            // Remove o ID do lançamento se ele já estiver na lista
            $this->listaExclusao = array_diff($this->listaExclusao, [$lancamento_id]);
        } else {
            // Adiciona o ID do lançamento à lista se ele ainda não estiver presente
            $this->listaExclusao[] = $lancamento_id;
        }
        $this->emit('$refresh');

        // $this->dispatchBrowserEvent('update-button-delete', ['lancamento_id' => $lancamento_id,'array' => $this->listaExclusao]);
    }
    public function checkExclusao($lancamento_id)
    {
        return in_array($lancamento_id, $this->listaExclusao);
    }
    public function processarExclussao()
    {
        dd('Falta Criar Regra');
    }
    public function editarLancamento()
    {
        dd('Falta Criar Regra');
    }

    public function render()
    {
        $contaID = $this->Conta->ID;
        $saldoAnterior = Lancamento::where(function ($query) use ($contaID) {
            return $query->where('ContaDebitoID', $contaID)->orWhere('ContaCreditoID', $contaID);
        })->where('DataContabilidade','<',$this->De)->sum('valor');

        $empresas = Empresa::whereHas('EmpresaUsuario', function ($query) {
            return $query->where('UsuarioID', Auth::user()->id);
        })
            ->orderBy('Descricao')
            ->pluck('Descricao', 'ID');
        $contas = Conta::where('EmpresaID',$this->selEmpresa)->where('Grau',5)->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')->pluck('PlanoContas.Descricao','Contas.ID');
        return view('livewire.conta.extrato', compact('empresas','contas','saldoAnterior'));
    }
}
