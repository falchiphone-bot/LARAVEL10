<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateUserHoldings extends Command
{
    protected $signature = 'holdings:truncate {--force : Executa sem pedir confirmação}';
    protected $description = 'Trunca completamente a tabela user_holdings (cuidado: remove inclusive soft deletes)';

    public function handle(): int
    {
        $conn = DB::connection();
        $driver = $conn->getDriverName();

        if(!Schema::hasTable('user_holdings')){
            $this->error('Tabela user_holdings não existe.');
            return self::FAILURE;
        }

        if(!$this->option('force')){
            if(!$this->confirm('Isso irá APAGAR TODOS os registros de user_holdings. Continuar?')){
                $this->info('Abortado.');
                return self::SUCCESS;
            }
        }

        $this->line('Truncando tabela user_holdings (driver: '.$driver.')...');
        try {
            switch($driver){
                case 'mysql':
                case 'pgsql':
                    DB::statement('TRUNCATE TABLE user_holdings RESTART IDENTITY CASCADE');
                    break;
                case 'sqlsrv':
                    DB::statement('TRUNCATE TABLE [user_holdings]');
                    break;
                case 'sqlite':
                    DB::statement('DELETE FROM user_holdings');
                    DB::statement("DELETE FROM sqlite_sequence WHERE name='user_holdings'");
                    break;
                default:
                    DB::table('user_holdings')->delete();
            }
            $this->info('Concluído.');
            return self::SUCCESS;
        } catch(\Throwable $e){
            $this->error('Falhou: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
