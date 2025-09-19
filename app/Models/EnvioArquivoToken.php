<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioArquivoToken extends Model
{
    use HasFactory;

    protected $table = 'envio_arquivo_tokens';

    protected $fillable = [
        'envio_arquivo_id','token','expires_at','allow_download','created_by'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'allow_download' => 'boolean',
    ];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function arquivo()
    {
        return $this->belongsTo(EnvioArquivo::class, 'envio_arquivo_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
