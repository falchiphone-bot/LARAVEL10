<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TradeideaCreateRequest extends FormRequest
{

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
            // 'cliente' =>'required',
            // 'assessor' =>'required',
            // 'Id_Tradeidea' =>'required',
            // 'tradeidea' =>'required',
            // 'analista' =>'required',
            // 'motivo' =>'required',
        ];
    }
}

