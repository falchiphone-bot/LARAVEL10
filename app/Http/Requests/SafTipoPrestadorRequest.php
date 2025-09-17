<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SafTipoPrestadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'uf' => 'nullable|string|size:2',
            'pais' => 'nullable|string|max:255',
            'funcao_profissional_id' => 'nullable|exists:FuncaoProfissional,id',
        ];
    }
}
