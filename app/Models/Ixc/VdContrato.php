<?php

namespace App\Models\Ixc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VdContrato extends Model
{
    use HasFactory;

    protected $connection = 'ixc';

    public function contratos()
    {
        return $this->hasMany(ClientContract::class,'id_vd_contrato','id');
    }
}
