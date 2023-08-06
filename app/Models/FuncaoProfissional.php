<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class FuncaoProfissional extends Model
{
    protected $table = 'FuncaoProfissional';
    public $timestamps = true;
    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => 'string',
    ];
}
