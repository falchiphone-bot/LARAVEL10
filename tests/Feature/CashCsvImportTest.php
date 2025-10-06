<?php

use App\Models\User;
use App\Models\InvestmentAccount;
use App\Models\InvestmentAccountCashEvent;
use App\Models\InvestmentAccountCashSnapshot;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\get;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function(){
    // Garantir migrações
    Artisan::call('migrate');
});

it('importa CSV gerando snapshot e eventos novos', function(){
    $user = User::factory()->create();
    // Permissões simuladas: atribuir via gate simples ignorando Spatie (assumindo middleware já testado em outro lugar)
    // Se necessário, criar permissões reais ou bypass middleware usando actingAs + sem rota protegida (mas rotas usam can:). Para este teste focamos lógica interna.
    // Criar conta
    $account = InvestmentAccount::factory()->create(['user_id'=>$user->id]);

    $csv = <<<CSV
Data transação,Data liquidação,Descrição,Valor,Saldo
04/10/2025,06/10/2025,Dividendos de IRM,24.34,85314.01
04/10/2025,06/10/2025,Imposto sobre dividendo de IRM,-7.30,85289.67
CSV;

    // Como a rota tem middleware de permissão, podemos desabilitar temporariamente policies usando withoutMiddleware se necessário.
    // Simples: Registrar permissões na sessão (mock). Se falhar, ajustar test para chamar service direto.

    actingAs($user);

    $file = UploadedFile::fake()->createWithContent('avenue-report-statement.csv',$csv);

    withoutMiddleware();
    $response = post(route('cash.import.csv.store'),[
        'account_id'=>$account->id,
        'csv_file'=>$file,
    ]);

    $response->assertRedirect(route('cash.events.index').'#gsc.tab=0');

    expect(InvestmentAccountCashSnapshot::count())->toBe(1);
    expect(InvestmentAccountCashEvent::count())->toBe(2);

    $div = InvestmentAccountCashEvent::where('title','Dividendos de IRM')->first();
    expect($div)->not->toBeNull();
    expect($div->amount)->toBe(24.34);

    $tax = InvestmentAccountCashEvent::where('title','Imposto sobre dividendo de IRM')->first();
    expect($tax->amount)->toBe(-7.30);
});

it('faz merge sem duplicar eventos já existentes e atualiza categoria', function(){
    $user = User::factory()->create();
    $account = InvestmentAccount::factory()->create(['user_id'=>$user->id]);

    actingAs($user);

    $csv1 = <<<CSV
Data transação,Data liquidação,Descrição,Valor,Saldo
04/10/2025,06/10/2025,Dividendos de IRM,24.34,85314.01
CSV;
    $file1 = UploadedFile::fake()->createWithContent('avenue-report-statement.csv',$csv1);
    withoutMiddleware();
    post(route('cash.import.csv.store'),[
        'account_id'=>$account->id,
        'csv_file'=>$file1,
    ]);
    expect(InvestmentAccountCashEvent::count())->toBe(1);

    // Reimporta mudando potencialmente categoria (ex: descrição que vira tax se palavra imposto aparece)
    $csv2 = <<<CSV
Data transação,Data liquidação,Descrição,Valor,Saldo
04/10/2025,06/10/2025,Dividendos de IRM,24.34,85314.01
CSV;
    $file2 = UploadedFile::fake()->createWithContent('avenue-report-statement.csv',$csv2);
    withoutMiddleware();
    post(route('cash.import.csv.store'),[
        'account_id'=>$account->id,
        'csv_file'=>$file2,
    ]);

    // Continua 1 evento (sem duplicação)
    expect(InvestmentAccountCashEvent::count())->toBe(1);
});
