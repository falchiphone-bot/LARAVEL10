<?php
/// efetuado por Pedro Roberto Falchi

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faturamentos extends Model
{
    protected $table = 'faturamento';
    public $timestamps = false;

    protected $fillable = [
        'EmpresaID',
        'data',
        'ValorFaturamento',
        'ValorImposto',
        'PercentualImposto',
        'ValorBaseLucroLiquido',
        'PercentualLucroLiquido',
        'LucroLiquido',
        'LancadoPor',
         'created_at',
         'updated_at'];

    protected $casts = [
        'EmpresaID' => 'integer',
        'data' => 'date',];

    public function empresarelacionada(): HasOne
    {
        return $this->HasOne(Empresa::class, 'ID','EmpresaID');
    }


}
