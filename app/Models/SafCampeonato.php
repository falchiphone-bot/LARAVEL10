<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SafCampeonato extends Model
{
    protected $table = 'saf_campeonatos';
    public $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'nome',
        'cidade',
        'uf',
        'pais',
        'federacao_id',
    ];

    protected $casts = [
        'nome' => 'string',
        'cidade' => 'string',
        'uf' => 'string',
        'pais' => 'string',
        'federacao_id' => 'integer',
    ];

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categorias::class, 'saf_campeonato_categoria', 'campeonato_id', 'categoria_id');
    }

    public function federacao()
    {
        return $this->belongsTo(SafFederacao::class, 'federacao_id');
    }
}
