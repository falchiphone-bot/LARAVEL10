<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SafAnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ano' => ['required','integer','min:1900','max:9999'],
        ];
    }
}
