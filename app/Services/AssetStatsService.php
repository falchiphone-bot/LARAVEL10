<?php

namespace App\Services;

class AssetStatsService
{
    /**
     * Calcula estatísticas básicas sobre um array de valores numéricos.
     * Retorna: avg, median, max, min, count.
     * Para array vazio retorna todos null (count=0).
     * @param array<int,float|int|string|null> $values
     * @return array{avg: float|null, median: float|null, max: float|null, min: float|null, count: int}
     */
    public function compute(array $values): array
    {
        $nums = [];
        foreach ($values as $v) {
            if ($v === null || $v === '') { continue; }
            if (is_string($v) && trim($v) === '') { continue; }
            if (!is_numeric($v)) { continue; }
            $nums[] = (float)$v;
        }
        $count = count($nums);
        if ($count === 0) {
            return [
                'avg' => null,
                'median' => null,
                'max' => null,
                'min' => null,
                'count' => 0,
            ];
        }
        sort($nums, SORT_NUMERIC);
        $sum = array_sum($nums);
        $avg = $sum / $count;
        if ($count % 2 === 1) {
            $median = $nums[intdiv($count, 2)];
        } else {
            $median = ($nums[$count/2 - 1] + $nums[$count/2]) / 2.0;
        }
        return [
            'avg' => $avg,
            'median' => $median,
            'max' => $nums[$count-1],
            'min' => $nums[0],
            'count' => $count,
        ];
    }
}
