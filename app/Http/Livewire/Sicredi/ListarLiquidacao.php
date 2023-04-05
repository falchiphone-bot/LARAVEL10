<?php

namespace App\Http\Livewire\Sicredi;

use App\Helpers\SicredApiHelper;
use App\Models\Atletas\CobrancaSicredi;
use App\Models\ContaCobranca;
use Carbon\Carbon;
use Livewire\Component;

class ListarLiquidacao extends Component
{
    public $consulta;
    public $consultaDia;
    public $consultaDiaDisplay;
    public $contaCobranca;
    public $msgSalvarRecebimentos;

    public function updated()
    {
        $contaCobranca = ContaCobranca::find($this->contaCobranca);
        $consulta = Carbon::createFromFormat('Y-m-d', $this->consultaDia);
        $this->consultaDiaDisplay = $consulta->format('d/m/Y');
        $this->consultaDia = $consulta->format('Y-m-d');
        $this->consulta = SicredApiHelper::boletoLiquidadoDia($contaCobranca->conta, $contaCobranca->agencia, $contaCobranca->posto, $contaCobranca->token_conta, $contaCobranca->devSicredi->SICREDI_CLIENT_ID, $contaCobranca->devSicredi->SICREDI_CLIENT_SECRET, $contaCobranca->devSicredi->SICREDI_TOKEN, $consulta->format('d/m/Y'));
        $this->msgSalvarRecebimentos = '';
    }

    public function mount()
    {
        $contaCobranca = ContaCobranca::first();
        $this->contaCobranca = $contaCobranca->id;

        $now = Carbon::now()->subDay(1);
        $this->consultaDiaDisplay = $now->format('d/m/Y');
        $this->consultaDia = $now->format('Y-m-d');
        $this->consulta = SicredApiHelper::boletoLiquidadoDia($contaCobranca->conta, $contaCobranca->agencia, $contaCobranca->posto, $contaCobranca->token_conta, $contaCobranca->devSicredi->SICREDI_CLIENT_ID, $contaCobranca->devSicredi->SICREDI_CLIENT_SECRET, $contaCobranca->devSicredi->SICREDI_TOKEN, $now->format('d/m/Y'));
    }

    public function salvarRecebimentos()
    {
        $conta = ContaCobranca::find($this->contaCobranca);
        $nossonumero = 0;
        if (count($this->consulta['dados']['items']) == 0) {
            $this->msgSalvarRecebimentos = 'Lista vazia';
        } else {
            foreach ($this->consulta['dados']['items'] as $item) {
                $verificar = CobrancaSicredi::orderBy('DataLiquidacao', 'DESC')
                    ->where('NossoNumero', $item['nossoNumero'])
                    ->first();
                if ($verificar) {
                    $nossonumero++;
                } else {
                    $cs = CobrancaSicredi::create([
                        'NossoNumero' => $item['nossoNumero'],
                        'Carteira' => 'SIMPLES',
                        'NumeroDocumento' => $item['seuNumero'],
                        'Pagador' => '',
                        'DataEmissao' => '',
                        'DataVencimento' => '',
                        'Valor' => $item['valor'],
                        'Liquidacao' => $item['valorLiquidado'],
                        'DataLiquidacao' => $item['dataPagamento'],
                        'SituacaoTitulo' => 'LIQUIDADO',
                        'Motivo' => date('d/m/Y H:i:s'),
                        'Associado' => $conta->associadobeneficiario,
                        'Conta' => $conta->conta,
                        'Beneficiario' => $conta->associadobeneficiario,
                        'Cobrando' => null,
                        'CobrandoEm' => null,
                        'PrevisaoPgto' => null,
                        'MovimentoPorUser' => null,
                        'MovimentoEm' => null,
                        'Atualizado' => date('d/m/Y H:i:s'),
                        'QuitadoIXC' => null,
                        'status_internet' => null,
                        'BaixarBanco' => 0,
                    ]);
                }
            }
            $this->msgSalvarRecebimentos = 'Econtrado ' . $nossonumero . ' Nosso Numero ja cadastrado e não cadastardo no banco de dados';
        }
    }

    public function render()
    {
        $contasCobrancas = ContaCobranca::pluck('associadobeneficiario', 'id');
        return view('livewire.sicredi.listar-liquidacao', compact('contasCobrancas'));
    }
}
