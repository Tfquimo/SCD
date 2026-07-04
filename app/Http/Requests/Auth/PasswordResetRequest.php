<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'string', 'email', 'max:180'],
            'password'              => [
                'required',
                'string',
                'confirmed',
                // Requerer um mínimo de 8 caracteres para a reposição de palavra-passe
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'             => 'Token de recuperação inválido.',
            'email.required'             => 'O e-mail é obrigatório.',
            'password.required'          => 'A nova senha é obrigatória.',
            'password.confirmed'         => 'As senhas não coincidem.',
            'password_confirmation.required' => 'A confirmação de senha é obrigatória.',
        ];
    }
}
