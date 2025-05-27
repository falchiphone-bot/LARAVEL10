<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Irmaos_Emaus_EntradaSaidaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            // 'data' =>'required|unique:feriados',
            'Empresa' =>'required',
            'idFichaControle' =>'required|numeric',
            'TipoEntradaSaida' =>'required',
            'DataEntradaSaida' =>'required|date',
            'Anotacoes' =>'nullable|string|max:5000',
        ];
    }
}
