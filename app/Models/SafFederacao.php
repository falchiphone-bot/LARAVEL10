<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SafFederacao extends Model
{
    protected $table = 'saf_federacoes';
    public $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'nome', 'cidade', 'uf', 'pais',
    ];

    protected $casts = [
        'nome' => 'string',
        'cidade' => 'string',
        'uf' => 'string',
        'pais' => 'string',
    ];

    public function campeonatos(): HasMany
    {
        return $this->hasMany(SafCampeonato::class, 'federacao_id');
    }
}
