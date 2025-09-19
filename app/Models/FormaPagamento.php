<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    protected $table = 'forma_pagamentos';
    public $timestamps = false;
    protected $primaryKey = 'nome';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => 'string',
    ];

    public function getRouteKeyName(): string
    {
        return 'nome';
    }

    public function setNomeAttribute($value): void
    {
        $value = is_string($value) ? trim($value) : $value;
        $value = preg_replace('/\s+/', ' ', (string)$value);
        $this->attributes['nome'] = $value;
    }
}
