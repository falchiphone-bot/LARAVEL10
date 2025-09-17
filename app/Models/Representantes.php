<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Representantes extends Model
{
    protected $table = 'representantes';
    public $timestamps = true;
    protected $fillable = ['nome', 'EmpresaID','cpf', 'cnpj','email', 'telefone','tipo_representante','agente_fifa','oficial_cbf','sem_registro','user_created','user_updated'];

    protected $casts = [
        'nome' => 'string',
        'cpf' => 'string',
        'cnpj' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'tipo_representante' => 'int',
        'EmpresaID' => 'int',
        'agente_fifa' => 'boolean',
        'oficial_cbf' => 'boolean',
        'sem_registro' => 'boolean',
    ];


    public function MostraTipo(): HasOne
    {
        return $this->hasOne(TipoRepresentante::class, 'id', 'tipo_representante');
    }
    public function MostraEmpresa(): BelongsTo
    {
        // Representante pertence a uma Empresa (Empresas.ID)
        return $this->belongsTo(Empresa::class, 'EmpresaID', 'ID');
    }

    /**
     * Acessor/Mutator para CPF: trata string vazia como null na leitura e
     * evita gravar NULL em coluna NOT NULL (usa string vazia) na escrita.
     */
    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ($value === '' ? null : $value),
            set: fn ($value) => ($value === null ? '' : $value),
        );
    }

    /**
     * Acessor/Mutator para CNPJ: trata string vazia como null na leitura e
     * evita gravar NULL em coluna NOT NULL (usa string vazia) na escrita.
     */
    protected function cnpj(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ($value === '' ? null : $value),
            set: fn ($value) => ($value === null ? '' : $value),
        );
    }


}
