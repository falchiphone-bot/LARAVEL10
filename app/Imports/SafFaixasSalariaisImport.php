<?php

namespace App\Imports;

use App\Models\SafFaixaSalarial;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class SafFaixasSalariaisImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    protected int $inserted = 0;

    public function rules(): array
    {
        return [
            'tipo_contrato' => ['required', 'in:CLT,PJ,ESTAGIO,clt,pj,estagio'],
            'periodicidade' => ['required', 'in:MENSAL,HORA,DIA,mensal,hora,dia'],
            'moeda' => ['required', 'size:3'],
            'valor_minimo' => ['nullable'],
            'valor_maximo' => ['required'],
            'vigencia_inicio' => ['required'],
            // vigencia_fim opcional
        ];
    }

    public function insertedCount(): int
    {
        return $this->inserted;
    }

    public function model(array $row)
    {
        // Espera colunas: nome, funcao_profissional_id, saf_tipo_prestador_id, senioridade, tipo_contrato, periodicidade, valor_minimo, valor_maximo, moeda, vigencia_inicio, vigencia_fim, ativo, observacoes
        if (empty($row['tipo_contrato']) || empty($row['periodicidade']) || !isset($row['valor_maximo']) || empty($row['moeda']) || empty($row['vigencia_inicio'])) {
            return null; // pula linha inválida (também será capturada por failures)
        }

        $toFloat = function($value) {
            if ($value === null || $value === '') return null;
            if (is_numeric($value)) return (float)$value;
            // Trata formatos pt-BR: 1.234,56 -> 1234.56
            $s = str_replace([' ', chr(194).chr(160)], '', (string)$value);
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
            return is_numeric($s) ? (float)$s : null;
        };

        $bool = function($v) {
            $v = strtolower(trim((string)$v));
            return in_array($v, ['1','true','sim','yes','y','s','ativo','on','x'], true);
        };

        $model = new SafFaixaSalarial([
            'nome' => $row['nome'] ?? null,
            'funcao_profissional_id' => $row['funcao_profissional_id'] ?? null,
            'saf_tipo_prestador_id' => $row['saf_tipo_prestador_id'] ?? null,
            'senioridade' => isset($row['senioridade']) && $row['senioridade'] !== '' ? strtoupper($row['senioridade']) : null,
            'tipo_contrato' => strtoupper($row['tipo_contrato']),
            'periodicidade' => strtoupper($row['periodicidade']),
            'valor_minimo' => $toFloat($row['valor_minimo'] ?? null) ?? 0,
            'valor_maximo' => $toFloat($row['valor_maximo'] ?? null) ?? 0,
            'moeda' => strtoupper($row['moeda']),
            'vigencia_inicio' => $row['vigencia_inicio'],
            'vigencia_fim' => $row['vigencia_fim'] ?? null,
            'ativo' => isset($row['ativo']) ? $bool($row['ativo']) : true,
            'observacoes' => $row['observacoes'] ?? null,
        ]);

        // Se salvar sem exceptions, conta como inserido (Laravel Excel persiste automaticamente quando retorna Model)
        $this->inserted++;
        return $model;
    }
}
