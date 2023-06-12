<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RedeSocialUsuarios extends Model
{
    protected $table = 'redesocialusuarios';
    public $timestamps = true;
    protected $fillable = ['RedeSocialRepresentante_id', 'RedeSocialRepresentante', 'RedeSocial_complemento','user_created', 'user_updated' ];

    protected $casts = [
        'RedeSocialRepresentante_id' => 'int',
        'RedeSocialRepresentante' => 'int',
        'RedeSocial_complemento' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];


    public function RedeSocialRepresentantes(): HasOne
    {
        return $this->hasOne(RedeSocial::class, 'id', 'RedeSocialRepresentante');
    }
}


