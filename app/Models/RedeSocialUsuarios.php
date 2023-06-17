<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RedeSocialUsuarios extends Model
{
    protected $table = 'redesocialusuarios';
    public $timestamps = true;
    protected $fillable = ['RedeSocialRepresentante_id', 'RedeSocialRepresentante', 'RedeSocial_complemento','user_created', 'user_updated', 'RedeSocialFormandoBase_id' ];

    protected $casts = [
        'RedeSocialRepresentante_id' => 'int',
        'RedeSocialRepresentante' => 'int',
        'RedeSocialFormandoBase_id'=> 'int',
        'RedeSocial' => 'int',
        'RedeSocial_complemento' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];


    // public function RedeSocialRepresentantes(): HasOne
    // {
    //     return $this->hasOne(RedeSocial::class, 'id', 'RedeSocial');
    // }

    public function RedeSociais(): HasOne
    {
        return $this->hasOne(RedeSocial::class, 'id', 'RedeSocial');
    }
}


