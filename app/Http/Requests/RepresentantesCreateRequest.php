<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepresentantesCreateRequest extends FormRequest
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
            'nome' => 'required',
            'agente_fifa' => 'nullable|boolean',
            'oficial_cbf' => 'nullable|boolean',
            'sem_registro' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $agente = (bool)$this->input('agente_fifa');
            $oficial = (bool)$this->input('oficial_cbf');
            $sem = (bool)$this->input('sem_registro');
            if ($sem && ($agente || $oficial)) {
                $v->errors()->add('sem_registro', 'Sem registro não pode ser marcado junto com Agente FIFA ou Oficial CBF.');
            }
        });
    }
}
