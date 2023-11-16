<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class webhookAtendimentoEncerrado extends Model
{
    use HasFactory;

    protected $table = 'webhooksAtendimentoEncerrado';

    public $timestamps = true;

    protected $fillable = [
        'id_contact',
        'user_atendimento',
        'inicio_atendimento',
        'fim_atendimento',
    ];


    public function MostraContact(): HasOne
    {
        return $this->hasOne(webhookContact::class, 'id', 'id_contact');
    }
}


