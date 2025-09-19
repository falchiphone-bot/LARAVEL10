<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioArquivoShare extends Model
{
    use HasFactory;

    protected $table = 'envio_arquivo_shares';

    protected $fillable = [
        'envio_arquivo_id','user_id'
    ];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function arquivo()
    {
        return $this->belongsTo(EnvioArquivo::class, 'envio_arquivo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
