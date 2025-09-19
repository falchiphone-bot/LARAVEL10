<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfBr;

class SafColaboradorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome' => ['required','string','max:120'],
            'representante_id' => ['nullable','integer','exists:representantes,id'],
            'funcao_profissional_id' => ['nullable','integer','exists:FuncaoProfissional,id'],
            'saf_tipo_prestador_id' => ['nullable','integer','exists:saf_tipos_prestadores,id'],
            'saf_faixa_salarial_id' => ['nullable','integer','exists:saf_faixas_salariais,id'],
            'pix_nome' => ['nullable','string','max:255','exists:pix,nome'],
            'documento' => ['nullable','string','max:20'],
            // Aceita com/sem máscara e valida dígitos verificadores
            'cpf' => ['nullable','string','max:20', new CpfBr()],
            'email' => ['nullable','string','email','max:120'],
            'telefone' => ['nullable','string','max:50'],
            'cidade' => ['nullable','string','max:120'],
            'uf' => ['nullable','string','size:2'],
            'pais' => ['nullable','string','max:60'],
            'ativo' => ['nullable','boolean'],
            'observacoes' => ['nullable','string'],
        ];
    }
}
