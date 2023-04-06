<?php

namespace App\Http\Livewire\Sicredi;

use App\Helpers\SicredApiHelper;
use App\Models\Atletas\CobrancaSicredi;
use App\Models\ContaCobranca;
use App\Models\LogConsultaSicred;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ListarLiquidacao extends Component
{
    public $consulta;
    public $consultaDia;
    public $consultaDiaDisplay;
    public $contaCobranca;
    public $msgSalvarRecebimentos;
    public $cache;
    
    public function updated()
    {
        $contaCobranca = ContaCobranca::find($this->contaCobranca);
        $consultaDia = Carbon::createFromFormat('Y-m-d', $this->consultaDia);
        $this->consultaDiaDisplay = $consultaDia->format('d/m/Y');
        $this->consultaDia = $consultaDia->format('Y-m-d');
        $cache = Cache::get('carteira_id_'.$contaCobranca->id.'_'.$this->consultaDiaDisplay);
        if ($cache) {
            $this->consulta = $cache;
            $this->cache = true;
        }else {
            $this->cache = false;
            $this->consulta = SicredApiHelper::boletoLiquidadoDia($contaCobranca->conta, $contaCobranca->agencia, $contaCobranca->posto, $contaCobranca->token_conta, $contaCobranca->devSicredi->SICREDI_CLIENT_ID, $contaCobranca->devSicredi->SICREDI_CLIENT_SECRET, $contaCobranca->devSicredi->SICREDI_TOKEN, $consultaDia->format('d/m/Y'));
            if ($this->consulta['status']) {
                Cache::put('carteira_id_'.$contaCobranca->id.'_'.$this->consultaDiaDisplay,$this->consulta,20000);
            }
        }
        $this->msgSalvarRecebimentos = '';
    }

    public function mount()
    {
        $contaCobranca = ContaCobranca::first();
        $this->contaCobranca = $contaCobranca->id;

        $now = Carbon::now()->subDay(1);
        $this->consultaDiaDisplay = $now->format('d/m/Y');
        $this->consultaDia = $now->format('Y-m-d');
        $cache = Cache::get('carteira_id_'.$contaCobranca->id.'_'.$this->consultaDiaDisplay);
        if ($cache) {
            $this->consulta = $cache;
            $this->cache = true;
        } else {
            $this->cache = false;
            $consulta = SicredApiHelper::boletoLiquidadoDia($contaCobranca->conta, $contaCobranca->agencia, $contaCobranca->posto, $contaCobranca->token_conta, $contaCobranca->devSicredi->SICREDI_CLIENT_ID, $contaCobranca->devSicredi->SICREDI_CLIENT_SECRET, $contaCobranca->devSicredi->SICREDI_TOKEN, $now->format('d/m/Y'));
            if ($consulta['status']) {
                $cache = Cache::put('carteira_id_'.$contaCobranca->id.'_'.$this->consultaDiaDisplay,$consulta,20000);
            }
            $this->consulta = $consulta;
        }
    }

    public function limparCache($key)
    {
        Cache::pull($key);
        $this->cache = false;
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
                        'DataLiquidacao' => explode(' ', $item['dataPagamento'])[0],
                        'SituacaoTitulo' => 'LIQUIDADO',
                        'Motivo' => date('d/m/Y H:i:s'),
                        'Associado' => $conta->associadobeneficiario,
                        'Conta' => $conta->conta,
                        'Beneficiario' => $conta->associadobeneficiario,
                        'Cobrando' => '',
                        'CobrandoEm' => '',
                        'PrevisaoPgto' => '',
                        'MovimentoPorUser' => '',
                        'MovimentoEm' => '',
                        'Atualizado' => date('d/m/Y H:i:s'),
                        'QuitadoIXC' => '',
                        'status_internet' => '',
                        'BaixarBanco' => '0',
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
