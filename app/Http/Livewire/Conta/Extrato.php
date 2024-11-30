<?php

namespace App\Http\Livewire\Conta;

use App\Exports\LancamentoExport;
use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\SolicitacaoExclusao;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use LancamentoExport as GlobalLancamentoExport;
use Maatwebsite\Excel\Facades\Excel;

use function JmesPath\search;

class Extrato extends Component
{
    //mudança de empresas e contas
    public $selEmpresa;
    public $selConta;

    //models carregadas
    public $Conta;
    public $Lancamentos;

    public $LancamentosPDF;

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
    public $SaidasGeral;
    public $EntradasGeral;
    public $Notificacao;
    public $DataBloqueio;
    public $data_bloqueio_conta;
    public $data_bloqueio_empresa;

    public $exibicao_pesquisa;
    public $editar_lancamento = false;
    //resolvendo problema do select2
    public $modal = false;

    protected $listeners = ['selectedSelEmpresaItem', 'selectedSelContaItem', 'search', 'alterarData'];

    public function editarLancamento($lancamento_id, $empresa_id = null)
    {
        $this->editar_lancamento = $lancamento_id;
        $this->emitTo('lancamento.editar-lancamento', 'alterarIdLancamento', $lancamento_id, $empresa_id);
        $this->emitTo('lancamento.arquivo-lancamento', 'resetData', $lancamento_id);
        $this->dispatchBrowserEvent('abrir-modal');
        $this->modal = true;

        // dd('Editanto Lancamento');
    }

    public function alterarData($date)
    {
        $novadadata = Carbon::parse($date);
        foreach ($this->Lancamentos as $lancamento) {
            if (!$this->temBloqueio($lancamento->ID, $date)) {
                $lancamento->DataContabilidade = $novadadata->format('d-m-Y');
                $lancamento->save();
            }
        }
    }

