<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = ['Representantes','representantes'];
        $columns = ['cpf','cnpj'];

        foreach ($tables as $tbl) {
            if (!Schema::hasTable($tbl)) { continue; }

            foreach ($columns as $col) {
                if (!Schema::hasColumn($tbl, $col)) { continue; }

                // Obter o tipo atual da coluna para manter tamanho/tipo ao alterar NULL/NOT NULL
                $info = DB::selectOne(
                    "SELECT DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS len, NUMERIC_PRECISION AS prec, NUMERIC_SCALE AS scale
                     FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?",
                    [$tbl, $col]
                );

                if (!$info) { continue; }

                $type = strtolower($info->type ?? 'varchar');
                $len = $info->len ?? null;
                $prec = $info->prec ?? null;
                $scale = $info->scale ?? null;

                // Montar a parte do tipo (varchar(len), nvarchar(len), etc.)
                $typeDef = $type;
                if (in_array($type, ['varchar','nvarchar','char','nchar'])) {
                    if ($len === null) { $len = 255; }
                    $lenStr = ((int)$len === -1) ? 'MAX' : (string)(int)$len;
                    $typeDef = sprintf('%s(%s)', $type, $lenStr);
                } elseif (in_array($type, ['decimal','numeric'])) {
                    $p = $prec !== null ? (int)$prec : 18;
                    $s = $scale !== null ? (int)$scale : 0;
                    $typeDef = sprintf('%s(%d,%d)', $type, $p, $s);
                } elseif (in_array($type, ['int','bigint','smallint','tinyint','bit','datetime','datetime2','date','time'])) {
                    // tipos sem tamanho
                    $typeDef = $type;
                } else {
                    // fallback
                    $typeDef = 'varchar(255)';
                }

                // Tornar a coluna NULL
                $sql = sprintf('ALTER TABLE %s ALTER COLUMN %s %s NULL', $tbl, $col, $typeDef);
                try { DB::statement($sql); } catch (\Throwable $e) { /* ignora para a outra variação */ }
            }
        }
    }

    public function down(): void
    {
        $tables = ['Representantes','representantes'];
        $columns = ['cpf','cnpj'];

        foreach ($tables as $tbl) {
            if (!Schema::hasTable($tbl)) { continue; }

            foreach ($columns as $col) {
                if (!Schema::hasColumn($tbl, $col)) { continue; }

                $info = DB::selectOne(
                    "SELECT DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS len, NUMERIC_PRECISION AS prec, NUMERIC_SCALE AS scale
                     FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?",
                    [$tbl, $col]
                );
                if (!$info) { continue; }

                $type = strtolower($info->type ?? 'varchar');
                $len = $info->len ?? null;
                $prec = $info->prec ?? null;
                $scale = $info->scale ?? null;

                $typeDef = $type;
                if (in_array($type, ['varchar','nvarchar','char','nchar'])) {
                    if ($len === null) { $len = 255; }
                    $lenStr = ((int)$len === -1) ? 'MAX' : (string)(int)$len;
                    $typeDef = sprintf('%s(%s)', $type, $lenStr);
                } elseif (in_array($type, ['decimal','numeric'])) {
                    $p = $prec !== null ? (int)$prec : 18;
                    $s = $scale !== null ? (int)$scale : 0;
                    $typeDef = sprintf('%s(%d,%d)', $type, $p, $s);
                } elseif (in_array($type, ['int','bigint','smallint','tinyint','bit','datetime','datetime2','date','time'])) {
                    $typeDef = $type;
                } else {
                    $typeDef = 'varchar(255)';
                }

                // Antes de forçar NOT NULL, substitui NULL por string vazia para não falhar
                try { DB::statement(sprintf("UPDATE %s SET %s = '' WHERE %s IS NULL", $tbl, $col, $col)); } catch (\Throwable $e) {}

                $sql = sprintf('ALTER TABLE %s ALTER COLUMN %s %s NOT NULL', $tbl, $col, $typeDef);
                try { DB::statement($sql); } catch (\Throwable $e) { /* ignore */ }
            }
        }
    }
};
