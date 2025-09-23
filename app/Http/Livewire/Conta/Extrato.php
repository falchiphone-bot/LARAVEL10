<?php

namespace App\Http\Livewire\Conta;

use App\Exports\LancamentoExport;
use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\MoedasValores;
use App\Models\SolicitacaoExclusao;
use Carbon\Carbon;
use Livewire\Component;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;

class Extrato extends Component
{
    protected $listeners = [
        'selectedSelEmpresaItem' => 'selectedSelEmpresaItem',
        'selectedSelContaItem' => 'selectedSelContaItem',
    // Permite disparar busca via Livewire.emit('search') do JS
    'search' => 'search',
    ];
    // Propriedades do componente (restauradas conforme uso no código)
    public $selEmpresa;
    public $selConta;
    public $Empresa;
    public $Conta;
    public $Lancamentos;
    public $De;
    public $Ate;
    public $Descricao;
    public $DescricaoApartirDe;
    public $Conferido;
    public $SaidasGeral;
    public $EntradasGeral;
    public $Notificacao;
    public $Investimentos;
    public $Transferencias;
    public $SemDefinir;
    public $data_bloqueio_conta;
    public $data_bloqueio_empresa;
    public $listaExclusao = [];
    public $listaSoma = [];
    public $exibicao_pesquisa = '';
    public $editar_lancamento = null;

    public function selectedSelEmpresaItem($item)
    {
        if ($item) {
            $this->selEmpresa = $item;
            session(['conta.extrato.empresa.id' => $this->selEmpresa]);
            $this->Empresa = Empresa::find($item);
            $this->data_bloqueio_empresa = $this->Empresa->Bloqueiodataanterior?->format('Y-m-d');
            // Se o modal estiver aberto, atualizar a empresa também no componente filho
            if ($this->editar_lancamento) {
                try {
                    $this->emitTo('lancamento.editar-lancamento', 'changeEmpresaID', (int) $this->selEmpresa);
                } catch (\Throwable $e) { /* noop */ }
            }
            // Limpa seleção de conta e sessão vinculada para evitar busca com conta antiga
            $this->selConta = null;
            $this->Conta = null;
            $this->data_bloqueio_conta = null;
            session()->forget('extrato_ContaID');
            $this->Lancamentos = null;
            // Limpeza visual extra: fechar modais e limpar erros
            $this->dispatchBrowserEvent('fechar-modal');
            $this->dispatchBrowserEvent('desabilitar-selConta');
            $this->dispatchBrowserEvent('limpar-selConta');
            $this->resetErrorBag();
            $this->resetValidation();
            // Atualiza visualização executando uma busca (sem conta, zera resultado)
            $this->search();
        } else {
            $this->selEmpresa = null;
            session(['conta.extrato.empresa.id' => $this->selEmpresa]);
            // Limpa seleção de conta e sessão vinculada
            $this->selConta = null;
            $this->Conta = null;
            $this->data_bloqueio_conta = null;
            session()->forget('extrato_ContaID');
            // Limpeza visual extra e atualização
            $this->dispatchBrowserEvent('fechar-modal');
            $this->dispatchBrowserEvent('desabilitar-selConta');
            $this->dispatchBrowserEvent('limpar-selConta');
            $this->resetErrorBag();
            $this->resetValidation();
            $this->search();
        }
        $this->updated();
    }
    public function selectedSelContaItem($item)
    {
        if ($item) {
            $this->selConta = $item;
            session(['extrato_ContaID' => $item]);
            $this->Conta = Conta::find($item);
            $this->data_bloqueio_conta = $this->Conta->Bloqueiodataanterior?->format('Y-m-d');
            // $this->emit('search');
            // Executa busca automaticamente ao selecionar a conta
            $this->search();
        } else {
            $this->selConta = null;
        }
        $this->updated();
    }
    public function hydrate()
    {
        // $this->emit('select2');
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Hook para chamadas internas e para evitar erro em $this->updated()
    public function updated($name = null, $value = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Garante datas padrão e grava em sessão
    protected function ensureDatesDefault(): void
    {
        $today = date('Y-m-d');
        if (empty($this->De)) {
            $this->De = $today;
            session(['Extrato_De' => $this->De]);
        }
        if (empty($this->Ate)) {
            $this->Ate = $today;
            session(['Extrato_Ate' => $this->Ate]);
        }
    }

    // Persistir datas quando o usuário alterar nos inputs
    public function updatedDe($value): void
    {
        if (empty($value)) {
            $this->De = date('Y-m-d');
        }
        session(['Extrato_De' => $this->De]);
    // Atualiza automaticamente a visualização ao alterar a data inicial
    $this->search();
    }

    public function updatedAte($value): void
    {
        if (empty($value)) {
            $this->Ate = date('Y-m-d');
        }
        session(['Extrato_Ate' => $this->Ate]);
    // Atualiza automaticamente a visualização ao alterar a data final
    $this->search();
    }

    // Atualiza lista ao mudar filtro de conferência
    public function updatedConferido($value): void
    {
        $this->search();
    }

    // Atualiza lista ao mudar filtro de notificação
    public function updatedNotificacao($value): void
    {
        $this->search();
    }

    // Atualiza lista ao mudar filtro de descrição
    public function updatedDescricao($value): void
    {
        $this->search();
    }

    // Atualiza lista ao mudar filtro de data a partir de
    public function updatedDescricaoApartirDe($value): void
    {
        $this->search();
    }

    public function mount($contaID)
    {
        // Restaurar filtros da sessão
        $this->De = session('Extrato_De');
        $this->Ate = session('Extrato_Ate');
        $this->ensureDatesDefault();

        // Restaurar empresa/conta da sessão se existirem; caso contrário usar $contaID inicial
        $sessConta = session('extrato_ContaID');
        $sessEmpresa = session('conta.extrato.empresa.id');

        if ($sessConta) {
            $this->Conta = Conta::find($sessConta);
            $this->selConta = $this->Conta?->ID;
        } else {
            // fallback ao parâmetro
            session(['extrato_ContaID' => $contaID]);
            $this->Conta = Conta::find($contaID);
            $this->selConta = $this->Conta?->ID;
        }

        if ($sessEmpresa) {
            $this->selEmpresa = $sessEmpresa;
        } else {
            $this->selEmpresa = $this->Conta?->EmpresaID;
            session(['conta.extrato.empresa.id' => $this->selEmpresa]);
        }

        $this->Empresa = $this->selEmpresa ? Empresa::find($this->selEmpresa) : null;
        $this->data_bloqueio_conta = $this->Conta->Bloqueiodataanterior?->format('Y-m-d');
        $this->data_bloqueio_empresa = $this->Empresa?->Bloqueiodataanterior?->format('Y-m-d');


    $de = Carbon::parse($this->De)->startOfDay();
    $ate = Carbon::parse($this->Ate)->endOfDay();

        $lancamentos = Lancamento::query()
            ->daConta($contaID)
            ->naoExcluido()
            ->periodo($de, $ate);

    $this->Lancamentos = $lancamentos->orderByData()->get();


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
                $this->addError('data_bloqueio', 'Não é possivel alterar esse lançamento para essa data, pois há um bloqueio de data na Conta - ID: ' . $lancamento_id);
                return true;
            }
        }
        $data_empresa = $this->Empresa->Bloqueiodataanterior;
        if ($data_empresa) {
            if (Carbon::createFromDate($data_empresa)->greaterThanOrEqualTo($dataLancamento)) {
                $this->addError('data_bloqueio', 'Não é possivel alterar esse lançamento para essa data, pois há um bloqueio de data na Empresa - ID: ' . $lancamento_id);
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
                $this->addError('data_bloqueio', 'Não é possivel alterar esse lançamento para essa data, pois há um bloqueio de data na Conta Partida: ' . $descricao);
                return true;
            }
        }
        return false;
    }

