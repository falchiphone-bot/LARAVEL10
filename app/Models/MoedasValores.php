<?php
/// efetuado por Pedro Roberto Falchi

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoedasValores extends Model
{
    protected $table = 'moedavalores';
    public $timestamps = false;

    protected $fillable = ['idmoeda', 'data', 'valor', 'created_at', 'updated_at'];

    protected $casts = [

        'data' => 'date',
    ];

    public function ValoresComMoeda(): HasOne
    {
        return $this->hasOne(Moeda::class, 'id', 'idmoeda');
    }
}
