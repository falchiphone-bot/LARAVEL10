<?php

namespace App\Http\Livewire\Lancamento;

use App\Models\Conta;
use App\Models\ContasPagar;
use App\Models\Empresa;
use App\Models\Historicos;
use App\Models\Lancamento;
use App\Models\LancamentoComentario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditarLancamento extends Component
{
    public $contas;
    public $empresa_id;

    public Lancamento $lancamento;
    public Empresa $empresa;
    public $empresas;
    public $historicos;
    public $comentarios;
    public $comentario;

    public $currentTab;

    protected $listeners = [
        'alterarIdLancamento',
        'salvarLancamento',
        'selectHistorico',
        'changeContaDebitoID',
        'changeContaCreditoID',
        'changeEmpresaID'
    ];

    public function alterarIdLancamento($lancamento_id,$empresa_id = null)
    {
        $this->mount($lancamento_id,$empresa_id);
        if ($empresa_id) {
            $this->empresa = Empresa::find($empresa_id);
            $this->lancamento->EmpresaID = $empresa_id;
        }
    }

    protected function prepareForValidation($attributes)
    {
        $attributes['lancamento']->Valor = str_replace(',', '.', str_replace('.', '', $attributes['lancamento']->Valor));
        $attributes['lancamento']->ValorQuantidadeDolar = str_replace(',', '.', str_replace('.', '', $attributes['lancamento']->ValorQuantidadeDolar));
        return $attributes;
    }
    protected $rules = [
        'lancamento.Valor' => 'required|decimal:2|gt:0',
        'lancamento.ValorQuantidadeDolar' => 'decimal:2|gt:0',
        'lancamento.EmpresaID' => 'required|integer',
        'lancamento.ContaCreditoID' => 'required|integer',
        'lancamento.ContaDebitoID' => 'required|integer',
        'lancamento.DataContabilidade' => 'required|date',
        'lancamento.HistoricoID' => ['required_without:lancamento.Descricao'],
        'lancamento.Descricao' => 'required_without:lancamento.HistoricoID',
    ];

    public function changeContaDebitoID($value)
    {
        $this->lancamento->ContaDebitoID = $value;
    }

    public function changeContaCreditoID($value)
    {
        $this->lancamento->ContaCreditoID = $value;
    }

    public function changeEmpresaID($value)
    {
        $this->lancamento->EmpresaID = $value;
        $this->empresa = Empresa::find($value);
        $this->atualizarContasHistoricos($value);
    }

    public function salvarLancamento($novo = null)
    {


        if ($this->lancamento->ContaDebitoID === $this->lancamento->ContaCreditoID) {
            $this->resetErrorBag();
            $this->resetValidation();
            $this->addError('ContaDebitoID', 'Conta Débito e Conta Crédito não podem ser iguais. Feche esta guia e abra novamente.');
            return;
        }



        // $this->lancamento->ValorQuantidadeDolar =  '0,10';
        $Dolar = $this->lancamento->ValorQuantidadeDolar;

        if($Dolar == 0 || $Dolar == null || $Dolar == "" || $Dolar == '0,00')
        {
            // $this->lancamento->ValorQuantidadeDolar =  '0,01';
            // dd($this->lancamento->ValorQuantidadeDolar);
        }
        else
        if($Dolar == 01)
        {
			//   $this->lancamento->ValorQuantidadeDolar = '0,01';            // dd(94,$this->lancamento->ValorQuantidadeDolar);
        }


        if ($novo) {
        // if($this->lancamento->ValorQuantidadeDolar == null || $this->lancamento->ValorQuantidadeDolar =="" || $this->lancamento->ValorQuantidadeDolar == '0,00'){
            // $this->lancamento->ValorQuantidadeDolar =  '0.01';
// DD( $this->lancamento->ValorQuantidadeDolar);
        // }
        }

        $this->validate();

        // if($this->lancamento->ValorQuantidadeDolar == '0.01'){
        //     $this->lancamento->ValorQuantidadeDolar =  'null';
        // }
        if($Dolar == 01)
        {
            $this->lancamento->ValorQuantidadeDolar =  '';
            // dd(113,$this->lancamento->ValorQuantidadeDolar);
        }


        if ($novo) {
            $novoLancamento = $this->lancamento->replicate();
            if (!$this->temBloqueio()) {
                $novoLancamento->DataContabilidade = $this->lancamento->DataContabilidade->format('d/m/Y');
                $novoLancamento->ValorQuantidadeDolar = null;
                $novoLancamento->save();
                session()->flash('message', 'Lançamento Criado.');
            }
        } elseif (!$this->temBloqueio($this->lancamento->ID, $this->lancamento->DataContabilidade)) {
            $this->lancamento['DataContabilidade'] = $this->lancamento->DataContabilidade->format('d-m-Y');
            $this->lancamento['EmpresaID'] = $this->lancamento['EmpresaID'] ?? session('conta.extrato.empresa.id');
            $this->lancamento['Usuarios_id'] = $this->lancamento['Usuarios_id'] ?? Auth::user()->id;


            // $this->lancamento->Valor = number_format($this->lancamento->Valor, 2, ',', '.');

            $valor = $this->lancamento->Valor;
            $valor = str_replace(['.', ','], ['', '.'], $valor);
            $valor = (float) $valor / 100;

            $valor = number_format($valor, 2, ',', '.');

            $this->lancamento->Valor = $this->lancamento->Valor;
            // $this->lancamento->ValorQuantidadeDolar = number_format($this->lancamento->ValorQuantidadeDolar, 2, ',', '.');




            if($this->lancamento->ValorQuantidadeDolar == null || $this->lancamento->ValorQuantidadeDolar == "" || $this->lancamento->ValorQuantidadeDolar == 0 || $Dolar == '0,01'){
                //  dd($this->lancamento->ValorQuantidadeDolar);
                $this->lancamento->ValorQuantidadeDolar =  '0.00';
                // dd($this->lancamento->ValorQuantidadeDolar);
            }


            if ($this->lancamento->save()) {
                session()->flash('message', 'Lançamento atualizado.');
                // $this->lancamento['DataContabilidade'] = $this->lancamento->DataContabilidade->format('Y-m-d');
            } else {
                $this->addError('save', 'Erro ao atualizar lançamento');
            }

            $contasPagar = ContasPagar::where('LancamentoID', $this->lancamento->ID)->first();

            if ($contasPagar) {
                $contasPagar->Valor = $this->lancamento->Valor;
                $contasPagar->EmpresaID = $this->lancamento->EmpresaID;
                $contasPagar->DataDocumento = $this->lancamento->DataContabilidade;

                $contasPagar->ContaFornecedorID = $this->lancamento->ContaDebitoID;
                $contasPagar->ContaPagamentoID = $this->lancamento->ContaCreditoID;
                // $contasPagar->Descricao = $this->lancamento->Descricao;
                $contasPagar->save();

                // dd($contasPagar, $this->lancamento);
            }
        }

    }

    public function acao($value)
    {
        $this->metodo = $value;
    }

    public function salvarComentario()
    {
        if ($this->comentario) {
            LancamentoComentario::create([
                'LancamentoID' => $this->lancamento->ID,
                'Descricao' => $this->comentario,
                'UsuarioID' => Auth::user()->id,
                'Created' => date('d/m/Y H:i:s'),
                'Visualizado' => 0,
            ]);
            $this->comentario = null;
            $this->comentarios = LancamentoComentario::where('LancamentoID', $this->lancamento->ID)->get();
            session()->flash('message', 'Comentário adicionado.');
        } else {
            $this->addError('save', 'Preecha comentário para salvar!');
        }
    }

    public function temBloqueio($lancamento_id = null)
    {
        if ($lancamento_id) {
            $lancamento = Lancamento::find($lancamento_id);
            $dataLancamento = Carbon::createFromDate($lancamento->DataContabilidade);

            $data_conta_debito = $lancamento->ContaDebito->Bloqueiodataanterior;
            if ($data_conta_debito) {
                if ($data_conta_debito->greaterThanOrEqualTo($this->lancamento->DataContabilidade) || $this->lancamento->DataContabilidade->lessThan($data_conta_debito)) {
                    $this->addError('data_bloqueio', 'Bloqueio de Data na Conta Debito');
                    return true;
                }
            }
            // dd($this->lancamento->DataContabilidade->lessThan($data_conta_debito));

            $data_empresa = Empresa::find($lancamento->EmpresaID)->Bloqueiodataanterior;
            if ($data_empresa) {
                if ($data_empresa->greaterThanOrEqualTo($dataLancamento) || $this->lancamento->DataContabilidade->lessThan($data_empresa)) {
                    $this->addError('data_bloqueio', 'Bloqueio de Data na Empresa');
                    return true;
                }
            }

            if ($lancamento->ContaCredito->Bloqueiodataanterior) {
                if ($lancamento->ContaCredito->Bloqueiodataanterior->greaterThanOrEqualTo($dataLancamento) || $this->lancamento->DataContabilidade->lessThan($data_empresa)) {
                    $this->addError('data_bloqueio', 'Bloqueio de Data na Conta Credito');
                    return true;
                }
            }

            return false;
        } elseif ($this->lancamento->DataContabilidade) {
            $data_empresa = $data_empresa = Empresa::find(session('conta.extrato.empresa.id'))->Bloqueiodataanterior;
            if ($data_empresa) {
                if ($data_empresa->greaterThanOrEqualTo($this->lancamento->DataContabilidade)) {
                    $this->addError('data_bloqueio', 'Bloqueio de Data na Empresa');
                    return true;
                }
            }
            if ($this->lancamento->ContaDebito->Bloqueiodataanterior ?? null) {
                if ($this->lancamento->ContaDebito->Bloqueiodataanterior->greaterThanOrEqualTo($this->lancamento->DataContabilidade)) {
                    $this->addError('data_bloqueio', 'Bloqueio de Data na Conta Debito');
                    return true;
                }
            }
            if ($this->lancamento->ContaCredito->Bloqueiodataanterior ?? null) {
                if ($this->lancamento->ContaCredito->Bloqueiodataanterior->greaterThanOrEqualTo($this->lancamento->DataContabilidade)) {
                    $this->addError('data_bloqueio', 'Bloqueio de Data na Conta Credito');
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    public function sessionTab($tab)
    {
        $this->currentTab = $tab;
    }

    public function selectHistorico($value)
    {
        $this->lancamento->HistoricoID = $value;
        $historico = Historicos::find($this->lancamento->HistoricoID);
        if ($historico) {
            $this->lancamento->ContaDebitoID = $historico->ContaDebitoID;
            $this->lancamento->ContaCreditoID = $historico->ContaCreditoID;
        }
        // $this->emitTo('conta.extrato','select2');
    }

    public function hydrated()
    {
        $this->resetErrorBag();
    }
    public function dehydrate()
    {
        if (!strpos($this->lancamento['Valor'], ",")) {
            $this->lancamento['Valor'] = number_format($this->lancamento->Valor,2,',','.');
        }
    }

    public function mount($lancamento_id = null)
    {

        $this->resetErrorBag();
        $this->resetValidation();

        $this->emitTo('lancamento.troca-empresa', 'setLancamentoID', $lancamento_id);
        $this->currentTab = 'lancamento';

        if ($lancamento_id != 'novo') {
            $this->lancamento = Lancamento::find($lancamento_id);
            $this->lancamento->Valor = number_format($this->lancamento->Valor, 2, ',', '.');
            $this->comentarios = LancamentoComentario::where('LancamentoID', $lancamento_id)->get();
        } else {
            $this->lancamento = new Lancamento();
        }

        $this->atualizarContasHistoricos($this->lancamento->EmpresaID);

        $this->emitTo('extrato', 'select2', ['target' => 'modal']);
    }

    public function atualizarContasHistoricos($empresa_id)
    {
        $this->contas = Conta::where('EmpresaID', $empresa_id ?? session('conta.extrato.empresa.id'))
            ->where('Grau', 5)
            ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', 'Planocontas_id')
            ->orderBy('PlanoContas.Descricao')
            ->pluck('PlanoContas.Descricao', 'Contas.ID');
        $this->historicos = Historicos::where('EmpresaID', $empresa_id ?? session('conta.extrato.empresa.id'))
            ->orderBy('Descricao', 'asc')
            ->get(['Descricao', 'ID']);
    }

    public function render()
    {
        return view('livewire.lancamento.editar-lancamento');
    }
}
