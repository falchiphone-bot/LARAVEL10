<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LancamentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Permitir, controle de permissão pode ser feito via middleware/Policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'Valor' => 'required',
            'EmpresaID' => 'required',
            'ContaDebitoID' => 'required',
            'ContaCreditoID' => 'required',
            'DataContabilidade' => 'required',
            'Descricao' => 'required',

        ];
    }
}
