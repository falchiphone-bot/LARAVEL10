<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SafAno extends Model
{
    protected $table = 'saf_anos';
    public $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = ['ano'];

    protected $casts = [
        'ano' => 'integer',
    ];

    public function campeonatos(): HasMany
    {
        return $this->hasMany(SafCampeonato::class, 'ano_id');
    }
}
