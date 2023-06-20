<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RecebimentoFormandoBase extends Model
{
    protected $table = 'recebimentoformandobase';
    public $timestamps = true;
    protected $fillable = ['formandobase_id', 'valor', 'data', 'representante_id',
    'user_created', 'user_updated' ];

    protected $casts = [
        'formandobase_id' => 'int',
        'representante_id' => 'int',
        'valor' => 'decimal',
        'data' => 'date',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];


    public function MostraFormandoBase(): HasOne
    {
        return $this->hasOne(FormandoBase::class, 'id', 'formando_base');
    }
    public function MostraRepresentante(): HasOne
    {
        return $this->hasOne(Representantes::class, 'id', 'representante_id');
    }
}