    public function search()
    {
    // Garante datas padrão e sessão
    $this->ensureDatesDefault();
        // Garante que a conta selecionada esteja na sessão
        if (!empty($this->selConta)) {
            session(['extrato_ContaID' => $this->selConta]);
        }
        $contaID = session('extrato_ContaID') ?? $this->selConta;

        // dd($contaID);
        if ($contaID) {
            $lancamentos = Lancamento::where(function ($query) use ($contaID) {
                return $query->where('Lancamentos.ContaDebitoID', $contaID)->orWhere('Lancamentos.ContaCreditoID', $contaID);
            });

            if ($this->De) {
                $start = $this->Descricao && $this->DescricaoApartirDe
                    ? Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->startOfDay()
                    : Carbon::createFromFormat('Y-m-d', $this->De)->startOfDay();
                $lancamentos->where('DataContabilidade', '>=', $start);
                session(['Extrato_De' => $this->De]);
            }
            if ($this->Ate) {
                $end = Carbon::createFromFormat('Y-m-d', $this->Ate)->endOfDay();
                $lancamentos->where('DataContabilidade', '<=', $end);
                session(['Extrato_Ate' => $this->Ate]);
            }

            if ($this->Descricao) {
                $lancamentos->where(function ($q) {
                    $q->where('Lancamentos.Descricao', 'like', "%$this->Descricao%")->orWhere('Historicos.Descricao', 'like', "%$this->Descricao%");
                });
            }
            // Filtro Conferido robusto para string/boolean/numérico
            if ($this->Conferido !== '' && $this->Conferido !== null) {
                $conf = $this->Conferido;
                // Não conferido: null ou 0
                if ($conf === 'false' || $conf === false || $conf === 0 || $conf === '0') {
                    $lancamentos->where(function ($q) {
                        return $q->whereNull('Conferido')->orWhere('Conferido', 0);
                    });
                }
                // Conferido: 1
                elseif ($conf === 'true' || $conf === true || $conf === 1 || $conf === '1') {
                    $lancamentos->where('Conferido', 1);
                }
                // Outros valores especiais são tratados mais abaixo (Saidas/Entradas Geral)
                else {
                    $lancamentos->where('Conferido', $conf);
                }
            }
            // if ($this->SaidasGeral != '') {
            //     if ($this->SaidasGeral == 'false') {
            //         $lancamentos->where(function ($q) {
            //             return $q->whereNull('SaidasGeral')->orWhere('SaidasGeral', 0);
            //         });
            //     } else {
            //         $lancamentos->where('SaidasGeral', $this->SaidasGeral);
            //     }
            // }

            // if ($this->EntradasGeral != '') {
            //     if ($this->EntradasGeral == 'false') {
            //         $lancamentos->where(function ($q) {
            //             return $q->whereNull('EntradasGeral')->orWhere('EntradasGeral', 0);
            //         });
            //     } else {
            //         $lancamentos->where('EntradasGeral', $this->EntradasGeral);
            //     }
            // }

            // Filtro de notificação: aplica apenas quando for 0 ou 1
            if (in_array((string) $this->Notificacao, ['0', '1'], true)) {
                $lancamentos->where('notificacao', $this->Notificacao);
            }

            if ($this->Conferido === 'SaidasGeral' || $this->Conferido === 'EntradasGeral') {
                $this->addError('conferido', 'Seleção inválida para o filtro Conferido.');
                return;
            }

            // $this->Lancamentos = $lancamentos
            //     ->orderBy('DataContabilidade')
            //     ->whereDoesntHave('SolicitacaoExclusao')
            //     ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
            //     ->get(['Lancamentos.EmpresaID','Lancamentos.ID', 'Lancamentos.Valor', 'Lancamentos.ValorQuantidadeDolar','DataContabilidade', 'Lancamentos.ContaCreditoID',
            //      'Lancamentos.ContaDebitoID', 'Lancamentos.Descricao', 'Historicos.Descricao as HistoricoDescricao',
            //      'Conferido', 'SaidasGeral', 'EntradasGeral', 'Investimentos', 'Transferencias', 'SemDefinir']);

            $this->Lancamentos = $lancamentos
            ->whereDoesntHave('SolicitacaoExclusao')
            ->leftJoin('Contabilidade.Empresas as Emp', 'Emp.ID', '=', 'Lancamentos.EmpresaID')
            ->leftJoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
            ->orderBy('DataContabilidade')
            ->get([
                'Lancamentos.EmpresaID',
                'Emp.ClassificaCaixaGeral',
                'Emp.AtualizarPoupancaAvenue',
                'Lancamentos.ID',
                'Lancamentos.Valor',
                'Lancamentos.ValorQuantidadeDolar',
                'DataContabilidade',
                'Lancamentos.ContaCreditoID',
                'Lancamentos.ContaDebitoID',
                'Lancamentos.Descricao',
                'Historicos.Descricao as HistoricoDescricao',
                'Conferido',
                'SaidasGeral',
                'EntradasGeral',
                'Investimentos',
                'Transferencias',
                'SemDefinir',
            ]);


        } else {
            $this->Lancamentos = null;
        }

        // dd($this->Lancamentos);

    }


    public function searchSaidasGeral()
    {
    $this->ensureDatesDefault();
        if ($this->Conferido !== 'SaidasGeral') {
            $this->addError('conferido', 'Seleção inválida para Saídas Gerais.');
            return;
        }

        $start = null; $end = null;
        if ($this->De) {
            $start = $this->Descricao && $this->DescricaoApartirDe
                ? Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->startOfDay()
                : Carbon::createFromFormat('Y-m-d', $this->De)->startOfDay();
            session(['Extrato_De' => $this->De]);
        }
        if ($this->Ate) {
            $end = Carbon::createFromFormat('Y-m-d', $this->Ate)->endOfDay();
            session(['Extrato_Ate' => $this->Ate]);
        }

        $lancamentos = Lancamento::query()
            ->leftJoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
            ->saidasGeral()
            ->periodo($start, $end)
            ->conferido($this->Conferido)
            ->naoExcluido()
            ->orderByData();

        $this->Lancamentos = $lancamentos->get([
            'Lancamentos.ID',
            'Lancamentos.Valor',
            'DataContabilidade',
            'Lancamentos.ContaCreditoID',
            'Lancamentos.Descricao',
            'Historicos.Descricao as HistoricoDescricao',
            'Conferido',
            'SaidasGeral'
        ]);
    }

    public function searchSaidasGeralExcel()
    {
    $this->ensureDatesDefault();
        if ($this->Conferido !== 'SaidasGeral') {
            $this->addError('conferido', 'Seleção inválida para exportação de Saídas Gerais.');
            return;
        }

        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $SaidasGeral = 1;

        $lancamentos = Lancamento::where(function ($query) use ($SaidasGeral) {
            return $query->where('Lancamentos.SaidasGeral', 1);
        });

        $start = null; $end = null;
        if ($this->De) {
            $start = $this->Descricao && $this->DescricaoApartirDe
                ? Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->startOfDay()
                : Carbon::createFromFormat('Y-m-d', $this->De)->startOfDay();
            $lancamentos->where('DataContabilidade', '>=', $start);
            session(['Extrato_De' => $this->De]);
        }
        if ($this->Ate) {
            $end = Carbon::createFromFormat('Y-m-d', $this->Ate)->endOfDay();
            $lancamentos->where('DataContabilidade', '<=', $end);
            session(['Extrato_Ate' => $this->Ate]);
        }

        if ($this->Conferido != '') {
            if ($this->Conferido == 'false') {
                $lancamentos->where(function ($q) {
                    return $q->whereNull('Conferido')->orWhere('Conferido', 0);
                });
            }
            if ($this->Conferido == 'SaidasGeral') {
                $lancamentos->where(function ($q) {
                    return $q->whereNull('SaidasGeral')->orWhere('SaidasGeral', 1);
                });
            } else {
                $lancamentos->where('Conferido', $this->Conferido);
            }
        }

        if ($this->Conferido == 'SaidasGeral') {
            $this->Lancamentos = $lancamentos
                ->orderBy('DataContabilidade')
                ->whereDoesntHave('SolicitacaoExclusao')
                ->with(['ContaDebito.PlanoConta', 'ContaCredito.PlanoConta', 'Historico'])
                ->get();
        }

        //  DD($this->Lancamentos);

        $SaidasGeral = 0;

        $ExportarLinha = [];
        $ExportarUnir = [];

        foreach ($this->Lancamentos as $item) {
            $exportarItem = [
                'DataContabilidade' => $item->DataContabilidade->format('d/m/Y'),
                'ContaDebitoID' => $item->ContaDebito->PlanoConta->Descricao ?? null,
                'ContaCreditoID' => $item->ContaCredito->PlanoConta->Descricao ?? null,
                'Valor' => $item->Valor,
                'Historico' => $item->Historico->Descricao ?? null,
                'Descricao' => $item->Descricao,
            ];

            $ExportarLinha[] = $exportarItem;
        }

    $exportarUnir = collect($ExportarLinha);

    $deDisplay = $start ? $start->format('d/m/Y 00:00:00') : Carbon::now()->startOfDay()->format('d/m/Y 00:00:00');
    $ateDisplay = $end ? $end->format('d/m/Y 23:59:59') : Carbon::now()->endOfDay()->format('d/m/Y 23:59:59');

    $Arquivo = 'Pagamentos por PEDRO ROBERTO FALCHI E SANDRA ELISA MAGOSSI FALCHI' . '-' . str_replace('/', '', $deDisplay) . '-a-' . str_replace('/', '', $ateDisplay) . '.xlsx';

        return Excel::download(new LancamentoExport($exportarUnir), "$Arquivo");
    }

