<?php

namespace App\Http\Livewire\Sicredi;

use App\Helpers\SicredApiHelper;
use App\Models\Atletas\CobrancaSicredi;
use App\Models\ContaCobranca;
use App\Models\Feriado;
use App\Models\Historicos;
use App\Models\Lancamento;
use App\Models\LogConsultaSicred;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use DateTime;
use Google\Service\PolyService\Format;
use Illuminate\Support\Facades\Date;

class ListarLiquidacao extends Component
{
    public $consulta;
    public $consultaDia;
    public $consultaDiaDisplay;
    public $contaCobranca;
    public $contaCobrancaID;

    public $msgSalvarRecebimentos;
    public $cache;

    public function buscar()
    {
        $this->contaCobranca = ContaCobranca::find($this->contaCobrancaID);
        $contaCobranca = $this->contaCobranca;
        $consultaDia = Carbon::createFromFormat('Y-m-d', $this->consultaDia);

        $this->consultaDiaDisplay = $consultaDia->format('d/m/Y');
        $this->consultaDia = $consultaDia->format('Y-m-d');

        $this->consulta = SicredApiHelper::boletoLiquidadoDia($contaCobranca, $consultaDia->format('d/m/Y'));

        $this->msgSalvarRecebimentos = '';


    }

    public function mount()
    {
        // $contaCobranca = ContaCobranca::first();
        $contaCobranca = ContaCobranca::where('conta','11382')->first();
        // dd($contaCobranca);
        $this->contaCobranca = $contaCobranca;
        $this->contaCobrancaID = $contaCobranca->id;

        $now = Carbon::now()->subDay(1);
        $this->consultaDiaDisplay = $now->format('d/m/Y');
        $this->consultaDia = $now->format('Y-m-d');

        $consulta = SicredApiHelper::boletoLiquidadoDia($contaCobranca, $now->format('d/m/Y'));

                $this->consulta = $consulta;

        // dd($this->consulta, $this->consultaDia, $this->consultaDiaDisplay, $contaCobranca);

    }

    public function limparCache($key)
    {
        Cache::pull($key);
        $this->cache = false;
    }

    public function criarLancamento($valorLiquido)
    {
        $contaCobranca = $this->contaCobranca;


        if (isset($contaCobranca->d_cobranca) && isset($contaCobranca->d_tarifa)) {
            $dataTarifa = Carbon::createFromFormat('Y-m-d', $this->consultaDia);
            $dataLiquidacao = Carbon::createFromFormat('Y-m-d', $this->consultaDia)->addDay($contaCobranca->d_cobranca);

            if ($dataLiquidacao->weekDay() == 6) {
                $dataLiquidacao->addDay(2);
            } elseif ($dataLiquidacao->weekday() == 7) {
                $dataLiquidacao->addDay(1);
            }


            $feriado = Feriado::where('data', $dataLiquidacao->format('Y-m-d'))->first();
            while ($feriado ) {
                $dataLiquidacao->addDay();
                $feriado = Feriado::where('data', $dataLiquidacao->format('Y-m-d'))->first();
            }

            $lancamentoCobranca = Lancamento::whereDate('DataContabilidade', $dataLiquidacao->format('Y-m-d'))
                ->where('EmpresaID', $contaCobranca->EmpresaID)
                ->where('HistoricoID', $contaCobranca->Credito_Cobranca)
                ->first('ID');

            if ($lancamentoCobranca) {
                $this->addError('lancamentoCobranca', 'Liquidação de cobrança já lançado no dia <strong>' . $dataLiquidacao->format('d/m/Y') . '</strong>.');
            } else {
                // dd($contaCobranca->Credito_Cobranca,$contaCobranca->Tarifa_Cobranca);
                $historico = Historicos::find($contaCobranca->Credito_Cobranca);

                $lc = Lancamento::create([
                    'Valor' => $valorLiquido,
                    'EmpresaID' => $contaCobranca->EmpresaID,
                    'ContaDebitoID' => $historico->ContaDebitoID,
                    'ContaCreditoID' => $historico->ContaCreditoID,
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' => $dataLiquidacao->format('d/m/Y'),
                    'HistoricoID' => $historico->ID,
                ]);
            }

            $lancamentoTarifa = Lancamento::whereDate('DataContabilidade', $this->consultaDia)
                ->where('HistoricoID', $contaCobranca->Tarifa_Cobranca)
                ->where('EmpresaID', $contaCobranca->EmpresaID)
                ->First();
            if ($lancamentoTarifa) {
                $this->addError('taxaCobranca', "Taxa de cobrança já lançado no dia <strong>$this->consultaDiaDisplay</strong>.");
            } else {
                $historicoTarifa = Historicos::find($contaCobranca->Tarifa_Cobranca);

                Lancamento::create([
                    'Valor' => count($this->consulta['dados']),
                    'EmpresaID' => $contaCobranca->EmpresaID,
                    'ContaDebitoID' => $historicoTarifa->ContaDebitoID,
                    'ContaCreditoID' => $historicoTarifa->ContaCreditoID,
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' =>  $dataTarifa->format('d/m/Y'),
                    'HistoricoID' => $historicoTarifa->ID,
                ]);
                session()->flash('message', 'Lançamentos criado.');
            }
        } else {
            $this->addError('diasD', 'Conta de Cobrança sem dias D especificado.');
        }
    }

    public function salvarRecebimentos()
    {
        $conta = $this->contaCobranca;
        $nossonumeroCadastrado = 0;

        if (count($this->consulta['dados']) == 0) {
            $this->addError('lista', 'Lista vazia.');
        } else {
            foreach ($this->consulta['dados'] as $item) {
                $verificar = CobrancaSicredi::orderBy('DataLiquidacao', 'DESC')
                    ->where('NossoNumero', $item['nossoNumero'])
                    ->first();
                if ($verificar) {
                    $nossonumeroCadastrado++;
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
                        'DataLiquidacao' => Carbon::createFromDate(explode(' ', $item['dataPagamento'])[0])->format('d/m/Y'),
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
            if ($nossonumeroCadastrado > 0) {
                $this->addError('salvarRecebimentos', $nossonumeroCadastrado . ' registros já cadastrados.');
            }
            session()->flash('message', 'Rotina executada com sucesso.');
        }
    }

    public function render()
    {

        $contasCobrancas = ContaCobranca::pluck('associadobeneficiario', 'id');

// dd($contasCobrancas);
        return view('livewire.sicredi.listar-liquidacao', compact('contasCobrancas'));
    }
}
