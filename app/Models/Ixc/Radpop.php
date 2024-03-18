<?php
namespace App\Models\Ixc;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Radpop extends Model
{
    protected $table = 'radpop';
    public $connection = 'ixc';

    public $timestamps = false;

    protected $fillable = [
        'pop', 'id_cidade',
    ];


    protected $casts = [
        'pop' => 'string',

    ];

    public function cidade(): HasOne
    {
        return $this->hasOne(cidade::class, 'id', 'id_cidade');
    }

}
