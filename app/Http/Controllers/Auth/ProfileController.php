<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Mostra o perfil do utilizador e as definições de segurança.
     */
    public function show(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return view('auth.profile', compact('user'));
    }

    /**
     * Actualiza o nome de apresentação do utilizador.
     */
    public function updateName(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
        ]);

        $request->user()->update(['name' => $validated['name']]);

        return back()->with('status', 'Nome actualizado com sucesso.');
    }

    /**
     * Altera a palavra-passe do utilizador autenticado.
     * Requer verificação da palavra-passe actual antes de aceitar a nova.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => [
                'required',
                'string',
                'confirmed',
                // Requerer um mínimo de 8 caracteres para a nova palavra-passe
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => ['required', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Verifica a palavra-passe actual com o Hash::check (nunca comparar em texto limpo)
        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'A senha actual está incorrecta.',
            ]);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('status', 'Senha alterada com sucesso.');
    }
}
