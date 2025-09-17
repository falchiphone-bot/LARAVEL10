<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepresentantesUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nome' => 'required',
            'agente_fifa' => 'nullable|boolean',
            'oficial_cbf' => 'nullable|boolean',
            'sem_registro' => 'nullable|boolean',
            'email' => 'nullable|string',
        ];

        // CPF/CNPJ: permitem null/vazio quando liberação/limpeza estiver ativa
        $liberaCpf = (bool)$this->input('liberacpf');
        $liberaCnpj = (bool)$this->input('liberacnpj');
        $rules['cpf'] = $liberaCpf ? 'nullable|string' : 'nullable|string';
        $rules['cnpj'] = $liberaCnpj ? 'nullable|string' : 'nullable|string';

        return $rules;
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
