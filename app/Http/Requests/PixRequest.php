<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PixRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        // Normaliza o nome removendo espaços extras e aparando
        if ($this->has('nome')) {
            $nome = (string) $this->input('nome');
            $nome = preg_replace('/\s+/', ' ', trim($nome ?? ''));
            $this->merge(['nome' => $nome]);
        }
    }

    public function rules(): array
    {
        $nome = $this->route('pix');
        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pix', 'nome')->ignore($nome, 'nome'),
            ],
        ];
    }
}
