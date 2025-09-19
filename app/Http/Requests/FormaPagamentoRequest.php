<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormaPagamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $nome = $this->route('FormaPagamento') ?? $this->route('forma_pagamento') ?? null;
        $ignore = $nome ? (',' . $nome . ',nome') : '';
        return [
            'nome' => ['required','string','max:255','unique:forma_pagamentos,nome'.$ignore],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nome')) {
            $v = (string)$this->input('nome');
            $v = trim($v);
            $v = preg_replace('/\s+/', ' ', $v);
            $this->merge(['nome' => $v]);
        }
    }
}
