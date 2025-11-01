<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PositionSizerController extends Controller
{
    /**
     * Constrói a planilha de Position Sizer (mesmo conteúdo do script CLI).
     */
    protected function buildSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Position Sizer');

        // Título
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', 'UNH — Mini‑Gestor de Posição');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Entradas
        $labels = [
            'A2' => 'Equity da conta ($)',
            'A3' => 'Risco por trade (%)',
            'A4' => 'Preço de entrada',
            'A5' => 'Preço de stop',
            'A6' => 'Slippage por ação ($)',
            'A7' => 'Taxas por ação ($)',
            'A8' => 'Custos fixos por trade ($)',
        ];
        foreach ($labels as $cell => $text) {
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }
        $defaults = [
            'B2' => 50000.00,
            'B3' => 0.01,
            'B4' => 371.00,
            'B5' => 368.80,
            'B6' => 0.05,
            'B7' => 0.00,
            'B8' => 0.00,
        ];
        foreach ($defaults as $cell => $val) { $sheet->setCellValue($cell, $val); }
        foreach (['B2','B3','B4','B5','B6','B7','B8'] as $addr) {
            $sheet->getStyle($addr)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
        }

        // Calculados
        $calcs = [
            'A9'  => 'Risco por ação ($)',
            'A10' => 'Risco permitido ($)',
            'A11' => 'Risco permitido (aj. custos fixos) ($)',
            'A12' => 'Tamanho da posição (ações)',
            'A13' => 'Exposição nominal ($)',
            'A14' => 'Direção',
            'A15' => 'Preço alvo 1R',
            'A16' => 'Preço alvo 2R',
            'A17' => 'Variação 2R vs 1R',
            'A18' => 'Perda máx. se stopar ($)',
            'A19' => 'Preço de break‑even (incl. custos fixos)',
        ];
        foreach ($calcs as $cell => $text) { $sheet->setCellValue($cell, $text); }
        $sheet->setCellValue('B9',  '=ABS(B4-B5)+B6+B7');
        $sheet->setCellValue('B10', '=B2*B3');
        $sheet->setCellValue('B11', '=MAX(B10-B8,0)');
        $sheet->setCellValue('B12', '=IF(B9>0,INT(B11/B9),0)');
        $sheet->setCellValue('B13', '=B12*B4');
        $sheet->setCellValue('B14', '=IF(B4>B5,"Long","Short")');
        $sheet->setCellValue('B15', '=IF(B14="Long",B4+B9,B4-B9)');
        $sheet->setCellValue('B16', '=IF(B14="Long",B4+2*B9,B4-2*B9)');
    // Variação 2R vs 1R — separar valor (B17) e percentual (C17)
    $sheet->setCellValue('B17', '=B16-B15');
    $sheet->setCellValue('C17', '=IF(B15>0,(B16-B15)/B15,"")');
    $sheet->setCellValue('B18', '=B12*B9+B8');
    $sheet->setCellValue('B19', '=IF(B12>0,IF(B14="Long",B4+B8/B12,B4-B8/B12),"")');
    // Variações do break-even em relação a 1R e 2R
    $sheet->setCellValue('A20', 'Variação break-even vs 1R');
    $sheet->setCellValue('B20', '=B15-B19');
    $sheet->setCellValue('C20', '=IF(B19>0,(B15-B19)/B19,"")');
    $sheet->setCellValue('A21', 'Variação break-even vs 2R');
    $sheet->setCellValue('B21', '=B16-B19');
    $sheet->setCellValue('C21', '=IF(B19>0,(B16-B19)/B19,"")');
    // Destaque visual: rótulo (A17) e valores
    $sheet->getStyle('A17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
    // Valor (azul claro) e percentual (amarelo claro)
    $sheet->getStyle('B17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
    $sheet->getStyle('C17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF3CD');

        // Formatação
        foreach (['B2','B4','B5','B6','B7','B8','B9','B10','B11','B13','B15','B16','B17','B18','B19','B20','B21'] as $addr) {
            $sheet->getStyle($addr)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        }
        // Percentuais (formato + destaque visual)
        foreach (['C17','C20','C21'] as $pcell) {
            $sheet->getStyle($pcell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle($pcell)->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FF0D47A1');
        }
        $sheet->getStyle('B3')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
        $sheet->getStyle('B12')->getNumberFormat()->setFormatCode('0');
        $sheet->getColumnDimension('A')->setWidth(36);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A9')->getFont()->setBold(true);

        // Destaque visual: rótulos e células das novas variações
        $sheet->getStyle('A20')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
        $sheet->getStyle('B20')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
        $sheet->getStyle('C20')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF3CD');
        $sheet->getStyle('A21')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
        $sheet->getStyle('B21')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
        $sheet->getStyle('C21')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF3CD');

    // Notas (deslocadas para não colidir)
    $sheet->mergeCells('A23:C23');
    $sheet->setCellValue('A23', 'Como usar: edite as células AZUIS (B2:B8). O restante é calculado automaticamente.');
    $sheet->getStyle('A23')->getFont()->setItalic(true)->setSize(10);
    $sheet->mergeCells('A24:C27');
    $sheet->setCellValue('A24', "Dicas:\n".
            "• Ajuste o risco (%) ao seu perfil (comum: 0,5%–1,0%).\n".
            "• O tamanho considera custos por ação e slippage.\n".
            "• Custos fixos reduzem o risco disponível antes do cálculo do tamanho.\n".
            "• Alvos 1R/2R usam o R por ação (entrada↔stop + custos por ação)."
        );
    $sheet->getStyle('A24')->getAlignment()->setWrapText(true);
    $sheet->getStyle('A2:C21')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_HAIR);

        return $spreadsheet;
    }

    /**
     * Baixa o XLSX como attachment (ou inline se o agente suportar).
     */
    public function download(Request $request)
    {
        $spreadsheet = $this->buildSpreadsheet();
        $filename = date('Ymd_His').'_Position_Sizer.xlsx';
        $writer = new Xlsx($spreadsheet);

        // Para inline, permitir ?disposition=inline (alguns navegadores ainda forçarão download)
        $disposition = $request->query('disposition', 'attachment');
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ];
        return response()->stream(function() use ($writer) {
            $writer->save('php://output');
        }, 200, $headers);
    }

    /**
     * Renderização em HTML para ver na web (sem download).
     */
    public function preview(Request $request)
    {
        $spreadsheet = $this->buildSpreadsheet();
        $htmlWriter = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
        // Ajustes simples de estilo via CSS embutido
        $htmlWriter->setUseInlineCss(true);
        $content = $htmlWriter->generateHtmlAll();
        return response($content, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Calculadora interativa (view) usando a função PHP positionSizer().
     */
    public function calculator(Request $request)
    {
        $input = [
            'equity'    => (float) $request->query('equity', 50000),
            'riskPct'   => (float) $request->query('riskPct', 0.01),
            'entry'     => (float) $request->query('entry', 371.00),
            'stop'      => (float) $request->query('stop', 368.80),
            'slippage'  => (float) $request->query('slippage', 0.05),
            'feeShare'  => (float) $request->query('feeShare', 0.00),
            'fixed'     => (float) $request->query('fixed', 0.00),
        ];

        // Usa helper global positionSizer(), com fallback para carregar helpers.php se necessário
        if (!\function_exists('positionSizer')) {
            // Tenta carregar helper explicitamente (ambiente pode estar sem autoload files atualizado)
            $helpersPath = \function_exists('app_path') ? app_path('helpers.php') : __DIR__.'/../../helpers.php';
            if (is_readable($helpersPath)) {
                require_once $helpersPath;
            }
        }
        $result = \function_exists('positionSizer') ? \positionSizer($input) : $this->fallbackPositionSizer($input);

        return view('tools.position_sizer_calculator', [
            'input' => $input,
            'result' => $result,
        ]);
    }

    /**
     * Fallback local caso o helper global não esteja disponível por algum motivo.
     */
    protected function fallbackPositionSizer(array $in): array
    {
        $equity   = (float)($in['equity']   ?? 50000);
        $riskPct  = (float)($in['riskPct']  ?? 0.01);
        $entry    = (float)($in['entry']    ?? 371.00);
        $stop     = (float)($in['stop']     ?? 368.80);
        $slip     = (float)($in['slippage'] ?? 0.05);
        $feeShare = (float)($in['feeShare'] ?? 0.00);
        $fixed    = (float)($in['fixed']    ?? 0.00);

        $rPerShare = abs($entry - $stop) + $slip + $feeShare;
        $riskAllowed = $equity * $riskPct;
        $riskAdj = max($riskAllowed - $fixed, 0);
        $size = $rPerShare > 0 ? (int) floor($riskAdj / $rPerShare) : 0;
        $notional = $size * $entry;
        $dir = $entry > $stop ? 'Long' : 'Short';
        $t1 = $dir === 'Long' ? $entry + $rPerShare : $entry - $rPerShare;
        $t2 = $dir === 'Long' ? $entry + 2*$rPerShare : $entry - 2*$rPerShare;
        $maxLoss = $size * $rPerShare + $fixed;
        $breakeven = $size > 0
            ? ($dir === 'Long' ? $entry + $fixed/$size : $entry - $fixed/$size)
            : null;

        return compact('rPerShare','riskAllowed','riskAdj','size','notional','dir','t1','t2','maxLoss','breakeven');
    }
}
