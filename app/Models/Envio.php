<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    use HasFactory;

    protected $fillable = ['nome','descricao','user_id','representante_id'];

    // Usa microssegundos para compatibilidade com datetime2(7)
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function arquivos()
    {
        return $this->hasMany(EnvioArquivo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function representante()
    {
        return $this->belongsTo(Representantes::class,'representante_id');
    }

    public function custos()
    {
        return $this->hasMany(EnvioCusto::class)
            ->whereNull('deleted_at')
            ->orderByDesc('data')
            ->orderByDesc('id');
    }

    public function safFaixasSalariais()
    {
        return $this->belongsToMany(\App\Models\SafFaixaSalarial::class, 'envio_saf_faixa_salarial', 'envio_id', 'saf_faixa_salarial_id');
    }
}
