<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DevSicrediCreateRequest extends FormRequest
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
            'DESENVOLVEDOR' =>'required',
            'SICREDI_CLIENT_ID'=>'required',
            'SICREDI_CLIENT_SECRET'=>'required',
            'SICREDI_TOKEN'=>'required',
            'URL_API'=>'required',
        ];
    }
}
