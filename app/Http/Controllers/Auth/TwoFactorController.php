<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorRequest;
use App\Models\AuditLog;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly TwoFactorService $twoFactorService
    ) {}

    /**
     * Mostra o formulário de desafio 2FA (passo 2 do login).
     */
    public function challenge(): View|RedirectResponse
    {
        if ($this->twoFactorService->sessionIsVerified(request())) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.two-factor');
    }

    /**
     * Verifica o código OTP submetido no passo de desafio.
     */
    public function verify(TwoFactorRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $this->twoFactorService->verify($user, $request->input('code'))) {
            AuditLog::create([
                'user_id'     => $user->id,
                'action'      => AuditLog::ACTION_2FA_FAILED,
                'entity_type' => \App\Models\User::class,
                'entity_id'   => $user->id,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'metadata'    => ['stage' => 'challenge'],
            ]);

            return back()->withErrors(['code' => 'Código de autenticação inválido ou expirado.']);
        }

        $this->twoFactorService->markSessionVerified($request);

        AuditLog::create([
            'user_id'     => $user->id,
            'action'      => AuditLog::ACTION_2FA_VERIFIED,
            'entity_type' => \App\Models\User::class,
            'entity_id'   => $user->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Mostra a página de configuração do 2FA (código QR + formulário de ativação).
     */
    public function setup(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user   = $request->user();
        $secret = $this->twoFactorService->generateSecret($user);
        $qrUrl  = $this->twoFactorService->getQrCodeUrl($user);

        return view('auth.two-factor-setup', compact('secret', 'qrUrl'));
    }

    /**
     * Ativa o 2FA após o utilizador confirmar o primeiro código OTP.
     */
    public function enable(TwoFactorRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $this->twoFactorService->enable($user, $request->input('code'), $request)) {
            return back()->withErrors(['code' => 'Código inválido. Verifique o seu Google Authenticator e tente novamente.']);
        }

        return redirect()->route('profile.show')
            ->with('status', '2FA activado com sucesso. A sua conta está agora mais segura.');
    }

    /**
     * Desativa o 2FA para o utilizador autenticado.
     */
    public function disable(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->twoFactorService->disable($user, $request);

        return redirect()->route('profile.show')
            ->with('status', '2FA desactivado.');
    }
}
