<?php

namespace App\Exports;

use App\Models\SafColaborador;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SafColaboradoresExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = SafColaborador::query()->with(['representante','funcaoProfissional','tipoPrestador','faixaSalarial']);

        $q = trim((string)($this->filters['q'] ?? ''));
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('documento', 'like', "%{$q}%")
                  ->orWhere('cidade', 'like', "%{$q}%")
                  ->orWhere('uf', 'like', "%{$q}%")
                  ->orWhere('pais', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

    $representanteId = $this->filters['representante_id'] ?? null;
        $funcaoId = $this->filters['funcao_profissional_id'] ?? null;
        $tipoId = $this->filters['saf_tipo_prestador_id'] ?? null;
        $faixaId = $this->filters['saf_faixa_salarial_id'] ?? null;
    $cpfParam = isset($this->filters['cpf']) ? preg_replace('/\D/', '', (string)$this->filters['cpf']) : null;
        $cpfExact = filter_var($this->filters['cpf_exact'] ?? null, FILTER_VALIDATE_BOOLEAN);
        if (!empty($representanteId)) { $query->where('representante_id', $representanteId); }
        if (!empty($funcaoId)) { $query->where('funcao_profissional_id', $funcaoId); }
        if (!empty($tipoId)) { $query->where('saf_tipo_prestador_id', $tipoId); }
        if (!empty($faixaId)) { $query->where('saf_faixa_salarial_id', $faixaId); }
    if (!empty($cpfParam)) {
            if ($cpfExact) {
                $query->whereRaw("REGEXP_REPLACE(IFNULL(cpf,''), '[^0-9]', '') = ?", [$cpfParam]);
            } else {
                $query->whereRaw("REGEXP_REPLACE(IFNULL(cpf,''), '[^0-9]', '') LIKE ?", ["%{$cpfParam}%"]);
            }
        }

        $allowedSorts = ['nome','cidade','uf','pais','representante','funcao','tipo','faixa'];
        $sort = $this->filters['sort'] ?? 'nome';
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($this->filters['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sort === 'representante') {
            $query->leftJoin('representantes as r', 'r.id', '=', 'saf_colaboradores.representante_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('r.nome', $dir);
        } elseif ($sort === 'funcao') {
            $query->leftJoin('FuncaoProfissional as fp', 'fp.id', '=', 'saf_colaboradores.funcao_profissional_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('fp.nome', $dir);
        } elseif ($sort === 'tipo') {
            $query->leftJoin('saf_tipos_prestadores as tp', 'tp.id', '=', 'saf_colaboradores.saf_tipo_prestador_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('tp.nome', $dir);
        } elseif ($sort === 'faixa') {
            $query->leftJoin('saf_faixas_salariais as fs', 'fs.id', '=', 'saf_colaboradores.saf_faixa_salarial_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('fs.nome', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        return $query;
    }

    public function headings(): array
    {
    return ['Nome','Representante','Função Profissional','Tipo de Colaborador','Faixa Salarial','Documento','CPF','Email','Telefone','Cidade','UF','País','Ativo'];
    }

    public function map($row): array
    {
        return [
            $row->nome,
            optional($row->representante)->nome,
            optional($row->funcaoProfissional)->nome,
            optional($row->tipoPrestador)->nome,
            optional($row->faixaSalarial)->nome,
            $row->documento,
            $row->cpf,
            $row->email,
            $row->telefone,
            $row->cidade,
            $row->uf,
            $row->pais,
            $row->ativo ? 'SIM' : 'NÃO',
        ];
    }
}
