<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountActive
{
    /**
     * Reject requests from authenticated users whose account has been deactivated.
     * The account record is preserved for audit trail purposes — only the active flag changes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'A sua conta foi desactivada. Contacte o administrador.']);
        }

        return $next($request);
    }
}