    public function searchSaidasGeralSoma()
    {
    $this->ensureDatesDefault();
        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $SaidasGeral = 1;
        $EntraddasGeral = 1;
        $totalsomadoSAIDAS = 0;
        $totalsomadoEntradas = 0;

        $lancamentosSaida = Lancamento::where('Lancamentos.SaidasGeral', 1);

        $lancamentosEntrada = Lancamento::where('Lancamentos.EntradasGeral', 1);

        // $this->De = session('Extrato_De') ?? date('Y-m-d');
        // $this->Ate = session('Extrato_Ate') ?? date('Y-m-d');
        session(['Extrato_De' => $this->De]);
        session(['Extrato_Ate' => $this->Ate]);
        if ($this->De) {
            $start = $this->Descricao && $this->DescricaoApartirDe
                ? Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->startOfDay()
                : Carbon::createFromFormat('Y-m-d', $this->De)->startOfDay();
            $lancamentosSaida->where('DataContabilidade', '>=', $start);
            $lancamentosEntrada->where('DataContabilidade', '>=', $start);
        }
        if ($this->Ate) {
            $end = Carbon::createFromFormat('Y-m-d', $this->Ate)->endOfDay();
            $lancamentosSaida->where('DataContabilidade', '<=', $end);
            $lancamentosEntrada->where('DataContabilidade', '<=', $end);
        }

        $totalsomadoSAIDAS = $lancamentosSaida->sum('Valor');

        $totalsomadoEntradas = $lancamentosEntrada->sum('Valor');

    $msg = 'TOTAL ENTRADAS: ' . number_format($totalsomadoEntradas, 2, ',', '.') . ' | TOTAL SAÍDAS: ' . number_format($totalsomadoSAIDAS, 2, ',', '.') . ' | RESULTADO: ' . number_format(($totalsomadoEntradas - $totalsomadoSAIDAS), 2, ',', '.');
    session()->flash('info', $msg);
    return;

        $SaidasGeral = 0;
    }

    public function searchEntradasGeral()
    {
    $this->ensureDatesDefault();
        if ($this->Conferido !== 'EntradasGeral') {
            $this->addError('conferido', 'Seleção inválida para Entradas Gerais.');
            return;
        }

        $start = null; $end = null;
        if ($this->De) {
            $start = $this->Descricao && $this->DescricaoApartirDe
                ? Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->startOfDay()
                : Carbon::createFromFormat('Y-m-d', $this->De)->startOfDay();
            session(['Extrato_De' => $this->De]);
        }
        if ($this->Ate) {
            $end = Carbon::createFromFormat('Y-m-d', $this->Ate)->endOfDay();
            session(['Extrato_Ate' => $this->Ate]);
        }

        $lancamentos = Lancamento::query()
            ->leftJoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
            ->entradasGeral()
            ->periodo($start, $end)
            ->conferido($this->Conferido)
            ->naoExcluido()
            ->orderByData();

        $this->Lancamentos = $lancamentos->get([
            'Lancamentos.ID',
            'Lancamentos.Valor',
            'DataContabilidade',
            'Lancamentos.ContaCreditoID',
            'Lancamentos.Descricao',
            'Historicos.Descricao as HistoricoDescricao',
            'Conferido',
            'EntradasGeral',
        ]);
    }


