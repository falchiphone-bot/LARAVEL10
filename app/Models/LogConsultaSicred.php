<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogConsultaSicred extends Model
{
    protected $table = 'Contabilidade.LogsConsultaSicredi';

    public $fillable = [
        'carteira_id',
        'quantidade',
        'dia',
    ];
}
