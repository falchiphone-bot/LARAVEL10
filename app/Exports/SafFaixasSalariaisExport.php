<?php

namespace App\Exports;

use App\Models\SafFaixaSalarial;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SafFaixasSalariaisExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $q = trim((string) ($this->filters['q'] ?? ''));
        $funcaoId = $this->filters['funcao_profissional_id'] ?? null;
        $tipoPrestadorId = $this->filters['saf_tipo_prestador_id'] ?? null;
        $senioridade = $this->filters['senioridade'] ?? null;
        $tipoContrato = $this->filters['tipo_contrato'] ?? null;
        $moeda = $this->filters['moeda'] ?? null;
        $vigentes = filter_var($this->filters['somente_vigentes'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $dataCorte = $this->filters['data_corte'] ?? null;

        $query = SafFaixaSalarial::query()->with(['funcaoProfissional','tipoPrestador']);
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('observacoes', 'like', "%{$q}%");
            });
        }
        if (!empty($funcaoId)) { $query->where('funcao_profissional_id', $funcaoId); }
        if (!empty($tipoPrestadorId)) { $query->where('saf_tipo_prestador_id', $tipoPrestadorId); }
        if (!empty($senioridade)) { $query->where('senioridade', $senioridade); }
        if (!empty($tipoContrato)) { $query->where('tipo_contrato', $tipoContrato); }
        if (!empty($moeda)) { $query->where('moeda', strtoupper($moeda)); }
        if ($vigentes) {
            $data = $dataCorte ? date('Y-m-d 00:00:00', strtotime($dataCorte)) : now();
            $query->where('vigencia_inicio','<=',$data)
                  ->where(function($w) use ($data) { $w->whereNull('vigencia_fim')->orWhere('vigencia_fim','>=',$data); });
        }

        return $query->orderBy('vigencia_inicio', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID','Nome','Funcao','Tipo Prestador','Senioridade','Contrato','Periodicidade','Valor Minimo','Valor Maximo','Moeda','Vig. Inicio','Vig. Fim','Ativo','Observacoes'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->nome,
            optional($row->funcaoProfissional)->nome,
            optional($row->tipoPrestador)->nome,
            $row->senioridade,
            $row->tipo_contrato,
            $row->periodicidade,
            (float)$row->valor_minimo,
            (float)$row->valor_maximo,
            $row->moeda,
            optional($row->vigencia_inicio)->format('Y-m-d'),
            $row->vigencia_fim ? optional($row->vigencia_fim)->format('Y-m-d') : null,
            $row->ativo ? 1 : 0,
            $row->observacoes,
        ];
    }
}
