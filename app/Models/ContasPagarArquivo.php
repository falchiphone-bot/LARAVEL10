<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContasPagarArquivo extends Model
{
    protected $table = 'ContasPagarArquivo';
    public $timestamps = true;
    protected $fillable = ['ContasPagar_id', 'arquivo_id', 'user_created', 'user_updated' ];

    protected $casts = [
        'contaspagar_id' => 'int',
        'arquivo_id' => 'int',
        'created_at' => 'string',
        'updated_at' => 'string',
        'user_created' => 'string',
        'user_updated' => 'string',
    ];


    public function MostraArquivoNome(): HasOne
    {
        return $this->hasOne(TipoArquivo::class, 'id', 'arquivo_id');
    }
    public function MostraLancamentoDocumento(): HasOne
    {
        return $this->hasOne(LancamentoDocumento::class, 'ID', 'arquivo_id');
    }
}
