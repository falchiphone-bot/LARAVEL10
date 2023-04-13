<?php

namespace App\Http\Livewire\Sicredi;

use App\Helpers\SicredApiHelper;
use App\Models\Atletas\CobrancaSicredi;
use App\Models\ContaCobranca;
use App\Models\Historicos;
use App\Models\Lancamento;
use App\Models\LogConsultaSicred;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use DateTime;
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

    public function updated()
    {
        $this->contaCobranca = ContaCobranca::find($this->contaCobrancaID);
        $contaCobranca = $this->contaCobranca;
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
        $this->contaCobranca = $contaCobranca;
        $this->contaCobrancaID = $contaCobranca->id;

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

    public function criarLancamento($valorLiquido)
    {

       ////////// DEVE SER VERIFICADO SE AS CONTAS DEBITO E CONTAS CREDITO TANTO DE LIQUIDACAO COMO TARIFAS ESTAO BLOQUEADAS OU AINDA SE A EMPRESA ESTÁ BLOQUEADA PARA LANCAMENTOS NAS DATAS AQUI
       //// PRECISA CORRIGIR O FORMATO DA DATA PARA SALVAR POIS O  protected $casts = [ 'DataContabilidade' => 'date','Created' => 'date', NA TABELA LANCAMENTOS É O QUE CAUSA OS PROBLEMAS..
       //// VAMOS ANALISAR.... AFETOU OUTRAS SITUAÇÕES
    ];

        $contaCobranca = $this->contaCobranca;
        if (isset($contaCobranca->d_cobranca) && isset($contaCobranca->d_tarifa)) {
            $dataContabilidade = Carbon::createFromFormat('Y-m-d',$this->consultaDia);
            $dataContabilidade = $dataContabilidade->addDay($contaCobranca->d_cobranca);

            $lancamentoCobranca = Lancamento::whereDate('DataContabilidade',$dataContabilidade->format('Y-m-d'))
            ->where('EmpresaID',$contaCobranca->EmpresaID)
            ->where('HistoricoID',$contaCobranca->Credito_Cobranca)->first('ID');



            if ($lancamentoCobranca) {
                $this->addError('lancamentoCobranca', 'Liquidação de cobrança já lançado no dia <strong>'.$dataContabilidade->format('d/m/Y').'</strong>.');
            }else {
                // dd($contaCobranca->Credito_Cobranca,$contaCobranca->Tarifa_Cobranca);
                $historico = Historicos::find($contaCobranca->Credito_Cobranca);



                $lc = Lancamento::create([
                    'Valor' => $valorLiquido,
                    'EmpresaID' => $contaCobranca->EmpresaID,
                    'ContaDebitoID' => $historico->ContaDebitoID,
                    'ContaCreditoID' => $historico->ContaCreditoID,
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' => date('d/m/Y H:i:s'),
                    'Created' => date('d/m/Y H:i:s'),
                    'HistoricoID' => $historico->ID,

                ]);

            }

            $lancamentoTarifa = Lancamento::whereDate('DataContabilidade',$this->consultaDia)
            ->where('HistoricoID',$contaCobranca->Tarifa_Cobranca)
            ->where('EmpresaID',$contaCobranca->EmpresaID)
            ->first();
            if ($lancamentoTarifa) {
                $this->addError('taxaCobranca', "Taxa de cobrança já lançado no dia <strong>$this->consultaDiaDisplay</strong>.");
            }else {
                $historicoTarifa = Historicos::find($contaCobranca->Tarifa_Cobranca);

                Lancamento::create([
                    'Valor' => count($this->consulta['dados']['items']),
                    'EmpresaID' => $contaCobranca->EmpresaID,
                    'ContaDebitoID' => $historicoTarifa->ContaDebitoID,
                    'ContaCreditoID' => $historicoTarifa->ContaCreditoID,
                    'Usuarios_id' => auth()->user()->id,
                    'DataContabilidade' => $this->consultaDiaDisplay,
                    'Created' => date('d/m/Y H:i:s'),
                    'HistoricoID' => $historicoTarifa->ID,
                ]);
                session()->flash('message', 'Lançamentos criado.');
            }
        }else {
            $this->addError('diasD', 'Conta de Cobrança sem dias D especificado.');
        }
    }

    public function salvarRecebimentos()
    {
        $conta = $this->contaCobranca;
        $nossonumeroCadastrado = 0;
        if (count($this->consulta['dados']['items']) == 0) {
            $this->addError('lista', 'Lista vazia.');
        } else {
            foreach ($this->consulta['dados']['items'] as $item) {
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
            if ($nossonumeroCadastrado > 0) {
                $this->addError('salvarRecebimentos', $nossonumeroCadastrado.' registros já cadastrados.');
            }
            session()->flash('message', 'Rotina executada com sucesso.');
        }
    }

    public function render()
    {
        $contasCobrancas = ContaCobranca::pluck('associadobeneficiario', 'id');
        return view('livewire.sicredi.listar-liquidacao', compact('contasCobrancas'));
    }
}
