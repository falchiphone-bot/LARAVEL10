<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentosArquivoVinculo extends Model
{
    protected $table = 'DocumentosArquivoVinculo';
    public $timestamps = true;
    protected $fillable = ['documento_id', 'arquivo_id_vinculo', 'user_created', 'user_updated' ];

    protected $casts = [
        'documento_id' => 'int',
        'arquivo_id_vinculo' => 'int',
        'created_at' => 'string',
        'updated_at' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];


    public function MostraArquivoNome(): HasOne
    {
        return $this->hasOne(TipoArquivo::class, 'id', 'arquivo_id_vinculo');
    }
    public function MostraLancamentoDocumento(): HasOne
    {
        return $this->hasOne(LancamentoDocumento::class, 'ID', 'arquivo_id_vinculo');
    }
}
