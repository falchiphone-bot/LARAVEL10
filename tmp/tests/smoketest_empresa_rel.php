<?php
chdir(__DIR__ . '/..');
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Representantes;

$r = Representantes::with('MostraEmpresa')->first();
if (!$r) {
    echo "NO_RECORDS\n";
    exit(0);
}
echo 'RepID=' . $r->id . ' EmpresaID=' . $r->EmpresaID . ' Empresa=' . ($r->MostraEmpresa->Descricao ?? 'NULL') . "\n";