    public function selectedSelEmpresaItem($item)
    {
        if ($item) {
            $this->selEmpresa = $item;
            session(['conta.extrato.empresa.id' => $this->selEmpresa]);
            $this->Empresa = Empresa::find($item);
            $this->data_bloqueio_empresa = $this->Empresa->Bloqueiodataanterior?->format('Y-m-d');
            $this->selConta = null;
            $this->Lancamentos = null;
        } else {
            $this->selEmpresa = null;
            session(['conta.extrato.empresa.id' => $this->selEmpresa]);
            $this->selConta = null;
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

    public function mount($contaID)
    {
        session(['extrato_ContaID' => $contaID]);
        $this->De = session('Extrato_De') ?? date('Y-m-d');
        $this->Ate = session('Extrato_Ate') ?? date('Y-m-d');

        $this->Conta = Conta::find($contaID);
        $this->selEmpresa = $this->Conta->EmpresaID;
        session(['conta.extrato.empresa.id' => $this->selEmpresa]);

        $this->Empresa = Empresa::find($this->selEmpresa);
        $this->data_bloqueio_conta = $this->Conta->Bloqueiodataanterior?->format('Y-m-d');
        $this->data_bloqueio_empresa = $this->Empresa->Bloqueiodataanterior?->format('Y-m-d');
        $this->selConta = $this->Conta->ID;

        $de = Carbon::createFromDate($this->De)->format('d-m-Y 00:00:00');
        $ate = Carbon::createFromDate($this->Ate)->format('d-m-Y 23:59:59');

        $lancamentos = Lancamento::where(function ($query) use ($contaID) {
            return $query->where('ContaDebitoID', $contaID)->orWhere('ContaCreditoID', $contaID);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->where(function ($where) use ($de, $ate) {
                return $where->where('DataContabilidade', '>=', $de)->where('DataContabilidade', '<=', $ate);
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
        $contaID = session('extrato_ContaID') ?? $this->selConta;

        // dd($contaID);
        if ($contaID) {
            $lancamentos = Lancamento::where(function ($query) use ($contaID) {
                return $query->where('Lancamentos.ContaDebitoID', $contaID)->orWhere('Lancamentos.ContaCreditoID', $contaID);
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

            if ($this->Descricao) {
                $lancamentos->where(function ($q) {
                    $q->where('Lancamentos.Descricao', 'like', "%$this->Descricao%")->orWhere('Historicos.Descricao', 'like', "%$this->Descricao%");
                });
            }
            if ($this->Conferido != '') {
                if ($this->Conferido == 'false') {
                    $lancamentos->where(function ($q) {
                        return $q->whereNull('Conferido')->orWhere('Conferido', 0);
                    });
                }

                // if ($this->Conferido == 'SaidasGeral') {
                //     $lancamentos->where(function ($q) {
                //         return $q->whereNull('SaidasGeral')->orWhere('SaidasGeral', 1);
                //     });
                // }
                // if ($this->Conferido == 'EntradasGeral') {
                //     $lancamentos->where(function ($q) {
                //         return $q->whereNull('EntradasGeral')->orWhere('EntradasGeral', 1);
                //     });
                // }
                else {
                    $lancamentos->where('Conferido', $this->Conferido);
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

            if ($this->Notificacao != '') {
                $lancamentos->where('notificacao', $this->Notificacao);
            }

            if ($this->Conferido === 'SaidasGeral' || $this->Conferido === 'EntradasGeral') {
                dd('VERIFICAR A SELEÇÃO');
            }

            $this->Lancamentos = $lancamentos
                ->orderBy('DataContabilidade')
                ->whereDoesntHave('SolicitacaoExclusao')
                ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
                ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'Lancamentos.ValorQuantidadeDolar','DataContabilidade', 'Lancamentos.ContaCreditoID',
                 'Lancamentos.ContaDebitoID', 'Lancamentos.Descricao', 'Historicos.Descricao as HistoricoDescricao',
                 'Conferido', 'SaidasGeral', 'EntradasGeral', 'Investimentos', 'Transferencias', 'SemDefinir']);
        } else {
            $this->Lancamentos = null;
        }
    }
    public function searchSaidasGeral()
    {
        if ($this->Conferido !== 'SaidasGeral') {
            dd('VERIFICAR A SELEÇÃO');
        }

        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $SaidasGeral = 1;

        $lancamentos = Lancamento::where(function ($query) use ($SaidasGeral) {
            return $query->where('Lancamentos.SaidasGeral', 1);
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
                ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
                ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'DataContabilidade', 'Lancamentos.ContaCreditoID', 'Lancamentos.Descricao', 'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral']);
        }

        $SaidasGeral = 0;
    }

    public function searchSaidasGeralExcel()
    {
        if ($this->Conferido !== 'SaidasGeral') {
            dd('VERIFICAR A SELEÇÃO PARA EXCEL');
        }

        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $SaidasGeral = 1;

        $lancamentos = Lancamento::where(function ($query) use ($SaidasGeral) {
            return $query->where('Lancamentos.SaidasGeral', 1);
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
                ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
                ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'DataContabilidade', 'Lancamentos.ContaDebitoID', 'Lancamentos.ContaCreditoID', 'Lancamentos.Descricao', 'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral']);
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

        // dd($ExportarUnir);

        // Caminho do arquivo .csv que você deseja criar na pasta "storage"
        $Arquivo = 'Pagamentos por PEDRO ROBERTO FALCHI E SANDRA ELISA MAGOSSI FALCHI' . '-' . str_replace('/', '', $de) . '-a-' . str_replace('/', '', $ate) . '.xlsx';

        return Excel::download(new LancamentoExport($exportarUnir), "$Arquivo");
    }

    public function searchSaidasGeralSoma()
    {
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
            if ($this->Descricao && $this->DescricaoApartirDe) {
                $de = Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->format('d/m/Y 00:00:00');
            } else {
                $de = Carbon::createFromFormat('Y-m-d', $this->De)->format('d/m/Y 00:00:00');
            }
            $lancamentosSaida->where('DataContabilidade', '>=', $de);
            $lancamentosEntrada->where('DataContabilidade', '>=', $de);
        }
        if ($this->Ate) {
            $ate = Carbon::createFromFormat('Y-m-d', $this->Ate)->format('d/m/Y 23:59:59');
            $lancamentosSaida->where('DataContabilidade', '<=', $ate);
            $lancamentosEntrada->where('DataContabilidade', '<=', $ate);
        }

        $totalsomadoSAIDAS = $lancamentosSaida->sum('Valor');

        $totalsomadoEntradas = $lancamentosEntrada->sum('Valor');

        dd(' TOTAL SOMADO DE TODAS ENTRADAS EM GERAL: ', $totalsomadoEntradas, ' TOTAL SOMADO DE TODAS SAIDAS EM GERAL: ', $totalsomadoSAIDAS, ' RESULTADO ENTRE ENTRADAS E SAIDAS: ', $totalsomadoEntradas - $totalsomadoSAIDAS, ' PERÍODO DE: ' . $de, ' A ' . $ate);

        $SaidasGeral = 0;
    }

    public function searchEntradasGeral()
    {
        if ($this->Conferido !== 'EntradasGeral') {
            dd('VERIFICAR A SELEÇÃO');
        }

        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $EntradasGeral = 1;

        $lancamentos = Lancamento::where(function ($query) use ($EntradasGeral) {
            return $query->where('Lancamentos.EntradasGeral', 1);
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
                ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
                ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'DataContabilidade', 'Lancamentos.ContaCreditoID', 'Lancamentos.Descricao', 'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral', 'EntradasGeral']);
        }

        $EntradasGeral = 0;
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
                ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'DataContabilidade', 'Lancamentos.ContaCreditoID', 'Lancamentos.Descricao',
                 'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral', 'EntradasGeral', 'Transferencias']);
        }

        $Transferencias = 0;
    }



    public function searchEntradasGeralExcel()
    {
        if ($this->Conferido !== 'EntradasGeral') {
            dd('VERIFICAR A SELEÇÃO');
        }

        $contaID = session('extrato_ContaID') ?? $this->selConta;
        $EntradasGeral = 1;

        $lancamentos = Lancamento::where(function ($query) use ($EntradasGeral) {
            return $query->where('Lancamentos.EntradasGeral', 1);
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
                ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
                ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'DataContabilidade', 'Lancamentos.ContaDebitoID', 'Lancamentos.ContaCreditoID', 'Lancamentos.Descricao', 'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral', 'EntradasGeral']);
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

        // dd($ExportarUnir);

        // Caminho do arquivo .csv que você deseja criar na pasta "storage"
        $Arquivo = 'Entradas para PEDRO ROBERTO FALCHI E SANDRA ELISA MAGOSSI FALCHI' . '-' . str_replace('/', '', $de) . '-a-' . str_replace('/', '', $ate) . '.xlsx';

        return Excel::download(new LancamentoExport($exportarUnir), "$Arquivo");
    }

    public function searchPDF()
    {
        $contaID = cache('extrato_ContaID') ?? $this->selConta;
        if ($contaID) {
            $lancamentos = Lancamento::where(function ($query) use ($contaID) {
                return $query->where('Lancamentos.ContaDebitoID', $contaID)->orWhere('Lancamentos.ContaCreditoID', $contaID);
            });

            if ($this->De) {
                if ($this->Descricao && $this->DescricaoApartirDe) {
                    $de = Carbon::createFromFormat('Y-m-d', $this->DescricaoApartirDe)->format('d/m/Y 00:00:00');
                } else {
                    $de = Carbon::createFromFormat('Y-m-d', $this->De)->format('d/m/Y 00:00:00');
                }
                $lancamentos->where('DataContabilidade', '>=', $de);
                cache(['Extrato_De' => $this->De]);
            }
            if ($this->Ate) {
                $ate = Carbon::createFromFormat('Y-m-d', $this->Ate)->format('d/m/Y 23:59:59');
                $lancamentos->where('DataContabilidade', '<=', $ate);
                cache(['Extrato_Ate' => $this->Ate]);
            }

            if ($this->Descricao) {
                $lancamentos->where(function ($q) {
                    $q->where('Lancamentos.Descricao', 'like', "%$this->Descricao%")->orWhere('Historicos.Descricao', 'like', "%$this->Descricao%");
                });
            }
            if ($this->Conferido != '') {
                if ($this->Conferido == 'false') {
                    $lancamentos->where(function ($q) {
                        return $q->whereNull('Conferido')->orWhere('Conferido', 0);
                    });
                } else {
                    $lancamentos->where('Conferido', $this->Conferido);
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

            if ($this->Notificacao != '') {
                $lancamentos->where('notificacao', $this->Notificacao);
            }
            $this->Lancamentos = $lancamentos
                ->orderBy('DataContabilidade')
                ->whereDoesntHave('SolicitacaoExclusao')
                ->leftjoin('Contabilidade.Historicos', 'Historicos.ID', 'HistoricoID')
                ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'DataContabilidade', 'Lancamentos.ContaCreditoID', 'Lancamentos.ContaDebitoID', 'Lancamentos.Descricao', 'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral', 'EntradasGeral']);

            return redirect()
                ->route('Extrato.gerarpdf')
                ->with('LancamentosPDF', [
                    'DadosExtrato' => $this->Lancamentos,
                    'de' => $de,
                    'ate' => $ate,
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
            ->get(['Lancamentos.ID', 'Lancamentos.Valor', 'Empresas.Descricao as NomeEmpresa', 'DataContabilidade', 'Lancamentos.ContaCreditoID', 'Lancamentos.ContaDebitoID', 'Lancamentos.Descricao', 'Historicos.Descricao as HistoricoDescricao', 'Conferido', 'SaidasGeral', 'EntradasGeral']);

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

        dd($dolard, $dolarc, $dolar);

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
            dd('REGISTRO JÁ MARCADO COMO SAIDA GERAL');
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

        $lancamento->DataContabilidade = $novaData->format('d-m-Y');

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
        $de = Carbon::createFromDate($this->De)->format('d/m/Y');
        $contaID = $this->selConta;
        $totalCredito = Lancamento::where(function ($q) use ($de, $contaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $de);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($de, $contaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $de);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoAnterior = $totalDebito - $totalCredito;


        $totalCreditoDolar = Lancamento::where(function ($q) use ($de, $contaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $de);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.ValorQuantidadeDolar');

        $totalDebitoDolar = Lancamento::where(function ($q) use ($de, $contaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $this->selEmpresa)
                ->where('DataContabilidade', '<', $de);
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


        return view('livewire.conta.extrato', compact('empresas', 'contas', 'saldoAnterior', 'saldoAnteriorDolar'));
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
