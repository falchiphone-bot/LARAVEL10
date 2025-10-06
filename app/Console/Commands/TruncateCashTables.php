<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateCashTables extends Command
{
    protected $signature = 'cash:truncate {--force : Executa sem confirmação interativa}';
    protected $description = 'Trunca (limpa) as tabelas de eventos e snapshots de caixa: investment_account_cash_events e investment_account_cash_snapshots';

    public function handle(): int
    {
        // Ambiente de proteção: impedir em produção sem --force
        if(app()->environment('production') && !$this->option('force')){
            $this->error('Ambiente de produção detectado. Use --force para confirmar.');
            return self::FAILURE;
        }

        if(!$this->option('force')){
            if(!$this->confirm('Tem certeza que deseja TRUNCAR (apagar TODOS os registros) das tabelas de caixa?')){
                $this->warn('Operação cancelada.');
                return self::SUCCESS;
            }
        }

        $tables = [
            'investment_account_cash_events',
            'investment_account_cash_snapshots',
        ];

        DB::beginTransaction();
        try {
            $driver = DB::getDriverName();
            if($driver === 'mysql'){
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } elseif($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            } elseif($driver === 'sqlsrv') {
                // SQL Server: usar DELETE + RESEED
            }

            foreach($tables as $t){
                if($driver === 'sqlsrv'){
                    DB::statement("DELETE FROM [$t]");
                    DB::statement("DBCC CHECKIDENT ('$t', RESEED, 0)");
                } else {
                    DB::statement('TRUNCATE TABLE '.$t);
                }
            }

            if($driver === 'mysql'){
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }

            DB::commit();
            $this->info('Tabelas truncadas com sucesso: '.implode(', ', $tables));
            return self::SUCCESS;
        } catch(\Throwable $e){
            DB::rollBack();
            $this->error('Falha ao truncar: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
