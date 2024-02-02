<?php

namespace App\Models\Ixc;
use Carbon\Carbon;
use App\Helpers\Enumerators;
use App\Models\Ixc\ClientContract;
use Illuminate\Support\Facades\Auth;
use App\Models\BillingServices\Setup;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users\TelephoneExtension;
use App\Models\BillingServices\CustomerService;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountReceivable extends Model
{
    public $table = 'fn_areceber';

    public $connection = 'ixc';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data_vencimento' => 'datetime:d/m/Y',
        'ultima_conexao_inicial' => 'datetime:d/m/Y',
        'ultima_conexao_final' => 'datetime:d/m/Y',
        'nao_bloquear_ate' => 'datetime:d/m/Y',
    ];

    /**
     * @return BelongsTo
     **/
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_cliente');
    }

    /**
     * @return HasOne
     **/
    public function customerService(): HasOne
    {
        return $this->hasOne(CustomerService::class, 'ixc_client_id','id_cliente');
    }

    /**
     * @return BelongsTo
     **/
    public function clientContract(): BelongsTo
    {
        return $this->BelongsTo(ClientContract::class, 'id_contrato');
    }

    /**
     * @return object
     * Aqui acontece a busca de um cliente com boletos em atrasos
     **/
    public static function getDelayedCustomerPayments($filtro = null){
        $user = $filtro ? $filtro : Auth::user()->id;
        $setup = Setup::where('usuario_id',$user)->first();
        if (!$setup) {
            $setup = Setup::where('usuario_id',0)->first();
            if (auth()->user()->hasRole('super-admin') || auth()->user()->hasAnyPermission(['supervisor-cobranca'])){
                $filtro = true;
            }else {
                $filtro = false;
            }
        }
        $order = ($setup->tipo_data == 'fn_areceber.data_vencimento') ? 'fn_areceber.data_vencimento' : 'cliente_contrato.nao_bloquear_ate' ;

        $userId = false;
        $initials = TelephoneExtension::where('ixc_user_id',$setup->usuario_id)->first();

        $query = Client::join('fn_areceber', 'cliente.id', '=', 'fn_areceber.id_cliente')
        ->join('cliente_contrato', function ($join) { $join->on('cliente_contrato.id','=','fn_areceber.id_contrato')->orOn('cliente_contrato.id','=','fn_areceber.id_contrato_avulso');})
        ->whereDate('data_vencimento', '<', now())
        ->where('cliente.ativo', 'S')
        ->where('fn_areceber.status','=',Enumerators\AccountsReceivableEnumerators::status['À receber']);
        if ($initials) {
            $userId = Auth::user()->id;
            $query->whereRaw("SUBSTR(razao, 1, 1) BETWEEN '$initials->initialsIn' and '$initials->initialsOut'");
        }

        if ($setup->data_de) {
            $query->where($setup->tipo_data,'>=',$setup->data_de);
        }
        if ($setup->data_ate) {
            $query->where($setup->tipo_data,'<=',$setup->data_ate);
        }

        $query
            ->where('liberado','S')
            ->where('cliente_contrato.nao_bloquear_ate','<',now()->format('Y-m-d'))
            ->whereNotIn('cliente_contrato.id_cliente', CustomerService::customersServed())
            ->where('cliente_contrato.status','<>','P')
            // ->whereRaw("dayname(cliente_contrato.nao_bloquear_ate) not in ('Monday','Sunday','Saturday')")
            ->orderBy($order,$setup->direcao);

        if($setup->contract_status){
            $query->where('cliente_contrato.status',$setup->contract_status);
        }
        if($setup->status_acesso){
            $query->whereIn('cliente_contrato.status_internet',explode(',',$setup->status_acesso));
        }
        if ($filtro) {
            return $query->count('cliente_contrato.id_cliente');
        }
        if ($query->first(['cliente_contrato.id_cliente'])) {
            return $query->first(['cliente_contrato.id_cliente']);
        }else {
            return null;
        }
    }


    /**
     * @return object
     * Buscando dias de consumo do cliente a partir do boleto
     **/
    public function consumption()//:HasMany
    {
        $start = Carbon::parse($this->data_vencimento)->subMonth(1)->format('Y-m-d');
        $end = $this->data_vencimento;

        if($this->clientContract->radUser)
        return $this->clientContract->radUser->radAcct()->where([['acctstarttime','>=',$start],['acctstarttime','<=',$end],['framedipaddress','not like','10.%']])->groupByRaw('date(acctupdatetime)');
        else
        return $this->clientContract->radUser();
    }

    /**
     * @return string
     * retorna a data devencimento formatada para portugues
     **/
    public function getDataVencimentoFormatadaAttribute()
    {
        return Carbon::parse($this->data_vencimento)->format('d/m/Y');
    }
}
