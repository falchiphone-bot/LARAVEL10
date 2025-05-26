<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Irmaos_Emaus_FichaControleCreateRequest extends FormRequest
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
            'Nome' =>'required::string|max:250',
            'Prontuario' => 'required|numeric',
            'Livro' => 'required|numeric',
            'Folha' => 'required|numeric',

        ];
    }
}
