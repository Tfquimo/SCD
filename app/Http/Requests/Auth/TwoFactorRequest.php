<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'O código de autenticação é obrigatório.',
            'code.digits'   => 'O código deve ter exactamente 6 dígitos.',
        ];
    }
}
