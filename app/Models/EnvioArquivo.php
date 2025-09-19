<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioArquivo extends Model
{
    use HasFactory;

    protected $table = 'envio_arquivos';

    protected $fillable = [
        'envio_id','path','original_name','size','mime_type','uploaded_by',
        'mp4_path','hls_path','transcode_status','transcode_error'
        ,'last_transcode_at'
    ];

    // Usa microssegundos em timestamps para SQL Server (datetime2(7))
    protected $dateFormat = 'Y-m-d H:i:s.u';

    public function envio()
    {
        return $this->belongsTo(Envio::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
