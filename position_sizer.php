<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Position Sizer');

/** -------------------- Título -------------------- */
$sheet->mergeCells('A1:B1');
$sheet->setCellValue('A1', 'UNH — Mini‑Gestor de Posição');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

/** -------------------- Entradas (editáveis) -------------------- */
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

/* Valores padrão (altere à vontade) */
$defaults = [
    'B2' => 50000.00, // Equity
    'B3' => 0.01,     // 1% de risco
    'B4' => 371.00,   // Exemplo de entrada (pós-pullback)
    'B5' => 368.80,   // Exemplo de stop
    'B6' => 0.05,     // Slippage
    'B7' => 0.00,     // Taxa por ação
    'B8' => 0.00,     // Custo fixo
];
foreach ($defaults as $cell => $val) {
    $sheet->setCellValue($cell, $val);
}

/* Estilo de célula editável (azul claro) */
$inputCells = ['B2','B3','B4','B5','B6','B7','B8'];
foreach ($inputCells as $addr) {
    $sheet->getStyle($addr)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFD9EAF7');
}

/** -------------------- Campos calculados -------------------- */
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
foreach ($calcs as $cell => $text) {
    $sheet->setCellValue($cell, $text);
}

/* Fórmulas — mesmas da planilha anterior */
$sheet->setCellValue('B9',  '=ABS(B4-B5)+B6+B7');                    // R por ação
$sheet->setCellValue('B10', '=B2*B3');                               // Risco permitido bruto
$sheet->setCellValue('B11', '=MAX(B10-B8,0)');                       // Ajuste por custos fixos
$sheet->setCellValue('B12', '=IF(B9>0,INT(B11/B9),0)');              // Tamanho (ações)
$sheet->setCellValue('B13', '=B12*B4');                              // Exposição nominal
$sheet->setCellValue('B14', '=IF(B4>B5,"Long","Short")');            // Direção
$sheet->setCellValue('B15', '=IF(B14="Long",B4+B9,B4-B9)');          // 1R
$sheet->setCellValue('B16', '=IF(B14="Long",B4+2*B9,B4-2*B9)');      // 2R
// Variação 2R vs 1R — separar valor (B17) e percentual (C17)
$sheet->setCellValue('B17', '=B16-B15');
$sheet->setCellValue('C17', '=IF(B15>0,(B16-B15)/B15,"")');
$sheet->setCellValue('B18', '=B12*B9+B8');                           // Perda máx.
$sheet->setCellValue('B19', '=IF(B12>0,IF(B14="Long",B4+B8/B12,B4-B8/B12),"")'); // Break-even
// Variações do break-even em relação a 1R e 2R
$sheet->setCellValue('A20', 'Variação break-even vs 1R');
$sheet->setCellValue('B20', '=B15-B19');
$sheet->setCellValue('C20', '=IF(B19>0,(B15-B19)/B19,"")');
$sheet->setCellValue('A21', 'Variação break-even vs 2R');
$sheet->setCellValue('B21', '=B16-B19');
$sheet->setCellValue('C21', '=IF(B19>0,(B16-B19)/B19,"")');
// Destaque visual: rótulo (A17), valor (B17) e percentual (C17)
$sheet->getStyle('A17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
$sheet->getStyle('B17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
$sheet->getStyle('C17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF3CD');
// Destaque visual das variações de break-even
$sheet->getStyle('A20')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
$sheet->getStyle('B20')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
$sheet->getStyle('C20')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF3CD');
$sheet->getStyle('A21')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
$sheet->getStyle('B21')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
$sheet->getStyle('C21')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF3CD');

/** -------------------- Formatação numérica -------------------- */
$currencyCells = ['B2','B4','B5','B6','B7','B8','B9','B10','B11','B13','B15','B16','B17','B18','B19','B20','B21'];
foreach ($currencyCells as $addr) {
    $sheet->getStyle($addr)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
}
$sheet->getStyle('B3')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
$sheet->getStyle('B12')->getNumberFormat()->setFormatCode('0'); // inteiro

/** -------------------- Largura de colunas -------------------- */
$sheet->getColumnDimension('A')->setWidth(36);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(14);

/** -------------------- Destaques -------------------- */
$sheet->getStyle('A2')->getFont()->setBold(true);
$sheet->getStyle('A9')->getFont()->setBold(true);

/** -------------------- Notas de uso -------------------- */
$sheet->mergeCells('A23:C23');
$sheet->setCellValue('A23', 'Como usar: edite as células AZUIS (B2:B8). O restante é calculado automaticamente.');
$sheet->getStyle('A23')->getFont()->setItalic(true)->setSize(10);

$sheet->mergeCells('A24:C27');
$sheet->setCellValue('A24',
    "Dicas:\n" .
    "• Ajuste o risco (%) ao seu perfil (comum: 0,5%–1,0%).\n" .
    "• O tamanho considera custos por ação e slippage.\n" .
    "• Custos fixos reduzem o risco disponível antes do cálculo do tamanho.\n" .
    "• Alvos 1R/2R usam o R por ação (entrada↔stop + custos por ação)."
);
$sheet->getStyle('A24')->getAlignment()->setWrapText(true);

/** -------------------- Bordas leves na área principal -------------------- */
$borderRange = 'A2:C21';
$sheet->getStyle($borderRange)->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_HAIR);
// Percentuais (formato + destaque visual)
foreach (['C17','C20','C21'] as $pcell) {
    $sheet->getStyle($pcell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
    $sheet->getStyle($pcell)->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FF0D47A1');
}

/** -------------------- Salvar no disco -------------------- */
// Usa timestamp no lugar de "UNH" para identificar o momento da geração
$filename = date('Ymd_His').'_Position_Sizer.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

echo "Arquivo gerado: {$filename}\n";

/**
 * =================== Modo alternativa: enviar direto ao navegador ===================
 * Descomente a seção abaixo se for rodar como script web (ex.: http://localhost/position_sizer.php).
 */
/*
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.date('Ymd_His').'_Position_Sizer.xlsx'.'"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
*/
