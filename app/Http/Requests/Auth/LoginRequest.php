<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'max:180'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'O endereço de e-mail é obrigatório.',
            'email.email'       => 'Introduza um endereço de e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.min'      => 'A senha deve ter pelo menos 8 caracteres.',
        ];
    }
}
