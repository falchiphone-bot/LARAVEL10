<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SafFaixaSalarial;

class SafFaixaSalarialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome' => ['nullable','string','max:120'],
            'funcao_profissional_id' => ['nullable','exists:FuncaoProfissional,id'],
            'saf_tipo_prestador_id' => ['nullable','exists:saf_tipos_prestadores,id'],
            'senioridade' => ['nullable','string','max:30'],
            'tipo_contrato' => ['required','string','max:20', Rule::in(['CLT','PJ','ESTAGIO'])],
            'periodicidade' => ['required','string','max:20', Rule::in(['MENSAL','HORA','DIA'])],
            'valor_minimo' => ['required','numeric','gte:0'],
            'valor_maximo' => ['required','numeric','gte:valor_minimo'],
            'moeda' => ['required','string','size:3'],
            'vigencia_inicio' => ['required','date'],
            'vigencia_fim' => ['nullable','date','after_or_equal:vigencia_inicio'],
            'ativo' => ['sometimes','boolean'],
            'observacoes' => ['nullable','string'],
        ];
    }
}
