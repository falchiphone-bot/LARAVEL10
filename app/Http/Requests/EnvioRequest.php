<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnvioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome' => ['required','string','max:255'],
            'descricao' => ['nullable','string'],
            'files' => ['nullable','array'],
            'files.*' => ['file','max:102400'], // 100 MB
        ];
    }
}
