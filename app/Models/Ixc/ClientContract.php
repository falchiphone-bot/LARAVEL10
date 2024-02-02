<?php

namespace App\Models\Ixc;

use App\Helpers\Enumerators;
use App\Models\Atendimento\Atendimento;
use App\Models\Comissoes\Pagamento;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientContract extends Model
{
    /**
     * @var string
     */
    public $table = 'cliente_contrato';

    /**
     * @var string
     */
    public $connection = 'ixc';

    /**
     * @var string[]
     */
    protected $with = ['seller', 'pagamento', 'client', 'radUser'];

    /**
     * Get all of the atendimentos for the ClientContract
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class, 'id_contrato', 'id');
    }


    /**
     * Get all of the os for the ClientContract
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'id_contrato_kit');
    }

    /**
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'id_vendedor');
    }

    /**
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_cliente');
    }

    /**
     * @return HasOne
     */
    public function radUser(): HasOne
    {
        return $this->hasOne(RadUser::class, 'id_contrato');
    }

    /**
     * @return BelongsTo
     */
    public function vdContract(): BelongsTo
    {
        return $this->belongsTo(VdContract::class, 'id_vd_contrato');
    }

    /**
     * @return HasOne
     */
    public function pagamento(): HasOne
    {
        return $this->hasOne(Pagamento::class, 'contrato_id');
    }

    /**
     * @return string
     */
    public function getDateAttribute(): string
    {
        return Carbon::parse($this->data)->format('d/m/Y');
    }

    /**
     * @return string
     */
    public function getCommissionAttribute(): string
    {
        return number_format($this->getCommission(), 2, ',', '.');
    }

    /**
     * Calcula comissão da venda.
     */

    // public function getCommission(): float
    // {
    //     $commissionAmount = $this->seller->seller->commission_amount ?? 0;
    //     $vdContract = $this->vdContract;

    //     //utiliza-se ainda rad_group_id pra não precisar afazer alterarção diretamente no banco de dados
    //     if ($this->seller->seller->isCommissionByPlan())
    //         $commissionAmount = $this->seller->seller->plansCommission()
    //             ->where('rad_group_id', $vdContract->id)
    //             ->first()->commission_amount ?? 0;

    //     return $this->seller->seller->isCommissionFixed() ? $commissionAmount : $vdContract->valor_contrato * $commissionAmount / 100;
    // }

    /**
     * @return HasMany
     */
    public function accountReceivable(): HasMany
    {
        return $this->hasMany(AccountReceivable::class, 'id_contrato')
            ->where('fn_areceber.data_vencimento','<', now())->where(function ($query) {
            $query->where('status', '=', Enumerators\AccountsReceivableEnumerators::status['À receber'])
                ->orWhere('status', '=', Enumerators\AccountsReceivableEnumerators::status['Parcial']);
        })->orderBy('data_vencimento','asc');
    }

    /**
     * Get the primeiroPagamento associated with the ClientContract
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function primeiroPagamento(): HasOne
    {
        return $this->hasOne(AccountReceivable::class, 'id_contrato', 'id')->where('status','R')->orderBy('id');;
    }


    /**
     * Get all of the aR for the ClientContract
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aR(): HasMany
    {
        return $this->hasMany(AccountReceivable::class, 'id_contrato');
    }

    /**
     * @return Builder[]|Collection|HasMany[]
     */
    public function contractTitlesDelayed()
    {
        $rel = $this->aR()->where('data_vencimento','<',now())
        ->where(function ($query){
            $query->where('status', '=', Enumerators\AccountsReceivableEnumerators::status['À receber'])
                ->orWhere('status', '=', Enumerators\AccountsReceivableEnumerators::status['Parcial']);
        });
        return $rel;
    }

    public function formatarData($date)
    {
        return Carbon::createFromDate($date)->format('d/m/Y');
    }

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'ultima_conexao_inicial' => 'date',
        'ultima_conexao_final' => 'date',
        'pago_ate_data' => 'date',
        'nao_bloquear_ate' => 'date',
        'data_ativacao' => 'date',
    ];
}
