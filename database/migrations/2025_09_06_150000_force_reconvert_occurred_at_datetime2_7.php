<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if(DB::getDriverName() !== 'sqlsrv') return;
        if(!Schema::hasTable('openai_chat_records')) return;

        // Obter metadados atuais
        $info = DB::selectOne("SELECT DATA_TYPE, IS_NULLABLE, DATETIME_PRECISION FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='openai_chat_records' AND COLUMN_NAME='occurred_at'");
        if(!$info) return;
        $dataType = strtolower($info->DATA_TYPE ?? '');
        $precision = (int)($info->DATETIME_PRECISION ?? 0);
        $nullable = strtoupper($info->IS_NULLABLE ?? 'NO') === 'YES';

        // Se já é datetime/datetime2/smalldatetime/date apenas ajustar para datetime2(7) e sair
        if(in_array($dataType, ['datetime','datetime2','smalldatetime','date'])){
            // Ajustar apenas se precisão diferente de 7
            if($dataType !== 'datetime2' || $precision !== 7){
                try { DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2(7) ".($nullable? 'NULL':'NOT NULL')); } catch(Throwable $e){}
            }
            return; // nada mais a fazer
        }

        // Caso contrário (coluna textual) criar coluna temporária para conversão
        if(!Schema::hasColumn('openai_chat_records','occurred_at_tmp')){
            try {
                Schema::table('openai_chat_records', function(Blueprint $table){
                    $table->dateTime('occurred_at_tmp', 7)->nullable();
                });
            } catch (Throwable $e) {
                try { DB::statement("ALTER TABLE openai_chat_records ADD occurred_at_tmp datetime2(7) NULL"); } catch(Throwable $e2) {}
            }
        }
        if(!Schema::hasColumn('openai_chat_records','occurred_at_tmp')){
            return; // falha em criar temp, aborta
        }

        // Tentar converter strings em formatos conhecidos: yyyy-mm-dd HH:MM:SS(.fff), dd/mm/yyyy HH:MM:SS, dd/mm/yyyy
        // 1ª tentativa: estilo ISO 126 direto
    DB::statement("UPDATE openai_chat_records SET occurred_at_tmp = TRY_CONVERT(datetime2(7), occurred_at, 126) WHERE occurred_at_tmp IS NULL");
        // 2ª tentativa: brasileiro completo data hora
    DB::statement("UPDATE openai_chat_records SET occurred_at_tmp = TRY_CONVERT(datetime2(7), occurred_at, 103) WHERE occurred_at_tmp IS NULL");
        // 3ª: se só data dd/mm/yyyy sem hora => adicionar 00:00:00
    DB::statement("UPDATE openai_chat_records SET occurred_at_tmp = TRY_CONVERT(datetime2(7), occurred_at + ' 00:00:00', 103) WHERE occurred_at_tmp IS NULL AND occurred_at LIKE '[0-3][0-9]/[0-1][0-9]/20%'");

        // Tratar casos ambíguos dia/mes (ambos <=12) ainda nulos: heurística inverter
    $rows = DB::select("SELECT id, occurred_at FROM openai_chat_records WHERE occurred_at_tmp IS NULL AND occurred_at LIKE '[0-3][0-9]/[0-1][0-9]/20%'");
        foreach($rows as $r){
            $parts = explode(' ', $r->occurred_at);
            $date = $parts[0];
            $time = $parts[1] ?? '00:00:00';
            $dmy = explode('/', $date);
            if(count($dmy)==3){
                [$d,$m,$y]=$dmy; if((int)$d<=12 && (int)$m<=12 && $d!==$m){
                    $swapped = $m.'/'.$d.'/'.$y.' '.$time;
                    $val = DB::selectOne("SELECT TRY_CONVERT(datetime2(7), ?) AS v", [$swapped]);
                    if($val && $val->v){
                        DB::update("UPDATE openai_chat_records SET occurred_at_tmp = TRY_CONVERT(datetime2(7), ?, 103) WHERE id=?", [$swapped, $r->id]);
                    }
                }
            }
        }

        // Substituir coluna original se havia texto
    $col = DB::selectOne("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='openai_chat_records' AND COLUMN_NAME='occurred_at'");
    if($col && !in_array(strtolower($col->DATA_TYPE), ['datetime','datetime2','smalldatetime','date'])){
            // remover constraint de índice se necessário (tentativa silenciosa)
            try { DB::statement("DROP INDEX idx_openai_chat_records_occurred_at ON openai_chat_records"); } catch(Throwable $e) {}
            // dropar original
            Schema::table('openai_chat_records', function(Blueprint $table){
                $table->dropColumn('occurred_at');
            });
            // criar correta
            Schema::table('openai_chat_records', function(Blueprint $table){
                $table->dateTime('occurred_at', 7)->nullable();
            });
        } else {
            // Apenas alterar tipo para datetime2(7) se já era datetime/datetime2 com menor precisão
            try { DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2(7) NULL"); } catch(Throwable $e) {}
        }

        // Copiar valores
        if(Schema::hasColumn('openai_chat_records','occurred_at_tmp')){
            DB::statement("UPDATE openai_chat_records SET occurred_at = occurred_at_tmp WHERE occurred_at_tmp IS NOT NULL");
        }

        // Normalizar NULL restantes
        DB::statement("UPDATE openai_chat_records SET occurred_at = '2000-01-01T00:00:00' WHERE occurred_at IS NULL");

        // Tornar NOT NULL
        try { DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2(7) NOT NULL"); } catch(Throwable $e) {}

        // Remover temporária
        if(Schema::hasColumn('openai_chat_records','occurred_at_tmp')){
            Schema::table('openai_chat_records', function(Blueprint $table){
                $table->dropColumn('occurred_at_tmp');
            });
        }
    }

    public function down(): void
    {
        // Sem reversão completa; opcional poderia reduzir precisão.
    }
};
