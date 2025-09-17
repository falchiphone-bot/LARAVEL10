<?php
// Smoke test: limpar CPF do primeiro representante, conferir persistência e reverter

// Executa a partir da raiz do projeto no container (/var/www)
require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Representantes;

$m = Representantes::query()->first();
if (!$m) {
    echo "NO_RECORDS\n";
    exit(0);
}

$id = $m->id;
$origRaw = $m->getRawOriginal('cpf');
$origApp = $m->cpf; // via accessor ('' -> null)

$m->cpf = '';
$m->save();

$after = Representantes::find($id);
$afterRaw = $after->getRawOriginal('cpf');
$afterApp = $after->cpf;

echo "ID=$id BEFORE_RAW=" . var_export($origRaw, true)
    . " BEFORE_APP=" . var_export($origApp, true)
    . " AFTER_RAW=" . var_export($afterRaw, true)
    . " AFTER_APP=" . var_export($afterApp, true)
    . "\n";

// Reverter
$after->cpf = $origApp; // se era null, mutator grava ''
$after->save();
echo "REVERTED=1\n";
