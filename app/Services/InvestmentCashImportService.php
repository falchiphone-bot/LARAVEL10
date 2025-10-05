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

    private function brToFloat(string $s): float
    { return (float) str_replace([',','.'], ['.',''], preg_replace('/\./','',$s)); }

    private function parseDate(string $d): ?string
    { try { return (new DateTimeImmutable(str_replace('/','-',$d)))->format('Y-m-d'); } catch(\Throwable $e){ return null; } }

    private function inferCategory(string $title): string
    {
        $t = mb_strtolower($title);
        return match(true){
            str_contains($t,'dividend') => 'dividend',
            str_contains($t,'imposto') => 'tax',
            str_contains($t,'adicionar') => 'add',
            str_contains($t,'retirar') => 'withdraw',
            default => 'other'
        };
    }
}
