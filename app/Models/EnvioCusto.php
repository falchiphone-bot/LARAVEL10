<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnvioCusto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'envio_id','nome','valor','data'
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2'
    ];

    public function envio()
    {
        return $this->belongsTo(Envio::class);
    }
}
