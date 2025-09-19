<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pix extends Model
{
    protected $table = 'pix';
    protected $primaryKey = 'nome';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['nome'];

    // Usar o campo 'nome' para model binding nas rotas
    public function getRouteKeyName(): string
    {
        return 'nome';
    }

    // Normaliza o valor antes de persistir
    public function setNomeAttribute($value): void
    {
        $nome = is_string($value) ? preg_replace('/\s+/', ' ', trim($value)) : $value;
        $this->attributes['nome'] = $nome;
    }
}
