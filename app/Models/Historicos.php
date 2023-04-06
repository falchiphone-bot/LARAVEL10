<?php
/// efetuado por Pedro Roberto Falchi
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Historicos extends Model
{
    protected $primaryKey = "ID";
    protected $table = 'Contabilidade.Historicos';
    public $timestamps = false;

    protected $fillable = ['ID', 'EmpresaID', 'Descricao', 'UsuarioID', 'Created', 'ContaDebitoID', 'ContaCreditoID', 'PIX', 'Valor'];

    protected $casts = [
        'Created' => 'date',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'EmpresaID');
    }

     /**
     * Get the ContaCredito associated with the Lancamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ContaDebito(): HasOne
    {
        return $this->hasOne(Conta::class, 'ID', 'ContaDebitoID');
    }

    /**
     * Get the ContaCredito associated with the Lancamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ContaCredito(): HasOne
    {
        return $this->hasOne(Conta::class, 'ID', 'ContaCreditoID');
    }
 
}
