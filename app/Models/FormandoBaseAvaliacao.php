<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormandoBaseAvaliacao extends Model
{
    protected $table = 'formandobaseavaliacao';
    public $timestamps = true;
    protected $fillable = ['formandobase_id', 'avaliacao', 'user_created', 'user_updated' ];

    protected $casts = [
        'formandobase_id' => 'int',
        'avaliacao' => 'float',
        'created_at' => 'string',
        'updated_at' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];


    public function MostraFormando(): HasOne
    {
        return $this->hasOne(FormandoBase::class, 'id', 'formandobase_id');
    }
}
