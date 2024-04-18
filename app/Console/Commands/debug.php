<?php

namespace App\Console\Commands;

use App\Models\Ixc\VdContrato;
use Illuminate\Console\Command;

class debug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $planos = VdContrato::where('nome','like','%app%')->get();
        $soma = 0;
        foreach ($planos as $plano) {
            $this->info($plano->nome);

            foreach ($plano->contratos as $contrato) {
                //$this->info($contrato->client->razao);
            }

            $this->info($plano->contratos()->count());
            $this->info('');
            $soma += $plano->contratos()->count();
        }
        $this->error('Total: '.$soma);
    }
}