    public function searchSemDefinicao()
    {

        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $SemDefinir = 1;

        $lancamentos = Lancamento::where(function ($query) use ($SemDefinir) {
            return $query->where('Lancamentos.SemDefinir', 1);
        });

        if ($this->De) {
            if ($this->Descricao && $this->DescricaoApartirDe) {
                $de = Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->format('d/m/Y 00:00:00');
            } else {
                $de = Carbon::createFromFormat('Y-m-d', $this->De)->format('d/m/Y 00:00:00');
            }
            $lancamentos->where('DataContabilidade', '>=', $de);
            session(['Extrato_De' => $this->De]);
        }
        if ($this->Ate) {
            $ate = Carbon::createFromFormat('Y-m-d', $this->Ate)->format('d/m/Y 23:59:59');
            $lancamentos->where('DataContabilidade', '<=', $ate);
            session(['Extrato_Ate' => $this->Ate]);
        }

        if ($this->Conferido != '') {
            if ($this->Conferido == 'false') {
                $lancamentos->where(function ($q) {
                    return $q->whereNull('Conferido')->orWhere('Conferido', 0);
                });
            }
            if ($this->Conferido == 'SemDefinir') {
                $lancamentos->where(function ($q) {
                    return $q->whereNull('SemDefinir')->orWhere('SemDefinir', 1);
                });
            } else {
                $lancamentos->where('Conferido', $this->Conferido);
            }
        }
        if ($this->Conferido == 'SemDefinir') {
            $this->Lancamentos = $lancamentos
                ->orderBy('DataContabilidade')
                ->whereDoesntHave('SolicitacaoExclusao')
                ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
                ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'DataContabilidade', 'Lancamentos.ContaCreditoID', 'Lancamentos.Descricao',
                 'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral', 'EntradasGeral', 'SemDefinir']);
        }

        $SemDefinir = 0;
    }

    public function searchTransferencias()
    {

        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $Transferencias = 1;

        $lancamentos = Lancamento::where(function ($query) use ($Transferencias) {
            return $query->where('Lancamentos.Transferencias', 1);
        });

        if ($this->De) {
            if ($this->Descricao && $this->DescricaoApartirDe) {
                $de = Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->format('d/m/Y 00:00:00');
            } else {
                $de = Carbon::createFromFormat('Y-m-d', $this->De)->format('d/m/Y 00:00:00');
            }
            $lancamentos->where('DataContabilidade', '>=', $de);
            session(['Extrato_De' => $this->De]);
        }
        if ($this->Ate) {
            $ate = Carbon::createFromFormat('Y-m-d', $this->Ate)->format('d/m/Y 23:59:59');
            $lancamentos->where('DataContabilidade', '<=', $ate);
            session(['Extrato_Ate' => $this->Ate]);
        }

        if ($this->Conferido != '') {
            if ($this->Conferido == 'false') {
                $lancamentos->where(function ($q) {
                    return $q->whereNull('Conferido')->orWhere('Conferido', 0);
                });
            }
            if ($this->Conferido == 'Transferencias') {
                $lancamentos->where(function ($q) {
                    return $q->whereNull('SemDefinir')->orWhere('Transferencias', 1);
                });
            } else {
                $lancamentos->where('Conferido', $this->Conferido);
            }
        }
        if ($this->Conferido == 'Transferencias') {
            $this->Lancamentos = $lancamentos
                ->orderBy('DataContabilidade')
                ->whereDoesntHave('SolicitacaoExclusao')
                ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
                ->get(['Lancamentos.EmpresaID','Lancamentos.ID', 'Lancamentos.Valor', 'DataContabilidade', 'Lancamentos.ContaCreditoID', 'Lancamentos.Descricao',
                 'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral', 'EntradasGeral', 'Transferencias']);
        }

        $Transferencias = 0;
    }



    public function searchEntradasGeralExcel()
    {
        if ($this->Conferido !== 'EntradasGeral') {
            $this->addError('conferido', 'Seleção inválida para exportação de Entradas Gerais.');
            return;
        }

        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $EntradasGeral = 1;

        $lancamentos = Lancamento::where(function ($query) use ($EntradasGeral) {
            return $query->where('Lancamentos.EntradasGeral', 1);
        });

        $start = null; $end = null;
        if ($this->De) {
            $start = $this->Descricao && $this->DescricaoApartirDe
                ? Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->startOfDay()
                : Carbon::createFromFormat('Y-m-d', $this->De)->startOfDay();
            $lancamentos->where('DataContabilidade', '>=', $start);
            session(['Extrato_De' => $this->De]);
        }
        if ($this->Ate) {
            $end = Carbon::createFromFormat('Y-m-d', $this->Ate)->endOfDay();
            $lancamentos->where('DataContabilidade', '<=', $end);
            session(['Extrato_Ate' => $this->Ate]);
        }

        if ($this->Conferido != '') {
            if ($this->Conferido == 'false') {
                $lancamentos->where(function ($q) {
                    return $q->whereNull('Conferido')->orWhere('Conferido', 0);
                });
            }
            if ($this->Conferido == 'EntradasGeral') {
                $lancamentos->where(function ($q) {
                    return $q->whereNull('EntradasGeral')->orWhere('EntradasGeral', 1);
                });
            } else {
                $lancamentos->where('Conferido', $this->Conferido);
            }
        }
        if ($this->Conferido == 'EntradasGeral') {
            $this->Lancamentos = $lancamentos
                ->orderBy('DataContabilidade')
                ->whereDoesntHave('SolicitacaoExclusao')
                ->with(['ContaDebito.PlanoConta', 'ContaCredito.PlanoConta', 'Historico'])
                ->get();
        }

        $EntradasGeral = 0;

        $ExportarLinha = [];
        $ExportarUnir = [];

        foreach ($this->Lancamentos as $item) {
            $exportarItem = [
                'DataContabilidade' => $item->DataContabilidade->format('d/m/Y'),
                'ContaDebitoID' => $item->ContaDebito->PlanoConta->Descricao ?? null,
                'ContaCreditoID' => $item->ContaCredito->PlanoConta->Descricao ?? null,
                'Valor' => $item->Valor,
                'Historico' => $item->Historico->Descricao ?? null,
                'Descricao' => $item->Descricao,
            ];

            $ExportarLinha[] = $exportarItem;
        }

    $exportarUnir = collect($ExportarLinha);

    $deDisplay = $start ? $start->format('d/m/Y 00:00:00') : Carbon::now()->startOfDay()->format('d/m/Y 00:00:00');
    $ateDisplay = $end ? $end->format('d/m/Y 23:59:59') : Carbon::now()->endOfDay()->format('d/m/Y 23:59:59');

    $Arquivo = 'Entradas para PEDRO ROBERTO FALCHI E SANDRA ELISA MAGOSSI FALCHI' . '-' . str_replace('/', '', $deDisplay) . '-a-' . str_replace('/', '', $ateDisplay) . '.xlsx';

        return Excel::download(new LancamentoExport($exportarUnir), "$Arquivo");
    }

    public function searchPDF()
    {
    $contaID = session('extrato_ContaID') ?? $this->selConta;
        if ($contaID) {
            $lancamentos = Lancamento::where(function ($query) use ($contaID) {
                return $query->where('Lancamentos.ContaDebitoID', $contaID)->orWhere('Lancamentos.ContaCreditoID', $contaID);
            });

            $start = null; $end = null;
            if ($this->De) {
                $start = $this->Descricao && $this->DescricaoApartirDe
                    ? Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->startOfDay()
                    : Carbon::createFromFormat('Y-m-d', $this->De)->startOfDay();
                $lancamentos->where('DataContabilidade', '>=', $start);
                session(['Extrato_De' => $this->De]);
            }
            if ($this->Ate) {
                $end = Carbon::createFromFormat('Y-m-d', $this->Ate)->endOfDay();
                $lancamentos->where('DataContabilidade', '<=', $end);
                session(['Extrato_Ate' => $this->Ate]);
            }

            if ($this->Descricao) {
                $lancamentos->where(function ($q) {
                    $q->where('Lancamentos.Descricao', 'like', "%$this->Descricao%")->orWhere('Historicos.Descricao', 'like', "%$this->Descricao%");
                });
            }
            // Filtro Conferido robusto para string/boolean/numérico
            if ($this->Conferido !== '' && $this->Conferido !== null) {
                $conf = $this->Conferido;
                if ($conf === 'false' || $conf === false || $conf === 0 || $conf === '0') {
                    $lancamentos->where(function ($q) {
                        return $q->whereNull('Conferido')->orWhere('Conferido', 0);
                    });
                } elseif ($conf === 'true' || $conf === true || $conf === 1 || $conf === '1') {
                    $lancamentos->where('Conferido', 1);
                } else {
                    $lancamentos->where('Conferido', $conf);
                }
            }

            if ($this->SaidasGeral != '') {
                if ($this->SaidasGeral == 'false') {
                    $lancamentos->where(function ($q) {
                        return $q->whereNull('SaidasGeral')->orWhere('SaidasGeral', 0);
                    });
                } else {
                    $lancamentos->where('SaidasGeral', $this->SaidasGeral);
                }
            }

            // Filtro de notificação: aplica apenas quando for 0 ou 1
            if (in_array((string) $this->Notificacao, ['0', '1'], true)) {
                $lancamentos->where('notificacao', $this->Notificacao);
            }
            $this->Lancamentos = $lancamentos
                ->orderBy('DataContabilidade')
                ->whereDoesntHave('SolicitacaoExclusao')
                ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
                ->get(['Lancamentos.EmpresaID','Lancamentos.ID', 'Lancamentos.Valor', 'DataContabilidade',
                'Lancamentos.ContaCreditoID', 'Lancamentos.ContaDebitoID', 'Lancamentos.Descricao',
                'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral', 'EntradasGeral']);

            return redirect()
                ->route('Extrato.gerarpdf')
                ->with('LancamentosPDF', [
                    'DadosExtrato' => $this->Lancamentos,
                    'de' => ($start ? $start->format('d/m/Y 00:00:00') : Carbon::now()->startOfDay()->format('d/m/Y 00:00:00')),
                    'ate' => ($end ? $end->format('d/m/Y 23:59:59') : Carbon::now()->endOfDay()->format('d/m/Y 23:59:59')),
                    'descricaoconta' => $this->Conta->Planoconta->Descricao,
                    'conta' => $this->Conta->ID,
                    'empresa' => $this->Conta->EmpresaID,
                ]);
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
        // $this->dispatchBrowserEvent('confirmarLancamento', ['lancamento_id' => $lancamento_id, 'status' => $lancamento->Conferido]);
        $this->search();
    }

    public function confirmarAtualizar($lancamento_id)
    {
        $this->search();
    }

    public function editarLancamento($lancamentoId, $empresaId = null)
    {
        // Define o registro a editar (ou 'novo') e abre o modal no front
        $this->editar_lancamento = $lancamentoId;
        if (!empty($empresaId)) {
            // Ajusta empresa selecionada quando informado (útil para novo)
            $this->selEmpresa = $empresaId;
        }
        // Garante que o componente filho (modal) atualize o lançamento antes de abrir
        try {
            $this->emitTo(
                'lancamento.editar-lancamento',
                'alterarIdLancamento',
                $lancamentoId,
                $empresaId ?? $this->selEmpresa
            );
        } catch (\Throwable $e) { /* noop */ }
        $this->dispatchBrowserEvent('abrir-modal');
    }

    public function fecharEdicao()
    {
        // Limpa seleção e solicita fechar o modal no front
        $this->editar_lancamento = null;
        $this->dispatchBrowserEvent('fechar-modal');
        $this->search();
    }

    public function confirmarAtualizarSaldoPoupanca($id, $saldo, $descricao, $data, $contaDebito, $contaCredito, $EmpresaID)
    {


    // dd($id, $saldo, $descricao, $data, $contaDebito, $contaCredito, $EmpresaID );
        $id = $id;
        $EmpresaID = $EmpresaID;
        $Saldo = $saldo;

        $Descricao = $descricao;

        $Debito = $contaDebito;
        $Credito =  $contaCredito;


        $Data = Carbon::parse($data);


        $ProximaData = $Data;

            // Adicionar 1 mês
        $ProximaData = $ProximaData->addMonth()->format('Y-m-d');


        $dataCalcular = MoedasValores::where('Data',$ProximaData)
        ->where('idmoeda', 3)
        ->first();


                if ($dataCalcular == null) {
                    $this->addError('poupanca', 'Não existe valor para a data informada: ' . $ProximaData);
                    return;
                }


        $NovaDescricao = $dataCalcular->valor . '% sobre saldo de '. $Saldo   . '. ';

        $juros = $Saldo * $dataCalcular->valor / 100;

        $jurosArredondado = round($juros, 2);

        // dd($Saldo, $dataCalcular, $Descricao,  $ProximaData,  $Debito, $Credito, $NovaDescricao, $jurosArredondado);
        // Mensagem de sucesso ou outra lógica
        session()->flash('success', 'Atualização realizada com sucesso!');




        return redirect()->route('lancamentos.atualizarpoupanca')
        ->with([
            'Saldo' => $Saldo,
            'EmpresaID' => $EmpresaID,
            'dataCalcular' => $dataCalcular,
            'Descricao' => $Descricao,
            'ProximaData' => $ProximaData,
            'Debito' => $Debito,
            'Credito' => $Credito,
            'NovaDescricao' => $NovaDescricao,
            'jurosArredondado' => $jurosArredondado,
        ]);


    }

    public function selecionarLancamento($contaID)
    {
        $lancamentos = Lancamento::where(function ($query) use ($contaID) {
            $query->where('Lancamentos.ContaDebitoID', $contaID)->orWhere('Lancamentos.ContaCreditoID', $contaID);
        });

        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);

        if ($this->De) {
            // Verifica a descrição e formata a data para o campo 'De'
            if ($this->Descricao && $this->DescricaoApartirDe) {
                $de = Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->format('Y-m-d 00:00:00');
            } else {
                $de = Carbon::createFromFormat('Y-m-d', $this->De)->format('Y-m-d 00:00:00');
            }

            $lancamentos->where('DataContabilidade', '>=', $de);
            cache(['Extrato_De' => $this->De]);
        }

        if ($this->Ate) {
            // Formata a data para o campo 'Até'
            $ate = Carbon::createFromFormat('Y-m-d', $this->Ate)->format('Y-m-d 23:59:59');
            $lancamentos->where('DataContabilidade', '<=', $ate);
            cache(['Extrato_Ate' => $this->Ate]);
        }

        // Executa a query e obtém os dados
        $lancamentos = $lancamentos
            ->orderBy('DataContabilidade')
            ->whereDoesntHave('SolicitacaoExclusao')
            ->leftJoin('Contabilidade.Historicos', 'Historicos.ID', '=', 'Lancamentos.HistoricoID')
            ->leftJoin('Contabilidade.Empresas', 'Empresas.ID', '=', 'Lancamentos.EmpresaID')
            ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'Lancamentos.ValorQuantidadeDolar', 'Empresas.Descricao as NomeEmpresa',
            'DataContabilidade', 'Lancamentos.ContaCreditoID',
             'Lancamentos.ContaDebitoID', 'Lancamentos.Descricao',
             'Historicos.Descricao as HistoricoDescricao', 'Conferido',
              'SaidasGeral', 'EntradasGeral']);

        return compact('lancamentos', 'de', 'ate');
    }

    public function contasGabrielMagossiFalchi()
    {
        $dados = [];
        $lancamentos = Lancamento::limit(0);
        // ================================================================================================================

        $contaID = 11146; // PRF

        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Gabriel Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];

        // ================================================================================================================
        $contaID = 19538; // STTARMAAKE

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Gabriel Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];

        // ================================================================================================================
        $contaID = 15394; //PEDRO

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Gabriel Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];
        //  ================================================================================================================
        $contaID = 19532; //NET RUBI SERVICOS

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Gabriel Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];
        //  ================================================================================================================
        $contaID = 11142; //INFRANET

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Gabriel Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];
        //  ================================================================================================================
        return redirect()->route('lancamentos.exibirDadosGabrielMagossiFalchi')->with('dados', $dados);
    }

    private function processarDados($contaID, $nomeBase)
    {
        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);

        $dadosLancamento = $this->selecionarLancamento($contaID);
        $lancamentos = $dadosLancamento['lancamentos'];
        $de = $dadosLancamento['de'] ?? 'Não informado';
        $ate = $dadosLancamento['ate'] ?? 'Não informado';

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $dolard = $lancamentos->where('ContaDebitoID', $contaID)->sum('ValorQuantidadeDolar');
        $dolarc = $lancamentos->where('ContaCreditoID', $contaID)->sum('ValorQuantidadeDolar');
        $saldo = $debito - $credito;
        $dolar = $dolard - $dolarc;

        // dd($lancamentos, $dolard, $dolarc, $dolar);

        $NomeEmpresa = $lancamentos->last()->NomeEmpresa ?? 'Nenhuma empresa encontrada';

        return [
            'Nome' => $nomeBase,
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'Dolar' => $dolar,
            'De' => $de,
            'Até' => $ate,
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa,
        ];
    }



    public function contasPoupancaAvenue()
    {

        $dados = [];
        $dados[] = $this->processarDados(19784, 'AVENUE/POUPANÇA- BASE 01');
        $dados[] = $this->processarDados(19782, 'AVENUE/POUPANÇA- BASE 03');
        $dados[] = $this->processarDados(19783, 'AVENUE/POUPANÇA- BASE 04');
        $dados[] = $this->processarDados(19778, 'AVENUE/POUPANÇA- BASE 05');
        $dados[] = $this->processarDados(19766, 'AVENUE/POUPANÇA- BASE 06');
        $dados[] = $this->processarDados(19787, 'AVENUE/POUPANÇA- BASE 07');
        $dados[] = $this->processarDados(19774, 'AVENUE/POUPANÇA- BASE 09');
        $dados[] = $this->processarDados(19770, 'AVENUE/POUPANÇA- BASE 10');
        $dados[] = $this->processarDados(19771, 'AVENUE/POUPANÇA- BASE 11');
        $dados[] = $this->processarDados(19780, 'AVENUE/POUPANÇA- BASE 12');
        $dados[] = $this->processarDados(19772, 'AVENUE/POUPANÇA- BASE 13');
        $dados[] = $this->processarDados(19762, 'AVENUE/POUPANÇA- BASE 14');
        $dados[] = $this->processarDados(19779, 'AVENUE/POUPANÇA- BASE 17');
        $dados[] = $this->processarDados(19781, 'AVENUE/POUPANÇA- BASE 18');
        $dados[] = $this->processarDados(19768, 'AVENUE/POUPANÇA- BASE 21');
        $dados[] = $this->processarDados(19774, 'AVENUE/POUPANÇA- BASE 24');
        $dados[] = $this->processarDados(19775, 'AVENUE/POUPANÇA- BASE 25');
        $dados[] = $this->processarDados(19776, 'AVENUE/POUPANÇA- BASE 26');
        $dados[] = $this->processarDados(19765, 'AVENUE/POUPANÇA - BASE 30');



        // return $dados;


        // dd($dados);
        //  ================================================================================================================
        return redirect()->route('lancamentos.avenuepoupanca')->with('dados', $dados);
    }


    public function contasCaioCesarMagossiFalchi()
    {
        $dados = [];
        $lancamentos = Lancamento::limit(0);
        // ================================================================================================================

        $contaID = 11141; // INFRANET

        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);
        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Caio Cesar Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];

        // ================================================================================================================
        $contaID = 19347; // NET RUBI SERVICOS

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Caio Cesar Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];

        // ================================================================================================================
        $contaID = 15393; //PEDRO

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Caio Cesar Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];
        //  ================================================================================================================
        $contaID = 11145; //PRF
        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Caio Cesar Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];
        //  ================================================================================================================

        $contaID = 19567; //STTARMAAKE
        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => 'Caio Cesar Magossi Falchi',
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];
        //  ================================================================================================================

        return redirect()->route('lancamentos.exibirDadosGabrielMagossiFalchi')->with('dados', $dados);
    }

    public function confirmarLancamentoEntradasGeral($lancamento_id)
    {
        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);

        $lancamento = Lancamento::find($lancamento_id);

        $funcao = __FUNCTION__;
        include __DIR__ . '/ConfereFuncao.php';

        if ($lancamento->EntradasGeral) {
            $lancamento->EntradasGeral = 0;
        } else {
            $lancamento->EntradasGeral = 1;
        }

        $lancamento->save();

        // $this->dispatchBrowserEvent('confirmarLancamentoEntradasGeral', ['lancamento_id' => $lancamento_id, 'statusEntradasGeral' => $lancamento->EntradasGeral]);
        // dd( $lancamento);
        $this->search();
    }

    public function confirmarInvestimentos($lancamento_id)
    {
        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);

        $lancamento = Lancamento::find($lancamento_id);

        $funcao = __FUNCTION__;
        include __DIR__ . '/ConfereFuncao.php';

        if ($lancamento->Investimentos) {
            $lancamento->Investimentos = 0;
        } else {
            $lancamento->Investimentos = 1;
        }

        $lancamento->save();

        // $this->dispatchBrowserEvent('confirmarLancamentoEntradasGeral', ['lancamento_id' => $lancamento_id, 'statusEntradasGeral' => $lancamento->EntradasGeral]);
        // dd( $lancamento);
        $this->search();
    }

    public function confirmarTransferencias($lancamento_id)
    {
        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);

        $lancamento = Lancamento::find($lancamento_id);

        $funcao = __FUNCTION__;
        include __DIR__ . '/ConfereFuncao.php';

        if ($lancamento->Transferencias) {
            $lancamento->Transferencias = 0;
        } else {
            $lancamento->Transferencias = 1;
        }

        $lancamento->save();

        // $this->dispatchBrowserEvent('confirmarLancamentoEntradasGeral', ['lancamento_id' => $lancamento_id, 'statusEntradasGeral' => $lancamento->EntradasGeral]);
        // dd( $lancamento);
        $this->search();
    }

    public function confirmarSemDefinir($lancamento_id)
    {
        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);

        $lancamento = Lancamento::find($lancamento_id);

        $funcao = __FUNCTION__;
        include __DIR__ . '/ConfereFuncao.php';

        if ($lancamento->SemDefinir) {
            $lancamento->SemDefinir = 0;
        } else {
            $lancamento->SemDefinir = 1;
        }

        $lancamento->save();

        // $this->dispatchBrowserEvent('confirmarLancamentoEntradasGeral', ['lancamento_id' => $lancamento_id, 'statusEntradasGeral' => $lancamento->EntradasGeral]);
        // dd( $lancamento);
        $this->search();
    }

    public function confirmarLancamentoSaidasGeral($lancamento_id)
    {
        $lancamento = Lancamento::find($lancamento_id);
        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);

        // $this->dispatchBrowserEvent('alert', ['message' => 'REGISTRO JÁ MARCADO COMO ENTRADA GERAL']);
        // return;

        if ($lancamento->EntradasGeral) {
            $this->addError('conflito', 'Registro já marcado como Entrada Geral.');
            return;
        }

        $funcao = __FUNCTION__;
        include __DIR__ . '/ConfereFuncao.php';

        if ($lancamento->SaidasGeral) {
            $lancamento->SaidasGeral = 0;
        } else {
            $lancamento->SaidasGeral = 1;
        }

        $lancamento->save();

        $this->dispatchBrowserEvent('confirmarLancamentoSaidasGeral', ['lancamento_id' => $lancamento_id, 'statusSaidasGeral' => $lancamento->SaidasGeral]);
        $this->search();
    }

    public function alterarDataVencidoRapido($lancamento_id, $acao)
    {
        $lancamento = Lancamento::find($lancamento_id);

        if ($this->temBloqueio($lancamento_id, $lancamento->DataContabilidade)) {
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

    $lancamento->DataContabilidade = $novaData;

        $lancamento->save();
        $this->search();
    }

    public function incluirExclusao($lancamento_id, $data)
    {
        if ($this->temBloqueio($lancamento_id, $data)) {
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
        // Normaliza data e ids antes de consultas
        $this->ensureDatesDefault();
        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $empresaID = $this->selEmpresa;
        $deDT = Carbon::parse($this->De)->startOfDay();
        $deSQL = $deDT->format('Y-m-d H:i:s');

        // Se faltar empresa ou conta, evita consultas inválidas
        if (empty($contaID) || empty($empresaID)) {
            $saldoAnterior = 0;
            $saldoAnteriorDolar = 0;
            $empresas = Empresa::whereHas('EmpresaUsuario', function ($query) {
                return $query->where('UsuarioID', Auth::user()->id);
            })
                ->select(DB::raw("CONCAT(Descricao,' - ',Cnpj) as Descricao"), 'ID')
                ->orderBy('Descricao')
                ->pluck('Descricao', 'ID');

            $contas = Conta::where('EmpresaID', $empresaID)
                ->where('Grau', 5)
                ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', 'Planocontas_id',)
                ->orderBy('PlanoContas.Descricao')
                ->get(['PlanoContas.Descricao', 'Contas.ID','PlanoContas.UsarDolar']);

            $periodo = '';
            if (!empty($this->De) && !empty($this->Ate)) {
                try {
                    $periodo = Carbon::parse($this->De)->format('d/m/Y') . ' a ' . Carbon::parse($this->Ate)->format('d/m/Y');
                } catch (\Throwable $e) {
                    $periodo = '';
                }
            }
            $filtro = '';
            if (!empty($this->Conferido)) {
                $filtro = ' | Filtro: ' . (string) $this->Conferido;
            }
            $this->exibicao_pesquisa = trim('Extrato ' . ($periodo ? "($periodo)" : '') . $filtro);

            return view('livewire.conta.extrato', compact('empresas', 'contas', 'saldoAnterior', 'saldoAnteriorDolar'))
                ->with('exibicao_pesquisa', $this->exibicao_pesquisa);
        }

        $totalCredito = Lancamento::where(function ($q) use ($deSQL, $contaID, $empresaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $empresaID)
                ->where('DataContabilidade', '<', $deSQL);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($deSQL, $contaID, $empresaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $empresaID)
                ->where('DataContabilidade', '<', $deSQL);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoAnterior = $totalDebito - $totalCredito;


        $totalCreditoDolar = Lancamento::where(function ($q) use ($deSQL, $contaID, $empresaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $empresaID)
                ->where('DataContabilidade', '<', $deSQL);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.ValorQuantidadeDolar');

        $totalDebitoDolar = Lancamento::where(function ($q) use ($deSQL, $contaID, $empresaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $empresaID)
                ->where('DataContabilidade', '<', $deSQL);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.ValorQuantidadeDolar');

        $saldoAnteriorDolar = $totalDebitoDolar - $totalCreditoDolar;



        $empresas = Empresa::whereHas('EmpresaUsuario', function ($query) {
            return $query->where('UsuarioID', Auth::user()->id);
        })
            ->select(DB::raw("CONCAT(Descricao,' - ',Cnpj) as Descricao"), 'ID')
            ->orderBy('Descricao')
            ->pluck('Descricao', 'ID');

        $contas = Conta::where('EmpresaID', $this->selEmpresa)
            ->where('Grau', 5)
            ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', 'Planocontas_id',)
            ->orderBy('PlanoContas.Descricao')
            ->get(['PlanoContas.Descricao', 'Contas.ID','PlanoContas.UsarDolar']);



        //     $contaDolar = Conta::where('EmpresaID', $this->selEmpresa)
        //     ->where('Grau', 5)
        //     ->where('PlanoContas.UsarDolar', 1)
        //     ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', 'Planocontas_id',)
        //     ->orderBy('PlanoContas.Descricao')
        //     ->get(['PlanoContas.Descricao', 'Contas.ID', 'PlanoContas.UsarDolar']);

        //    dd($contaDolar);


        // Cabeçalho de exibição (título) seguro caso filtros não estejam definidos
        $periodo = '';
        if (!empty($this->De) && !empty($this->Ate)) {
            try {
                $periodo = Carbon::parse($this->De)->format('d/m/Y') . ' a ' . Carbon::parse($this->Ate)->format('d/m/Y');
            } catch (\Throwable $e) {
                $periodo = '';
            }
        }
        $filtro = '';
        if (!empty($this->Conferido)) {
            $filtro = ' | Filtro: ' . (string) $this->Conferido;
        }
        $this->exibicao_pesquisa = trim('Extrato ' . ($periodo ? "($periodo)" : '') . $filtro);

        return view('livewire.conta.extrato', compact('empresas', 'contas', 'saldoAnterior', 'saldoAnteriorDolar'))
            ->with('exibicao_pesquisa', $this->exibicao_pesquisa);
    }

    public function gerarExtratoPdf_sempaginacao()
    {
        if (session('LancamentosPDF') == null) {
            return Redirect::back();
        }

        $lancamentosPDF = session('LancamentosPDF');

        // 1
        $lancamentos = $lancamentosPDF['DadosExtrato'];

        $de = $lancamentosPDF['de'];
        $dataDivididade = explode(' ', $de);
        $deformatada = $dataDivididade[0];
        $descricaoconta = $lancamentosPDF['descricaoconta'];
        $conta = $lancamentosPDF['conta'];

        $ate = $lancamentosPDF['ate'];
        $dataDivididaate = explode(' ', $ate);
        $ateformatada = $dataDivididaate[0];

        $desa = $de;
        $contaID = $conta;
        $this->selEmpresa = $lancamentosPDF['empresa'];

        $totalCredito = Lancamento::where(function ($q) use ($desa, $contaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $desa);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($desa, $contaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $desa);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoAnterior = $totalDebito - $totalCredito;

        // Construir a tabela HTML
        $htmlTable = '<h1><center><font color="black"><b>RELATÓRIO DE LANÇAMENTOS ' . '</b></font></center></h1>';
        $htmlTable .= '<h5><center><font color="blue"><b>Conta: ' . $descricaoconta . '</b></font></center></h5>';
        $htmlTable .= '<h1><center><font color="red"><b>Período de: ' . $deformatada . ' à ' . $ateformatada . '</b></font></center></h1>';
        $htmlTable .=
            '


            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Débito</th>
                        <th>Crédito</th>
                        </tr>

                        <tr>
                            <td colspan="4"><hr></td>
                        </tr>;
                        <tr>
                            <td colspan="3">SALDO ANTERIOR </td>
                            <td style="text-align: right;">' .
                    number_format($saldoAnterior, 2, ',', '.') .
                    '</td>
                        </tr>;


                </thead>
                <tbody>
        ';
                $debitoTotal = 0;
                $creditoTotal = 0;

                foreach ($lancamentosPDF['DadosExtrato'] as $lancamento) {
                    $id = $lancamento->ID;
                    $valor = number_format($lancamento->Valor, 2, ',', '.');
                    $data = $lancamento->DataContabilidade->format('d/m/Y');
                    $descricao = $lancamento['HistoricoDescricao'] . ' ' . $lancamento->Descricao;
                    $descricaoQuebrada = wordwrap($descricao, 50, '<br>', true);

                    if (strlen($descricao) < 50) {
                        $descricaoPreenchida = str_pad($descricao, 50, ' ');
                        $descricaocompleta = $descricaoPreenchida;
                    } else {
                        $descricaocompleta = $descricaoQuebrada;
                    }

                    if ($conta == $lancamento->ContaDebitoID) {
                        $debitoTotal += $lancamento->Valor;
                    }

                    if ($conta == $lancamento->ContaCreditoID) {
                        $creditoTotal += $lancamento->Valor;
                    }

                    $htmlTable .=
                        '<tr>
                <td>' .
                        $data .
                        '</td>
                <td>' .
                        $descricaocompleta .
                        '</td>
                <td style="text-align: right;">' .
                        ($conta == $lancamento->ContaDebitoID ? $valor : '') .
                        '</td>
                <td style="text-align: right;">' .
                        ($conta == $lancamento->ContaCreditoID ? $valor : '') .
                        '</td>

            </tr>';
                }
                $debitoTotalFormatado = number_format($debitoTotal, 2, ',', '.');
                $creditoTotalFormatado = number_format($creditoTotal, 2, ',', '.');
                $saldoAnteriorFormatado = number_format($saldoAnterior, 2, ',', '.');
                $htmlTable .= '<tr>
            <td colspan="4"><hr></td>
        </tr>';

                $htmlTable .=
                    '<tr>

                <td> TOTAL' .
                    '</td>
                <td>' .
                    '</td>
                <td style="text-align: right;">' .
                    ($debitoTotalFormatado ? $debitoTotalFormatado : '') .
                    '</td>
                <td style="text-align: right;">' .
                    ($creditoTotalFormatado ? $creditoTotalFormatado : '') .
                    '</td>
            </tr>';

                $saldo = $debitoTotal - $creditoTotal;
                $saldoFormatado = number_format($saldo, 2, ',', '.');

                $saldo = $saldoAnterior + $debitoTotal - $creditoTotal;
                $saldoFormatado = number_format($saldo, 2, ',', '.');

                $htmlTable .=
                    '<tr>
            <td> SALDO </td>
            <td></td>
            <td style="text-align: right;">' .
                    ($saldoFormatado != 0 ? $saldoFormatado : '') .
                    '</td>
        </tr>';

                $htmlTable .= '
                </tbody>
            </table>


        ';

                $html = $htmlTable;
                // Configurar e gerar o PDF com o Dompdf
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->render();

                // Salvar ou exibir o PDF
                $dompdf->stream('lancamentos.pdf', ['Attachment' => false]);

                // Obter o conteúdo do PDF
                // $output = $dompdf->output();

                // // Exibir o PDF em uma nova página
                // return response($output)
                //     ->header('Content-Type', 'application/pdf')
                //     ->header('Content-Disposition', 'inline; filename="lancamentos.pdf"');
    }

    public function gerarExtratoPdf_Bordas_Tabelas()
    {
        if (session('LancamentosPDF') == null) {
            return Redirect::back();
        }

        $lancamentosPDF = session('LancamentosPDF');

        $lancamentos = $lancamentosPDF['DadosExtrato'];
        $de = $lancamentosPDF['de'];
        $dataDivididade = explode(' ', $de);
        $deformatada = $dataDivididade[0];
        $descricaoconta = $lancamentosPDF['descricaoconta'];
        $conta = $lancamentosPDF['conta'];

        $ate = $lancamentosPDF['ate'];
        $dataDivididaate = explode(' ', $ate);
        $ateformatada = $dataDivididaate[0];

        $desa = $de;
        $contaID = $conta;
        $this->selEmpresa = $lancamentosPDF['empresa'];

        $totalCredito = Lancamento::where(function ($q) use ($desa, $contaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $desa);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($desa, $contaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $desa);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoAnterior = $totalDebito - $totalCredito;

        // Construir a tabela HTML
        $htmlTable = '<style>
            h1, h5 {
                text-align: center;
                margin: 10px 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }

            th {
                background-color: #f2f2f2;
            }

            .saldo-anterior {
                font-weight: bold;
            }

            .total {
                font-weight: bold;
            }
        </style>';

        $htmlTable .= '<h1>RELATÓRIO DE LANÇAMENTOS</h1>';
        $htmlTable .= '<h5>Conta: ' . $descricaoconta . '</h5>';
        $htmlTable .= '<h1>Período de: ' . $deformatada . ' à ' . $ateformatada . '</h1>';
        $htmlTable .=
            '
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Débito</th>
                        <th>Crédito</th>
                    </tr>
                    <tr>
                        <td colspan="4"><hr></td>
                    </tr>
                    <tr class="saldo-anterior">
                        <td colspan="3">SALDO ANTERIOR</td>
                        <td>' .
            number_format($saldoAnterior, 2, ',', '.') .
            '</td>
                    </tr>
                </thead>
                <tbody>';

        $debitoTotal = 0;
        $creditoTotal = 0;

        foreach ($lancamentosPDF['DadosExtrato'] as $lancamento) {
            $id = $lancamento->ID;
            $valor = number_format($lancamento->Valor, 2, ',', '.');
            $data = $lancamento->DataContabilidade->format('d/m/Y');
            $descricao = $lancamento['HistoricoDescricao'] . ' ' . $lancamento->Descricao;
            $descricaoQuebrada = wordwrap($descricao, 50, '<br>', true);

            if (strlen($descricao) < 50) {
                $descricaoPreenchida = str_pad($descricao, 50, ' ');
                $descricaocompleta = $descricaoPreenchida;
            } else {
                $descricaocompleta = $descricaoQuebrada;
            }

            if ($conta == $lancamento->ContaDebitoID) {
                $debitoTotal += $lancamento->Valor;
            }

            if ($conta == $lancamento->ContaCreditoID) {
                $creditoTotal += $lancamento->Valor;
            }

            $htmlTable .=
                '<tr>
                <td>' .
                $data .
                '</td>
                <td>' .
                $descricaocompleta .
                '</td>
                <td>' .
                ($conta == $lancamento->ContaDebitoID ? $valor : '') .
                '</td>
                <td>' .
                ($conta == $lancamento->ContaCreditoID ? $valor : '') .
                '</td>
            </tr>';
        }

        $debitoTotalFormatado = number_format($debitoTotal, 2, ',', '.');
        $creditoTotalFormatado = number_format($creditoTotal, 2, ',', '.');
        $saldoAnteriorFormatado = number_format($saldoAnterior, 2, ',', '.');

        $htmlTable .= '<tr>
            <td colspan="4"><hr></td>
        </tr>';

        $htmlTable .=
            '<tr class="total">
            <td> TOTAL</td>
            <td></td>
            <td>' .
            ($debitoTotalFormatado ? $debitoTotalFormatado : '') .
            '</td>
            <td>' .
            ($creditoTotalFormatado ? $creditoTotalFormatado : '') .
            '</td>
        </tr>';

        $saldo = $saldoAnterior + $debitoTotal - $creditoTotal;
        $saldoFormatado = number_format($saldo, 2, ',', '.');

        $htmlTable .=
            '<tr>
            <td> SALDO </td>
            <td></td>
            <td>' .
            ($saldoFormatado != 0 ? $saldoFormatado : '') .
            '</td>
        </tr>';

        $htmlTable .= '
            </tbody>
        </table>';

        $html = $htmlTable;

        // Configurar e gerar o PDF com o Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Salvar ou exibir o PDF
        $dompdf->stream('lancamentos.pdf', ['Attachment' => false]);
    }

    public function gerarExtratoPdf()
    {
        if (session('LancamentosPDF') == null) {
            return Redirect::back();
        }

        $lancamentosPDF = session('LancamentosPDF');

        $lancamentos = $lancamentosPDF['DadosExtrato'];

        $de = $lancamentosPDF['de'];
        $dataDivididade = explode(' ', $de);
        $deformatada = $dataDivididade[0];
        $descricaoconta = $lancamentosPDF['descricaoconta'];
        $conta = $lancamentosPDF['conta'];

        $ate = $lancamentosPDF['ate'];
        $dataDivididaate = explode(' ', $ate);
        $ateformatada = $dataDivididaate[0];

        $desa = $de;
        $contaID = $conta;
        $this->selEmpresa = $lancamentosPDF['empresa'];

        $totalCredito = Lancamento::where(function ($q) use ($desa, $contaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $desa);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($desa, $contaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $desa);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoAnterior = $totalDebito - $totalCredito;

        // Construir a tabela HTML
        $htmlTable = '<style>
            @page {
                margin-top: 50px;
            }

            h1, h5 {
                text-align: center;
                margin: 10px 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }

            th {
                background-color: #f2f2f2;
            }

            .saldo-anterior {
                font-weight: bold;
            }

            .total {
                font-weight: bold;
            }

            .header {
                position: fixed;
                top: -40px;
                left: 0;
                right: 0;
                height: 40px;
                background-color: #f2f2f2;
                text-align: center;
                line-height: 40px;
            }
        </style>';

        // $htmlTable .= '<div class="header">
        // <h5>Período de: ' . $deformatada . ' à ' . $ateformatada .  '</h5>
        // <h5>Conta: ' . $descricaoconta . '</h5>
        // </div>';

        // $htmlTable .= '<h1>RELATÓRIO DE LANÇAMENTOS</h1>';
        // $htmlTable .= '<h5>Conta: ' . $descricaoconta . '</h5>';
        // $htmlTable .= '<h1>Período de: ' . $deformatada . ' à ' . $ateformatada .  '</h1>';

        $htmlTable .=
            '

            <table>
                <thead>
                    <tr style="background-color: #eaf2ff;">
                            <th colspan="2" class="saldo-anterior"><h4>Período de: ' .
            $deformatada .
            ' à ' .
            $ateformatada .
            '</h4></td>
                            <th colspan="2" class="saldo-anterior"><h4>Conta: ' .
            $descricaoconta .
            '</h4></td>
                    </tr>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Débito</th>
                        <th>Crédito</th>
                    </tr>
                    <tr>
                        <td colspan="4"><hr></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="saldo-anterior">SALDO ANTERIOR</td>
                        <td style="text-align: right;">' .
            number_format($saldoAnterior, 2, ',', '.') .
            '</td>
                    </tr>
                </thead>
                <tbody>';

        $debitoTotal = 0;
        $creditoTotal = 0;

        foreach ($lancamentosPDF['DadosExtrato'] as $lancamento) {
            $id = $lancamento->ID;
            $valor = number_format($lancamento->Valor, 2, ',', '.');
            $data = $lancamento->DataContabilidade->format('d/m/Y');
            $descricao = $lancamento['HistoricoDescricao'] . ' ' . $lancamento->Descricao;
            $descricaoQuebrada = wordwrap($descricao, 50, '<br>', true);

            if (strlen($descricao) < 50) {
                $descricaoPreenchida = str_pad($descricao, 50, ' ');
                $descricaocompleta = $descricaoPreenchida;
            } else {
                $descricaocompleta = $descricaoQuebrada;
            }

            if ($conta == $lancamento->ContaDebitoID) {
                $debitoTotal += $lancamento->Valor;
            }

            if ($conta == $lancamento->ContaCreditoID) {
                $creditoTotal += $lancamento->Valor;
            }

            $htmlTable .=
                '<tr>
                <td>' .
                $data .
                '</td>
                <td>' .
                $descricaocompleta .
                '</td>
                <td style="text-align: right;">' .
                ($conta == $lancamento->ContaDebitoID ? $valor : '') .
                '</td>
                <td style="text-align: right;">' .
                ($conta == $lancamento->ContaCreditoID ? $valor : '') .
                '</td>
            </tr>';
        }

        $debitoTotalFormatado = number_format($debitoTotal, 2, ',', '.');
        $creditoTotalFormatado = number_format($creditoTotal, 2, ',', '.');
        $saldoAnteriorFormatado = number_format($saldoAnterior, 2, ',', '.');

        $htmlTable .= '<tr>
            <td colspan="4"><hr></td>
        </tr>';

        $htmlTable .=
            '<tr class="total">
            <td> TOTAL</td>
            <td></td>
            <td style="text-align: right;">' .
            ($debitoTotalFormatado ? $debitoTotalFormatado : '') .
            '</td>
            <td style="text-align: right;">' .
            ($creditoTotalFormatado ? $creditoTotalFormatado : '') .
            '</td>
        </tr>';

        $saldo = $saldoAnterior + $debitoTotal - $creditoTotal;
        $saldoFormatado = number_format($saldo, 2, ',', '.');

        $htmlTable .=
            '<tr class="total">
            <td> SALDO </td>
            <td></td>
            <td style="text-align: right;">' .
            ($saldoFormatado != 0 ? $saldoFormatado : '') .
            '</td>
        </tr>';

        $htmlTable .= '
            </tbody>
        </table>';

        // Configurar e gerar o PDF com o Dompdf
        $dompdf = new Dompdf();

        // Habilitar opção de cabeçalho
        $options = $dompdf->getOptions();
        $options->setIsHtml5ParserEnabled(true);
        $options->setIsRemoteEnabled(true);
        $options->setChroot(base_path());

        // Definir o cabeçalho
        $header = '<div style="text-align: center;">EXTRATO</div>';
        // $header = '<div style="text-align: center;">
        // <h5>Período de: ' . $deformatada . ' à ' . $ateformatada .  '</h5>
        // <h5>Conta: ' . $descricaoconta . '</h5>
        // </div>';
        // $options->setPdfBackendOptions(['enable_html5_parser' => true, 'enable_remote' => true]);
        $dompdf->setOptions($options);
        $dompdf->setBasePath(base_path());
        // $dompdf->setHttpContext(new Dompdf\FrameDecorator($header));

        $html = $header . $htmlTable;

        $dompdf->loadHtml($html);
        $dompdf->render();

        // Salvar ou exibir o PDF
        $dompdf->stream('lancamentos.pdf', ['Attachment' => false]);

        // Obter o conteúdo do PDF
        // $output = $dompdf->output();

        // Exibir o PDF em uma nova página
        // return response($output)
        //     ->header('Content-Type', 'application/pdf')
        //     ->header('Content-Disposition', 'inline; filename="lancamentos.pdf"');
    }

    public function contasGabrielMagossiFalchiMes()
    {
        $nome = 'GABRIEL MAGOSSI FALCHI';
        $dados = [];
        $lancamentos = Lancamento::limit(0);
        $contaID = 11146; // PRF

        session(['Extrato_Ate' => $this->Ate]);
        session(['Extrato_De' => $this->De]);
        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $agrupadosPorMesAno = [];

        foreach ($lancamentos as $lancamento) {
            // Tenta criar o objeto DateTime a partir do formato 'Y-m-d'
            $data = $lancamento['DataContabilidade'];

            // Verifica se a data foi criada corretamente
            if ($data) {
                // Extrair ano e mês da DataContabilidade
                $anoMes = $data->format('Y-m'); // Formato YYYY-MM

                // Agrupar os lançamentos por ano e mês
                if (!isset($agrupadosPorMesAno[$anoMes])) {
                    $agrupadosPorMesAno[$anoMes] = [];
                }

                $agrupadosPorMesAno[$anoMes][] = $lancamento;
            }
        }

        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;

        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }

        $dados[] = [
            'Nome' => $nome,
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];

        $DadosMes11146 = $agrupadosPorMesAno;
        // ================================================================================================================
        $dados = [];
        $lancamentos = Lancamento::limit(0);
        $contaID = 19538; // STTARMAAKE

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $agrupadosPorMesAno = [];
        foreach ($lancamentos as $lancamento) {
            $data = $lancamento['DataContabilidade'];
            if ($data) {
                $anoMes = $data->format('Y-m');
                if (!isset($agrupadosPorMesAno[$anoMes])) {
                    $agrupadosPorMesAno[$anoMes] = [];
                }
                $agrupadosPorMesAno[$anoMes][] = $lancamento;
            }
        }
        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;
        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }
        $dados[] = [
            'Nome' => $nome,
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];
        $DadosMes19538 = $agrupadosPorMesAno;
        // ================================================================================================================
        $dados = [];
        $lancamentos = Lancamento::limit(0);
        $contaID = 15394; //PEDRO

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $agrupadosPorMesAno = [];
        foreach ($lancamentos as $lancamento) {
            $data = $lancamento['DataContabilidade'];
            if ($data) {
                $anoMes = $data->format('Y-m');
                if (!isset($agrupadosPorMesAno[$anoMes])) {
                    $agrupadosPorMesAno[$anoMes] = [];
                }
                $agrupadosPorMesAno[$anoMes][] = $lancamento;
            }
        }
        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;
        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }
        $dados[] = [
            'Nome' => $nome,
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];
        $DadosMes15394 = $agrupadosPorMesAno;

        //  ================================================================================================================

        $dados = [];
        $lancamentos = Lancamento::limit(0);
        $contaID = 11142; //INFRANET

        $lancamentos = $this->selecionarLancamento($contaID)['lancamentos'];
        $de = $this->selecionarLancamento($contaID)['de'];
        $ate = $this->selecionarLancamento($contaID)['ate'];

        $agrupadosPorMesAno = [];
        foreach ($lancamentos as $lancamento) {
            $data = $lancamento['DataContabilidade'];
            if ($data) {
                $anoMes = $data->format('Y-m');
                if (!isset($agrupadosPorMesAno[$anoMes])) {
                    $agrupadosPorMesAno[$anoMes] = [];
                }
                $agrupadosPorMesAno[$anoMes][] = $lancamento;
            }
        }
        $debito = $lancamentos->where('ContaDebitoID', $contaID)->sum('Valor');
        $credito = $lancamentos->where('ContaCreditoID', $contaID)->sum('Valor');
        $saldo = $debito - $credito;
        $NomeEmpresa = null;

        foreach ($lancamentos as $lancamento) {
            $NomeEmpresa = $lancamento->NomeEmpresa;
        }
        $dados[] = [
            'Nome' => $nome,
            'Selecionados' => $lancamentos->count(),
            'Débito' => $debito,
            'Crédito' => $credito,
            'De' => $de ?? 'Não informado',
            'Até' => $ate ?? 'Não informado',
            'Saldo' => $saldo,
            'Empresa' => $NomeEmpresa ?? 'Nenhuma empresa encontrada',
        ];
        $DadosMes11142 = $agrupadosPorMesAno;

        //  ================================================================================================================

        $contaID = 19532; //NET RUBI SERVICOS

        //  ================================================================================================================
        // Primeiro array
        $dados1 = $DadosMes11146;
        // Segundo array
        $dados2 = $DadosMes19538;

        // Função para unir os arrays com mesclagem de meses repetidos
        foreach ($dados2 as $mes => $lancamentos) {
            if (isset($dados1[$mes])) {
                // Se o mês já existe em $dados1, mescla os valores
                $dados1[$mes] = array_merge($dados1[$mes], $lancamentos);
            } else {
                // Se o mês não existe, simplesmente adiciona o novo mês
                $dados1[$mes] = $lancamentos;
            }
        }
        //  ================================================================================================================

        $dados2 = $DadosMes15394;

        // Função para unir os arrays com mesclagem de meses repetidos
        foreach ($dados2 as $mes => $lancamentos) {
            if (isset($dados1[$mes])) {
                // Se o mês já existe em $dados1, mescla os valores
                $dados1[$mes] = array_merge($dados1[$mes], $lancamentos);
            } else {
                // Se o mês não existe, simplesmente adiciona o novo mês
                $dados1[$mes] = $lancamentos;
            }
        }
        //  ================================================================================================================
        $dados2 = $DadosMes11142;

        // Função para unir os arrays com mesclagem de meses repetidos
        foreach ($dados2 as $mes => $lancamentos) {
            if (isset($dados1[$mes])) {
                // Se o mês já existe em $dados1, mescla os valores
                $dados1[$mes] = array_merge($dados1[$mes], $lancamentos);
            } else {
                // Se o mês não existe, simplesmente adiciona o novo mês
                $dados1[$mes] = $lancamentos;
            }
        }
        //  ================================================================================================================

        $DadosMes = $dados1;

        return redirect()->route('lancamentos.DadosMes')->with('DadosMes', $DadosMes)->with('nome', $nome);
    }
}
