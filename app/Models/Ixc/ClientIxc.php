<?php

namespace App\Models\Ixc;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\BillingServices\CustomerService;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Helpers\Enumerators;

class ClientIxc extends Model
{
    /**
     * @var string
     */
    public $table = 'cliente';

    /**
     * @var string
     */
    public $connection = 'ixc';

    /**
     * @return HasMany
     **/
    public function clientContract(): HasMany
    {
        return $this->hasMany(ClientContract::class, 'id_cliente');
    }

    /**
     * @return HasMany
     **/
    public function accountsReceivables(): HasMany
    {
        return $this->hasMany(AccountReceivable::class, 'id_cliente');
    }

    /**
     * @return HasMany
     */
    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'id_cliente');
    }

    /**
     * @return HasMany
     **/
    public function radUsers(): HasMany
    {
        return $this->hasMany(RadUser::class, 'id_cliente');
    }

    /**
     * @return HasOne
     */
    public function customerService(): HasOne
    {
        return $this->hasOne(CustomerService::class, 'ixc_client_id');
    }

    /**
     * @return BelongsTo
     **/
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'cidade');
    }

    /**
     * @return Builder[]|Collection|HasMany[]
     */
    public function contractTitlesDelayed()
    {
        $contratos = $this->clientContract;
        $filtro = [];
        foreach ($contratos as $key => $contrato) {
            $boletos = AccountReceivable::where('id_contrato',$contrato->id)->where('status','A')->where('data_vencimento','<',now())->count('id');
            if ($boletos>0) {
                $filtro[$key] = $contrato;
            }
        }

        return $filtro;
    }

    /**
     * @return null|Model
     */
    public function getInstalationOrderAttribute(): ?Model
    {
        return $this->serviceOrders()->whereHas('subject', function ($query) {
            $query->where(function ($query) {
                $query->where('assunto', ServiceOrder::INSTALATTION);
                $query->orWhere('assunto', ServiceOrder::SECOND_INSTALATTION);
            });
        })->first();
    }

    /**
     * @return null|Model
     */
    public function getWithdrawalOrderAttribute(): ?Model
    {
        return $this->serviceOrders()->whereHas('subject', function ($query) {
            $query->where('assunto', ServiceOrder::WITHDRAWAL);
        })->first();
    }
}
