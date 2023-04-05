<?php
/// efetuado por Pedro Roberto Falchi
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevSicredi extends Model
{
    protected $table = 'DevSicrediAPI';
    // public $timestamps = false;

    protected $fillable = [
        'DESENVOLVEDOR',
        'SICREDI_CLIENT_ID',
        'SICREDI_CLIENT_SECRET',
        'SICREDI_TOKEN',
        'URL_API',
    ];

    protected $casts = [

    ];

    // public function DevSicrediComValores(): HasOne
    // {

    //     return $this->hasOne(DevSicredisValores::class, 'idDevSicredi', 'id');
    // }


}
