<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CaargoProfissional extends Model
{
    protected $table = 'CargoProfissional';
    public $timestamps = true;
    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => 'string',
    ];
}
