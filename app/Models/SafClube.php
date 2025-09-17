<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafClube extends Model
{
    protected $table = 'saf_clubes';
    public $timestamps = true;
    // Permite frações em SQL Server com datetime2(7)
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'nome',
        'cidade',
        'uf',
        'pais',
    ];

    protected $casts = [
        'nome' => 'string',
        'cidade' => 'string',
        'uf' => 'string',
        'pais' => 'string',
    ];
}
