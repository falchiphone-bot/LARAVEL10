<?php
namespace App\Services;

use Illuminate\Support\Str;
use DateTimeImmutable;

class InvestmentCashImportService
{
    public function parse(string $raw): array
    {
        $lines = preg_split('/\r?\n/', $raw);
        $clean = [];
        foreach($lines as $l){
            $t = trim($l); if($t==='') continue; $clean[] = $t; }
        $snapshot = null; $events=[]; $errors=[];
        // Detect snapshot: valor principal linha que começa com $ ou + $ etc.
        for($i=0;$i<count($clean);$i++){
            $line = $clean[$i];
            if($snapshot===null && preg_match('/^\$\s?([0-9\.]+,[0-9]{2})$/u',$line,$m)){
                $available = $this->brToFloat($m[1]);
                $futureAmount=null; $futureDate=null;
                if(isset($clean[$i+1]) && preg_match('/^[+]?\s?\$\s?([0-9\.]+,[0-9]{2})\s+em\s+([0-9]{2}\/[0-9]{2}\/[0-9]{4})$/u',$clean[$i+1],$m2)){
                    $futureAmount = $this->brToFloat($m2[1]);
                    $futureDate = $this->parseDate($m2[2]);
                }
                $snapshot = [
                    'available_amount'=>$available,
                    'future_amount'=>$futureAmount,
                    'future_date'=>$futureDate,
                ];
            }
        }
        // Events: blocos com data,data,título,[detalhe],valor,status
        for($i=0;$i<count($clean);$i++){
            if(!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/',$clean[$i])) continue;
            $d1 = $clean[$i]; $d2 = $clean[$i+1] ?? null;
            if(!$d2 || !preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/',$d2)) continue;
            $title = $clean[$i+2] ?? null; if(!$title) continue;
            $detailIdx = $i+3; $maybeDetail = $clean[$detailIdx] ?? null;
            $valIdx=null; $statusIdx=null; $detail=null;
            // Valor possui + ou - e cifra
            if($maybeDetail && preg_match('/^[+-]\s?\$/',$maybeDetail)){
                $valIdx = $detailIdx; $statusIdx = $detailIdx+1; $detail=null;
            } else {
                $detail = $maybeDetail; $valIdx = $detailIdx+1; $statusIdx = $detailIdx+2;
            }
            $valLine = $clean[$valIdx] ?? null; $statusLine = $clean[$statusIdx] ?? null;
            if(!$valLine || !$statusLine) continue;
            if(!preg_match('/^([+-])\s?\$\s?([0-9\.]+,[0-9]{2})$/',$valLine,$vm)) continue;
            $sign = $vm[1] === '-' ? -1 : 1;
            $amount = $this->brToFloat($vm[2]) * $sign;
            $category = $this->inferCategory($title);
            $events[] = [
                'event_date'=>$this->parseDate($d1),
                'settlement_date'=>$this->parseDate($d2),
                'category'=>$category,
                'title'=>$title,
                'detail'=>$detail,
                'amount'=>$amount,
                'currency'=>'USD',
                'status'=>$statusLine,
                'source'=>'avenue_screen_cash'
            ];
            // avançar índice de leitura para evitar reprocessar
            $i = $statusIdx;
        }
        return [ 'snapshot'=>$snapshot, 'events'=>$events, 'errors'=>$errors ];
    }

    /**
     * Parse CSV export (avenue-report-statement.csv) contendo colunas:
     * Data transação,Data liquidação,Descrição,Valor,Saldo
     * Assumindo separador vírgula e valores em formato decimal ponto (24.34, -7.30).
     * Retorna estrutura compatível com a importação já existente.
     */
    public function parseAvenueStatementCsv(string $csvRaw): array
    {
        // Remover BOM UTF-8 se presente
        $csvRaw = preg_replace('/^\xEF\xBB\xBF/', '', $csvRaw);
        $lines = preg_split('/\r?\n/', trim($csvRaw));
        if(!$lines || count($lines) === 0){
            return ['snapshot'=>null,'events'=>[],'errors'=>['empty_csv']];
        }
        $headerLine = array_shift($lines);
        // Detectar delimitador (contar ; vs ,)
        $semi = substr_count($headerLine,';');
        $comma = substr_count($headerLine,',');
        $delimiter = $semi > $comma ? ';' : ',';
        $header = str_getcsv($headerLine, $delimiter);
        $map = [];
        foreach($header as $idx=>$col){
            $colNorm = $this->normalizeHeader($col);
            $map[$colNorm] = $idx;
        }
        $hasDataTrans = isset($map['data transacao']);
        $hasDescricao = isset($map['descricao']);
        $hasValor = isset($map['valor']);
        if(!$hasDataTrans){ return ['snapshot'=>null,'events'=>[],'errors'=>['missing_data_transacao']]; }
        if(!$hasDescricao){ return ['snapshot'=>null,'events'=>[],'errors'=>['missing_descricao']]; }
        if(!$hasValor){ return ['snapshot'=>null,'events'=>[],'errors'=>['missing_valor']]; }
        $idxDataTrans = $map['data transacao'];
        $idxDataLiq = $map['data liquidacao'] ?? null;
        $idxDesc = $map['descricao'];
        $idxValor = $map['valor'];
        $idxSaldo = $map['saldo'] ?? null;

        $events = []; $errors = []; $snapshot = null; $saldoPrimeiraLinha = null; $linhaNumero = 1;
        foreach($lines as $rawLine){
            $linhaNumero++;
            $trimmed = trim($rawLine);
            if($trimmed==='') continue;
            $cols = str_getcsv($rawLine, $delimiter);
            if(count($cols) <= max($idxValor, $idxDesc, $idxDataTrans)) { $errors[]='cols_insufficient_l'.$linhaNumero; continue; }
            $dataTrans = trim($cols[$idxDataTrans] ?? '');
            $dataLiq = $idxDataLiq!==null ? trim($cols[$idxDataLiq] ?? '') : '';
            $descricao = trim($cols[$idxDesc] ?? '');
            $valorRaw = trim($cols[$idxValor] ?? '');
            $saldoRaw = $idxSaldo!==null ? trim($cols[$idxSaldo] ?? '') : null;
            if($saldoPrimeiraLinha === null && $saldoRaw !== null && $saldoRaw !== ''){
                $saldoPrimeiraLinha = $this->parseDecimalPoint($saldoRaw);
            }
            if($dataTrans==='' || $descricao==='' || $valorRaw===''){ $errors[]='missing_field_l'.$linhaNumero; continue; }
            $amount = $this->parseDecimalPoint($valorRaw);
            $events[] = [
                'event_date'=>$this->parseDate($dataTrans),
                'settlement_date'=>$dataLiq ? $this->parseDate($dataLiq) : null,
                'category'=>$this->inferCategory($descricao),
                'title'=>$descricao,
                'detail'=>null,
                'amount'=>$amount,
                'currency'=>'USD',
                'status'=>null,
                'source'=>'avenue_csv'
            ];
        }
        if($saldoPrimeiraLinha !== null){
            $snapshot = [
                'available_amount'=>$saldoPrimeiraLinha,
                'future_amount'=>null,
                'future_date'=>null,
            ];
        }
        return ['snapshot'=>$snapshot,'events'=>$events,'errors'=>$errors];
    }

    private function brToFloat(string $s): float
    {
        // Remove separadores de milhar (.) e converte vírgula decimal para ponto.
        // Ex.: 22.308,07 -> 22308.07 ; 24,34 -> 24.34
        $s = str_replace('.', '', $s);      // remove todos os pontos (milhar)
        $s = str_replace(',', '.', $s);     // vírgula decimal -> ponto
        // Garantir formato numérico simples
        if(!preg_match('/^-?\d+(\.\d+)?$/', $s)) {
            return (float) filter_var($s, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }
        return (float)$s;
    }

    private function parseDate(string $d): ?string
    { try { return (new DateTimeImmutable(str_replace('/','-',$d)))->format('Y-m-d'); } catch(\Throwable $e){ return null; } }

    private function inferCategory(string $title): string
    {
        $t = mb_strtolower($title);
        return match(true){
            str_contains($t,'dividend') => 'dividend',
            str_contains($t,'imposto') => 'tax',
            str_contains($t,'taxa') || str_contains($t,'cobrança') || str_contains($t,'cobranca') => 'fee',
            str_contains($t,'adicionar') => 'add',
            str_contains($t,'retirar') => 'withdraw',
            default => 'other'
        };
    }

    private function parseDecimalPoint(string $s): float
    {
        // CSV usa ponto como separador decimal (ex.: 24.34, -7.30)
        $s = trim($s);
        // Remove thousand separators se houver (",") ou espaços
        $s = str_replace([' ','\u{00A0}',','], ['','',''], $s); // assumindo que vírgula não é decimal aqui
        if(!preg_match('/^-?\d+(\.\d+)?$/', $s)){
            // fallback tentativa
            $s = str_replace(',', '.', $s);
        }
        return (float)$s;
    }

    private function normalizeHeader(string $h): string
    {
        $h = trim(mb_strtolower($h));
        // Replace accented chars with ASCII equivalents
        $h = iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $h);
        // Remove duplicated spaces
        $h = preg_replace('/\s+/', ' ', $h);
        // Normalizações específicas
        $h = str_replace(['transação','transaçao','transacão','transa c7ao'], 'transacao', $h);
        $h = str_replace(['liquidação','liquidacao','liquidaçao'], 'liquidacao', $h);
        $h = str_replace(['descrição','descriçao','descricaõ'], 'descricao', $h);
        return $h;
    }
}
