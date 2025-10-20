<?php

use App\Models\AssetDailyStat;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

class FakeMarketDataService {
    public function getQuote(string $symbol): array
    {
        // Retorna sempre um preço fixo para previsibilidade
        return [
            'symbol' => strtoupper($symbol),
            'price' => 227.29,
            'currency' => 'USD',
            'updated_at' => '2025-10-20 16:32:00',
            'source' => 'yahoo_rapidapi',
        ];
    }
}

it('persists quote and returns stored value for the day', function(){
    // Congela a data para garantir startOfDay consistente
    Carbon::setTestNow(Carbon::parse('2025-10-20 16:35:00'));

    // Usuário autenticado (API exige auth)
    $user = \App\Models\User::factory()->create();
    $this->be($user);

    // Injeta fake do MarketDataService
    $this->app->bind(\App\Services\MarketDataService::class, function(){
        return new FakeMarketDataService();
    });

    // Chama a API com persist=1
    $resp = $this->getJson(route('api.market.quote', ['symbol' => 'AMAT', 'persist' => 1]));
    $resp->assertOk();
    $json = $resp->json();

    // A resposta deve refletir o valor armazenado (mesmo número do fake)
    expect($json['symbol'] ?? null)->toBe('AMAT');
    expect($json['price'] ?? null)->toBe(227.29);

    // Verifica que persistiu em AssetDailyStat para a data de hoje (startOfDay)
    $stat = AssetDailyStat::where('symbol','AMAT')
        ->whereDate('date', Carbon::now()->format('Y-m-d'))
        ->first();
    expect($stat)->not->toBeNull();
    $this->assertEqualsWithDelta(227.29, (float) $stat->close_value, 0.000001);

    // Segunda chamada não deve criar duplicado para o mesmo dia
    $this->getJson(route('api.market.quote', ['symbol' => 'AMAT', 'persist' => 1]))->assertOk();
    $count = AssetDailyStat::where('symbol','AMAT')->whereDate('date', Carbon::now()->format('Y-m-d'))->count();
    expect($count)->toBe(1);
});
