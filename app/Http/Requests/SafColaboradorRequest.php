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
            'forma_pagamento_nome' => ['nullable','string','max:255','exists:forma_pagamentos,nome'],
            'documento' => ['nullable','string','max:20'],
            // Aceita com/sem máscara e valida dígitos verificadores
            'cpf' => ['nullable','string','max:20', new CpfBr()],
            'email' => ['nullable','string','email','max:120'],
            'telefone' => ['nullable','string','max:50'],
            'cidade' => ['nullable','string','max:120'],
            'uf' => ['nullable','string','size:2'],
            'pais' => ['nullable','string','max:60'],
            'valor_salario' => ['nullable','numeric','min:0'],
            'dia_pagamento' => ['nullable','integer','min:1','max:31'],
            'ativo' => ['nullable','boolean'],
            'observacoes' => ['nullable','string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('valor_salario')) {
            $raw = $this->input('valor_salario');
            if (is_string($raw)) {
                // Normaliza formato brasileiro: 1.234,56 -> 1234.56
                $norm = str_replace(['.', ' '], '', $raw);
                $norm = str_replace(',', '.', $norm);
                $this->merge(['valor_salario' => $norm]);
            }
        }
        if ($this->has('dia_pagamento')) {
            $dp = $this->input('dia_pagamento');
            if ($dp === '' || $dp === null) {
                $this->merge(['dia_pagamento' => null]);
            } else {
                $this->merge(['dia_pagamento' => (int) $dp]);
            }
        }
    }
}
