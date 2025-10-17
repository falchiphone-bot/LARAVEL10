<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BalanceteExport implements FromArray, WithHeadings, WithTitle
{
    protected array $linhas;
    protected ?array $grupos = null;
    protected float $totDeb;
    protected float $totCred;
    protected float $totSaldo;

    public function __construct(array $linhas, float $totDeb, float $totCred, float $totSaldo, ?array $grupos = null)
    {
        $this->linhas = $linhas;
        $this->totDeb = $totDeb;
        $this->totCred = $totCred;
        $this->totSaldo = $totSaldo;
        $this->grupos = $grupos;
    }

    public function array(): array
    {
        $rows = [];
        // Helper para DRE simples a partir dos grupos
        $dreReceitas = 0.0; $dreDespesas = 0.0; $dreResultado = 0.0; $dreIsPrejuizo = false;
        if (is_array($this->grupos) && !empty($this->grupos)) {
            $totDespDeb = (float)($this->grupos['3']['totDeb'] ?? 0);
            $totDespCred = (float)($this->grupos['3']['totCred'] ?? 0);
            $totRecDeb = (float)($this->grupos['4']['totDeb'] ?? 0);
            $totRecCred = (float)($this->grupos['4']['totCred'] ?? 0);
            $dreDespesas = max(0.0, $totDespDeb - $totDespCred);
            $dreReceitas = max(0.0, $totRecCred - $totRecDeb);
            $dreResultado = $dreReceitas - $dreDespesas;
            $dreIsPrejuizo = $dreResultado < 0;
        }
        if (is_array($this->grupos) && !empty($this->grupos)) {
            foreach ($this->grupos as $g) {
                // Cabeçalho do grupo
                $rows[] = [ $g['label'] ?? '', '', '', '', '' ];
                foreach ($g['linhas'] as $l) {
                    $nome = $l['conta'] ?? '';
                    $classificacao = $l['codigo'] ?? '';
                    $colConta = $nome;
                    $rows[] = [
                        $colConta,
                        $classificacao,
                        number_format((float)$l['debito'], 2, ',', '.'),
                        number_format((float)$l['credito'], 2, ',', '.'),
                        number_format((float)$l['saldo'], 2, ',', '.'),
                    ];
                }
                // Subtotal do grupo
                $rows[] = [
                    'Subtotal '.($g['label'] ?? ''),
                    '',
                    number_format((float)($g['totDeb'] ?? 0), 2, ',', '.'),
                    number_format((float)($g['totCred'] ?? 0), 2, ',', '.'),
                    number_format((float)($g['totSaldo'] ?? 0), 2, ',', '.'),
                ];
                // Linha em branco entre grupos
                $rows[] = ['', '', '', '', ''];
            }
        } else {
            foreach ($this->linhas as $l) {
                $nome = $l['conta'] ?? '';
                $classificacao = $l['codigo'] ?? '';
                $colConta = $nome;
                $rows[] = [
                    $colConta,
                    $classificacao,
                    number_format((float)$l['debito'], 2, ',', '.'),
                    number_format((float)$l['credito'], 2, ',', '.'),
                    number_format((float)$l['saldo'], 2, ',', '.'),
                ];
            }
        }
        // linha de totais
        $rows[] = [
            'TOTAL',
            '',
            number_format($this->totDeb, 2, ',', '.'),
            number_format($this->totCred, 2, ',', '.'),
            number_format($this->totSaldo, 2, ',', '.'),
        ];

        // DRE ao final (se houver grupos para base de cálculo)
        if (is_array($this->grupos) && !empty($this->grupos)) {
            $rows[] = ['', '', '', '', ''];
            $rows[] = ['Demonstrativo de Resultado', '', '', '', ''];
            $rows[] = ['Receitas', '', '', '', number_format($dreReceitas, 2, ',', '.')];
            $rows[] = ['Despesas', '', '', '', number_format($dreDespesas, 2, ',', '.')];
            $rows[] = [
                'Resultado',
                '',
                '',
                '',
                number_format($dreResultado, 2, ',', '.') . ($dreIsPrejuizo ? ' (PREJUÍZO)' : ' (LUCRO)')
            ];
        }
        return $rows;
    }

    public function headings(): array
    {
    return ['Conta', 'Classificação', 'Débito', 'Crédito', 'Saldo'];
    }

    public function title(): string
    {
        return 'Balancete';
    }
}
