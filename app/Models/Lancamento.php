<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lancamento extends Model
{
    protected $primaryKey = "ID";
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
        'SemDefinir'
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

    public function getDataContabilidadeAttribute($value)
    {
        return Carbon::createFromDate($value);
    }

    /**
     * Get all of the Arquivos for the Lancamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
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


    // public function setDataContabilidadeAttribute($value)
    // {
    //     return Carbon::createFromDate($value)->format('d/m/Y');
    // }

/**
     * Calcula a soma de 'ValorQuantidadeDolar' para todos os registros ou um subconjunto
     *
     * @param array $conditions (opcional) Condições para filtrar os registros
     * @return float
     */
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
            // Deixar validação para camada superior
            return $query;
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
