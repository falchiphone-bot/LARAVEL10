<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalanceteExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected array $linhas;
    protected ?array $grupos = null;
    protected float $totDeb;
    protected float $totCred;
    protected float $totSaldo;
    protected bool $showTrail = true;
    protected array $grau1Labels = [];
    protected array $grau2Labels = [];
    protected array $grau3Labels = [];
    protected array $grau4Labels = [];
    protected bool $showHier = false;
    protected bool $showPrev = true;
    /** @var int[] */
    protected array $saldoAnteriorRowIndexes = [];
    /** @var int[] */
    protected array $saldoAnteriorGrau5RowIndexes = [];
    /** @var int|null */
    protected ?int $dreResultadoRowIndex = null;

    public function __construct(array $linhas, float $totDeb, float $totCred, float $totSaldo, ?array $grupos = null, bool $showTrail = true, array $grau1Labels = [], array $grau2Labels = [], array $grau3Labels = [], array $grau4Labels = [], bool $showHier = false, bool $showPrev = true)
    {
        $this->linhas = $linhas;
        $this->totDeb = $totDeb;
        $this->totCred = $totCred;
        $this->totSaldo = $totSaldo;
        $this->grupos = $grupos;
        $this->showTrail = $showTrail;
        $this->grau1Labels = $grau1Labels;
        $this->grau2Labels = $grau2Labels;
        $this->grau3Labels = $grau3Labels;
        $this->grau4Labels = $grau4Labels;
        $this->showHier = $showHier;
        $this->showPrev = $showPrev;
    }

    public function array(): array
    {
        $rows = [];
        // Se modo hierárquico estiver ativo, renderiza a árvore (graus 1..5)
        if ($this->showHier) {
            $nodeTotals = [];
            $leafNames = [];
            $nodePrevTotals = [];
            foreach (($this->linhas ?? []) as $l) {
                $code = trim((string)($l['codigo'] ?? ''));
                if ($code === '') continue;
                $leafNames[$code] = $l['conta'] ?? '';
                $parts = array_values(array_filter(explode('.', $code), fn($p)=>$p!=='' && $p!==null));
                $n = count($parts);
                for ($k=1; $k<=min($n,5); $k++) {
                    $prefix = implode('.', array_slice($parts, 0, $k));
                    if (!isset($nodeTotals[$prefix])) $nodeTotals[$prefix] = ['deb'=>0.0,'cred'=>0.0,'saldo'=>0.0];
                    $nodeTotals[$prefix]['deb'] += (float)($l['debito'] ?? 0);
                    $nodeTotals[$prefix]['cred'] += (float)($l['credito'] ?? 0);
                    $nodeTotals[$prefix]['saldo'] += (float)($l['saldo'] ?? 0);
                }
                if ($this->showPrev) {
                    $prev = (float)($l['saldo_anterior'] ?? 0);
                    if ($prev != 0) {
                        for ($k=1; $k<=min($n,4); $k++) {
                            $prefix = implode('.', array_slice($parts, 0, $k));
                            if (!isset($nodePrevTotals[$prefix])) $nodePrevTotals[$prefix] = 0.0;
                            $nodePrevTotals[$prefix] += $prev;
                        }
                    }
                }
            }
            $codes = array_keys($nodeTotals);
            natcasesort($codes);
            foreach ($codes as $code) {
                $parts = array_values(array_filter(explode('.', $code)));
                $grau = count($parts);
                $label = $code;
                if ($grau === 1) { $label = $this->grau1Labels[$code] ?? $code; }
                elseif ($grau === 2) { $label = $this->grau2Labels[$code] ?? $code; }
                elseif ($grau === 3) { $label = $this->grau3Labels[$code] ?? $code; }
                elseif ($grau === 4) { $label = $this->grau4Labels[$code] ?? $code; }
                else { $label = $leafNames[$code] ?? $code; }
                $nt = $nodeTotals[$code];
                $prevNode = $nodePrevTotals[$code] ?? 0;
                if ($this->showPrev && $grau <= 4 && abs($prevNode) > 0) {
                    $rows[] = [
                        'Saldo Anterior', $code,
                        '', '',
                        number_format((float)$prevNode, 2, ',', '.'),
                    ];
                    $this->saldoAnteriorRowIndexes[] = count($rows); // 1-based após push
                }
                if ($this->showPrev && $grau === 5 && abs($prevNode) > 0) {
                    $rows[] = [
                        'Saldo Anterior', $code,
                        '', '',
                        number_format((float)$prevNode, 2, ',', '.'),
                    ];
                    $this->saldoAnteriorGrau5RowIndexes[] = count($rows);
                }
                $indentLabel = str_repeat('  ', max(0, $grau - 1)) . $label;
                $rows[] = [
                    $indentLabel,
                    $code,
                    number_format((float)$nt['deb'], 2, ',', '.'),
                    number_format((float)$nt['cred'], 2, ',', '.'),
                    number_format((float)$nt['saldo'], 2, ',', '.'),
                ];
            }
            // Totais gerais
            $rows[] = [
                'TOTAL',
                '',
                number_format($this->totDeb, 2, ',', '.'),
                number_format($this->totCred, 2, ',', '.'),
                number_format($this->totSaldo, 2, ',', '.'),
            ];
            // DRE simples a partir dos grupos (se houver)
            if (is_array($this->grupos) && !empty($this->grupos)) {
                $totDespDeb = (float)($this->grupos['3']['totDeb'] ?? 0);
                $totDespCred = (float)($this->grupos['3']['totCred'] ?? 0);
                $totRecDeb = (float)($this->grupos['4']['totDeb'] ?? 0);
                $totRecCred = (float)($this->grupos['4']['totCred'] ?? 0);
                $dreDespesas = max(0.0, $totDespDeb - $totDespCred);
                $dreReceitas = max(0.0, $totRecCred - $totRecDeb);
                $dreResultado = $dreReceitas - $dreDespesas;
                $rows[] = ['', '', '', '', ''];
                $rows[] = ['Demonstrativo de Resultado', '', '', '', ''];
                $rows[] = ['Receitas', '', '', '', number_format($dreReceitas, 2, ',', '.')];
                $rows[] = ['Despesas', '', '', '', number_format($dreDespesas, 2, ',', '.')];
                $rows[] = ['Resultado', '', '', '', number_format($dreResultado, 2, ',', '.')];
                $this->dreResultadoRowIndex = count($rows);
            }
            return $rows;
        }
        // Pré-calcular subtotais de Grau 4
        $buildNivel4Totals = function(array $list){
            $tot = [];
            foreach($list as $l){
                $code = trim((string)($l['codigo'] ?? ''));
                if($code === '') continue;
                $parts = array_values(array_filter(explode('.', $code)));
                if(count($parts) >= 5){
                    $prefix4 = implode('.', array_slice($parts, 0, 4));
                    if(!isset($tot[$prefix4])) $tot[$prefix4] = ['deb'=>0.0,'cred'=>0.0,'saldo'=>0.0];
                    $tot[$prefix4]['deb'] += (float)($l['debito'] ?? 0);
                    $tot[$prefix4]['cred'] += (float)($l['credito'] ?? 0);
                    $tot[$prefix4]['saldo'] += (float)($l['saldo'] ?? 0);
                }
            }
            return $tot;
        };
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
                $nivel4Totals = $buildNivel4Totals($g['linhas'] ?? []);
                // Soma de saldos anteriores por prefixo de grau 4
                $nivel4PrevTotals = [];
                if ($this->showPrev) {
                    foreach (($g['linhas'] ?? []) as $lPrev) {
                        $codePrev = trim((string)($lPrev['codigo'] ?? ''));
                        if ($codePrev === '') continue;
                        $partsPrev = array_values(array_filter(explode('.', $codePrev)));
                        if (count($partsPrev) >= 5) {
                            $p4 = implode('.', array_slice($partsPrev,0,4));
                            $nivel4PrevTotals[$p4] = ($nivel4PrevTotals[$p4] ?? 0) + (float)($lPrev['saldo_anterior'] ?? 0);
                        }
                    }
                }
                $lastPrefix4 = null;
                foreach (($g['linhas'] ?? []) as $idx => $l) {
                    $nome = $l['conta'] ?? '';
                    $classificacao = $l['codigo'] ?? '';
                    $code = trim((string)$classificacao);
                    $parts = $code !== '' ? array_values(array_filter(explode('.', $code))) : [];
                    $prefix4 = count($parts) >= 5 ? implode('.', array_slice($parts, 0, 4)) : null;
                    if($prefix4 && $prefix4 !== $lastPrefix4){
                        $lastPrefix4 = $prefix4; $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0];
                        $prev4 = $nivel4PrevTotals[$prefix4] ?? 0;
                        if ($this->showTrail) {
                            $partsPrefix = explode('.', $prefix4);
                            $p1 = $partsPrefix[0] ?? null;
                            $p2 = count($partsPrefix)>=2 ? implode('.', array_slice($partsPrefix,0,2)) : null;
                            $p3 = count($partsPrefix)>=3 ? implode('.', array_slice($partsPrefix,0,3)) : null;
                            $trail = [];
                            if(!empty($p1) && !empty(($this->grau1Labels[$p1] ?? null))) $trail[] = ($this->grau1Labels[$p1] ?? '') . " ($p1)";
                            if(!empty($p2) && !empty(($this->grau2Labels[$p2] ?? null))) $trail[] = ($this->grau2Labels[$p2] ?? '') . " ($p2)";
                            if(!empty($p3) && !empty(($this->grau3Labels[$p3] ?? null))) $trail[] = ($this->grau3Labels[$p3] ?? '') . " ($p3)";
                            if(!empty($trail)){
                                $rows[] = [ implode(' • ', $trail), '', '', '', '' ];
                            }
                        }
                        if ($this->showPrev && abs($prev4) > 0) {
                            $rows[] = [
                                'Saldo Anterior', $prefix4,
                                '', '',
                                number_format((float)$prev4, 2, ',', '.'),
                            ];
                            $this->saldoAnteriorRowIndexes[] = count($rows);
                        }
                        $rows[] = [
                            'Subtotal Grau 4', $prefix4,
                            number_format((float)$tot4['deb'], 2, ',', '.'),
                            number_format((float)$tot4['cred'], 2, ',', '.'),
                            number_format((float)$tot4['saldo'], 2, ',', '.'),
                        ];
                    }
                    $rows[] = [
                        $nome,
                        $classificacao,
                        number_format((float)$l['debito'], 2, ',', '.'),
                        number_format((float)$l['credito'], 2, ',', '.'),
                        number_format((float)$l['saldo'], 2, ',', '.'),
                    ];
                    $nextPrefix4 = null;
                    if(isset($g['linhas'][$idx+1])){
                        $n = $g['linhas'][$idx+1];
                        $nCode = trim((string)($n['codigo'] ?? ''));
                        $nParts = $nCode !== '' ? array_values(array_filter(explode('.', $nCode))) : [];
                        $nextPrefix4 = count($nParts) >= 5 ? implode('.', array_slice($nParts, 0, 4)) : null;
                    }
                    if($prefix4 && $prefix4 !== $nextPrefix4){
                        $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0];
                        $rows[] = [
                            'Total Grau 4', $prefix4,
                            number_format((float)$tot4['deb'], 2, ',', '.'),
                            number_format((float)$tot4['cred'], 2, ',', '.'),
                            number_format((float)$tot4['saldo'], 2, ',', '.'),
                        ];
                    }
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
            $nivel4Totals = $buildNivel4Totals($this->linhas);
            $nivel4PrevTotals = [];
            if ($this->showPrev) {
                foreach (($this->linhas ?? []) as $lPrev) {
                    $codePrev = trim((string)($lPrev['codigo'] ?? ''));
                    if ($codePrev === '') continue;
                    $partsPrev = array_values(array_filter(explode('.', $codePrev)));
                    if (count($partsPrev) >= 5) {
                        $p4 = implode('.', array_slice($partsPrev,0,4));
                        $nivel4PrevTotals[$p4] = ($nivel4PrevTotals[$p4] ?? 0) + (float)($lPrev['saldo_anterior'] ?? 0);
                    }
                }
            }
            $lastPrefix4 = null;
            foreach ($this->linhas as $idx => $l) {
                $nome = $l['conta'] ?? '';
                $classificacao = $l['codigo'] ?? '';
                $code = trim((string)$classificacao);
                $parts = $code !== '' ? array_values(array_filter(explode('.', $code))) : [];
                $prefix4 = count($parts) >= 5 ? implode('.', array_slice($parts, 0, 4)) : null;
                if($prefix4 && $prefix4 !== $lastPrefix4){
                    $lastPrefix4 = $prefix4; $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0];
                    $prev4 = $nivel4PrevTotals[$prefix4] ?? 0;
                    if ($this->showTrail) {
                        $partsPrefix = explode('.', $prefix4);
                        $p1 = $partsPrefix[0] ?? null;
                        $p2 = count($partsPrefix)>=2 ? implode('.', array_slice($partsPrefix,0,2)) : null;
                        $p3 = count($partsPrefix)>=3 ? implode('.', array_slice($partsPrefix,0,3)) : null;
                        $trail = [];
                        if(!empty($p1) && !empty(($this->grau1Labels[$p1] ?? null))) $trail[] = ($this->grau1Labels[$p1] ?? '') . " ($p1)";
                        if(!empty($p2) && !empty(($this->grau2Labels[$p2] ?? null))) $trail[] = ($this->grau2Labels[$p2] ?? '') . " ($p2)";
                        if(!empty($p3) && !empty(($this->grau3Labels[$p3] ?? null))) $trail[] = ($this->grau3Labels[$p3] ?? '') . " ($p3)";
                        if(!empty($trail)){
                            $rows[] = [ implode(' • ', $trail), '', '', '', '' ];
                        }
                    }
                    if ($this->showPrev && abs($prev4) > 0) {
                        $rows[] = [
                            'Saldo Anterior', $prefix4,
                            '', '',
                            number_format((float)$prev4, 2, ',', '.'),
                        ];
                        $this->saldoAnteriorRowIndexes[] = count($rows);
                    }
                    $rows[] = [
                        'Subtotal Grau 4', $prefix4,
                        number_format((float)$tot4['deb'], 2, ',', '.'),
                        number_format((float)$tot4['cred'], 2, ',', '.'),
                        number_format((float)$tot4['saldo'], 2, ',', '.'),
                    ];
                }
                $rows[] = [
                    $nome,
                    $classificacao,
                    number_format((float)$l['debito'], 2, ',', '.'),
                    number_format((float)$l['credito'], 2, ',', '.'),
                    number_format((float)$l['saldo'], 2, ',', '.'),
                ];
                $nextPrefix4 = null;
                if(isset($this->linhas[$idx+1])){
                    $n = $this->linhas[$idx+1];
                    $nCode = trim((string)($n['codigo'] ?? ''));
                    $nParts = $nCode !== '' ? array_values(array_filter(explode('.', $nCode))) : [];
                    $nextPrefix4 = count($nParts) >= 5 ? implode('.', array_slice($nParts, 0, 4)) : null;
                }
                if($prefix4 && $prefix4 !== $nextPrefix4){
                    $tot4 = $nivel4Totals[$prefix4] ?? ['deb'=>0,'cred'=>0,'saldo'=>0];
                    $rows[] = [
                        'Total Grau 4', $prefix4,
                        number_format((float)$tot4['deb'], 2, ',', '.'),
                        number_format((float)$tot4['cred'], 2, ',', '.'),
                        number_format((float)$tot4['saldo'], 2, ',', '.'),
                    ];
                }
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
            $this->dreResultadoRowIndex = count($rows);
        }
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Aplica estilo sutil (itálico, fonte 9) às linhas marcadas como "Saldo Anterior" (graus 1..4)
        foreach ($this->saldoAnteriorRowIndexes as $row) {
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setItalic(true)->setSize(9)->getColor()->setARGB('FF666666');
        }
        // Estilo para grau 5: fundo azul claro com texto preto, negrito e itálico
        foreach ($this->saldoAnteriorGrau5RowIndexes as $row) {
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true)->setItalic(true)->setSize(11)->getColor()->setARGB('FF000000');
            $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE6F2FF');
        }
        // Estilo da linha Resultado (DRE): fundo azul se positivo, vermelho se negativo; texto preto e negrito
        if ($this->dreResultadoRowIndex) {
            // A coluna E sempre contém o valor formatado; precisamos inferir sinal pelo texto, então por simplicidade aplicamos vermelho quando contiver '-'
            $cell = 'E'.$this->dreResultadoRowIndex;
            $val = $sheet->getCell($cell)->getValue();
            $isNeg = is_string($val) && strpos((string)$val, '-') !== false;
            $bgColor = $isNeg ? 'FFFFE6E6' : 'FFE6F2FF';
            $sheet->getStyle("A{$this->dreResultadoRowIndex}:E{$this->dreResultadoRowIndex}")->getFont()->setBold(true)->getColor()->setARGB('FF000000');
            $sheet->getStyle("A{$this->dreResultadoRowIndex}:E{$this->dreResultadoRowIndex}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB($bgColor);
        }
        return [];
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
