<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lancamento extends Model
{
    protected $primaryKey = 'ID';
    protected $table = 'Contabilidade.Lancamentos';
    public $timestamps = false;

    protected $fillable = [
        'ID',
        'Valor',
        'ValorQuantidadeDolar',
        'EmpresaID',
        'ContaDebitoID',
        'ContaCreditoID',
        'Usuarios_id',
        'DataContabilidade',
        'Created',
        'HistoricoID',
        'Complemento',
        'Descricao',
        'IDDocumentoEmpresa',
        'IdSolicitacaoEmpresarial',
        'Conferido',
        'SaidasGeral',
        'EntradasGeral',
        'Notificacao',
        'DiasNotificacaoAntesVencimento',
        'Investimentos',
        'Transferencias',
        'SemDefinir',
    ];

    protected $casts = [
        'DataContabilidade' => 'date:Y-m-d',
        'Conferido' => 'boolean',
        'SaidasGeral' => 'boolean',
        'EntradasGeral' => 'boolean',
        'Notificacao' => 'boolean',
        'Investimentos' => 'boolean',
        'Transferencias' => 'boolean',
        'SemDefinir' => 'boolean',
    ];

    // Relations
    public function Empresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'ID', 'EmpresaID');
    }

    public function ContaDebito(): HasOne
    {
        return $this->hasOne(Conta::class, 'ID', 'ContaDebitoID');
    }

    public function ContaCredito(): HasOne
    {
        return $this->hasOne(Conta::class, 'ID', 'ContaCreditoID');
    }

    public function SolicitacaoExclusao(): HasOne
    {
        return $this->hasOne(SolicitacaoExclusao::class, 'TableID', 'ID');
    }

    public function arquivos()
    {
        return $this->hasMany(LancamentoDocumento::class, 'LancamentoID', 'ID');
    }

    public function Historico(): HasOne
    {
        return $this->hasOne(Historicos::class, 'ID', 'HistoricoID');
    }

    public function ContasPagarArquivo(): HasOne
    {
        return $this->hasOne(ContasPagar::class, 'LancamentoID', 'ID');
    }

    // Mutator para normalizar o formato ao salvar
    public function setDataContabilidadeAttribute($value): void
    {
        if ($value instanceof \DateTimeInterface) {
            $this->attributes['DataContabilidade'] = Carbon::instance($value)->format('Y-m-d');
            return;
        }
        if (is_string($value)) {
            $v = trim($value);
            // d/m/Y
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
                try {
                    $this->attributes['DataContabilidade'] = Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d');
                    return;
                } catch (\Throwable $e) {}
            }
            // Y-m-d
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                $this->attributes['DataContabilidade'] = $v;
                return;
            }
            // Parse genérico
            try {
                $this->attributes['DataContabilidade'] = Carbon::parse($v)->format('Y-m-d');
                return;
            } catch (\Throwable $e) {}
        }
        $this->attributes['DataContabilidade'] = $value;
    }

    // Utils
    public static function somaValorQuantidadeDolar(array $conditions = []): float
    {
        $query = self::query();
        if (!empty($conditions)) {
            $query->where($conditions);
        }
        return (float) $query->sum('ValorQuantidadeDolar');
    }

    // Scopes reutilizáveis
    public function scopeDaConta($query, $contaId)
    {
        return $query->where(function ($q) use ($contaId) {
            $q->where('ContaDebitoID', $contaId)
              ->orWhere('ContaCreditoID', $contaId);
        });
    }

    public function scopePeriodo($query, ?\DateTimeInterface $de, ?\DateTimeInterface $ate)
    {
        if ($de) {
            $query->where('DataContabilidade', '>=', $de);
        }
        if ($ate) {
            $query->where('DataContabilidade', '<=', $ate);
        }
        return $query;
    }

    public function scopeNaoExcluido($query)
    {
        return $query->whereDoesntHave('SolicitacaoExclusao');
    }

    public function scopeTexto($query, ?string $descricao)
    {
        if ($descricao === null || $descricao === '') return $query;
        return $query->where(function ($q) use ($descricao) {
            $q->where('Lancamentos.Descricao', 'like', "%$descricao%")
              ->orWhere('Historicos.Descricao', 'like', "%$descricao%");
        });
    }

    public function scopeConferido($query, $valor)
    {
        if ($valor === '' || $valor === null) return $query;
        if ($valor === 'false') {
            return $query->where(function ($q) {
                $q->whereNull('Conferido')->orWhere('Conferido', 0);
            });
        }
        if ($valor === 'SaidasGeral' || $valor === 'EntradasGeral') {
            return $query; // validação em camada superior
        }
        return $query->where('Conferido', $valor);
    }

    public function scopeSaidasGeral($query)
    {
        return $query->where('Lancamentos.SaidasGeral', 1);
    }

    public function scopeEntradasGeral($query)
    {
        return $query->where('Lancamentos.EntradasGeral', 1);
    }

    public function scopeNotificacao($query, $valor)
    {
        if ($valor === '' || $valor === null) return $query;
        return $query->where('notificacao', $valor);
    }

    public function scopeEmpresa($query, $empresaId)
    {
        if (!$empresaId) return $query;
        return $query->where('EmpresaID', $empresaId);
    }

    public function scopeOrderByData($query)
    {
        return $query->orderBy('DataContabilidade');
    }

    public function scopeComRelacoesPadrao($query)
    {
        return $query->with(['ContaDebito.PlanoConta', 'ContaCredito.PlanoConta', 'Historico', 'Empresa']);
    }
}
