<?php

use App\Models\MoedasValores;
use App\Services\MoedaVariacaoService;
use Illuminate\Support\Collection;
use Carbon\Carbon;

test('calcula variacao posterior corretamente', function () {
    $service = new MoedaVariacaoService();

    $a = new MoedasValores(['valor' => 10, 'data' => Carbon::parse('2024-01-01')]);
    $b = new MoedasValores(['valor' => 15, 'data' => Carbon::parse('2024-01-02')]);
    $c = new MoedasValores(['valor' => 12, 'data' => Carbon::parse('2024-01-03')]);

    $col = new Collection([$b, $c, $a]); // fora de ordem de propósito
    $service->atribuir($col, 'posterior');

    // a -> b : (15-10)/10 = 50%
    expect(round($a->variacao_percentual, 2))->toBeFloat()->toBe(50.00);
    // b -> c : (12-15)/15 = -20%
    expect(round($b->variacao_percentual, 2))->toBeFloat()->toBe(-20.00);
    // c sem posterior
    expect($c->variacao_percentual)->toBeNull();
});

test('calcula variacao anterior corretamente', function () {
    $service = new MoedaVariacaoService();

    $a = new MoedasValores(['valor' => 10, 'data' => Carbon::parse('2024-01-01')]);
    $b = new MoedasValores(['valor' => 15, 'data' => Carbon::parse('2024-01-02')]);
    $c = new MoedasValores(['valor' => 12, 'data' => Carbon::parse('2024-01-03')]);

    $col = new Collection([$c, $b, $a]);
    $service->atribuir($col, 'anterior');

    // a sem anterior
    expect($a->variacao_percentual)->toBeNull();
    // b sobre a: (15-10)/10 = 50%
    expect(round($b->variacao_percentual, 2))->toBeFloat()->toBe(50.00);
    // c sobre b: (12-15)/15 = -20%
    expect(round($c->variacao_percentual, 2))->toBeFloat()->toBe(-20.00);
});

test('evita divisao por zero', function () {
    $service = new MoedaVariacaoService();

    $a = new MoedasValores(['valor' => 0, 'data' => Carbon::parse('2024-01-01')]);
    $b = new MoedasValores(['valor' => 15, 'data' => Carbon::parse('2024-01-02')]);

    $col = new Collection([$a, $b]);
    $service->atribuir($col, 'posterior');

    expect($a->variacao_percentual)->toBeNull();
});
