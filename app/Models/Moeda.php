<?php
/// efetuado por Pedro Roberto Falchi
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moeda extends Model
{
    protected $table = 'moedas';
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'observacao',

    ];

    protected $casts = [

    ];

    public function MoedaComValores(): HasOne
    {

        return $this->hasOne(MoedasValores::class, 'idmoeda', 'id');
    }


}
