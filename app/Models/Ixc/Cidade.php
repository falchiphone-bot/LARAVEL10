<?php

namespace App\Models\Ixc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cidade extends Model
{
    protected $table = 'cidade';
    public $connection = 'ixc';

    public $timestamps = false;

    protected $fillable = [
        'nome',
    ];


    protected $casts = [
        'nome' => 'string',

    ];

}

