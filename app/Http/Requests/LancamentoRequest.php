<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LancamentoResquest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
