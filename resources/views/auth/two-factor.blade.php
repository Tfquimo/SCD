@extends('layouts.auth')

@section('title', 'Verificação 2FA')

@section('content')
    <h1 class="auth-title">Verificação em dois passos</h1>
    <p class="auth-subtitle">
        Introduza o código de 6 dígitos do Google Authenticator para concluir o acesso.
    </p>

    @if ($errors->has('code'))
        <div class="alert-scd-danger mb-3" role="alert">
            <i class="bi bi-shield-x me-2"></i>{{ $errors->first('code') }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}" id="tfaForm" novalidate>
        @csrf

        {{-- OTP input --}}
        <div class="mb-4">
            <label for="code" class="form-label">Código de autenticação</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                <input
                    type="text"
                    id="code"
                    name="code"
                    class="form-control @error('code') is-invalid @enderror"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    autofocus
                    required
                    placeholder="000000"
                    style="font-size:1.6rem;letter-spacing:.4em;text-align:center;font-weight:600;"
                >
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div style="font-size:.75rem;color:var(--scd-text-muted);margin-top:.5rem;text-align:center;">
                O código expira em 30 segundos. Certifique-se que o relógio do dispositivo está sincronizado.
            </div>
        </div>

        {{-- Countdown timer visual --}}
        <div style="margin-bottom:1.2rem;">
            <div style="height:3px;background:rgba(255,255,255,.07);border-radius:3px;overflow:hidden;">
                <div id="otpProgress"
                     style="height:100%;background:linear-gradient(90deg,var(--scd-primary),var(--scd-accent));border-radius:3px;transition:width .9s linear;width:100%;">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-scd-primary" id="tfaSubmit">
            <i class="bi bi-shield-check me-2"></i>Verificar código
        </button>
    </form>

    <div class="auth-divider">ou</div>

    <div style="text-align:center;">
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="scd-link" style="font-size:.85rem;">
            <i class="bi bi-arrow-left me-1"></i>Voltar ao login
        </a>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
            @csrf
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // TOTP 30-second countdown progress bar
    function updateOtpProgress() {
        const bar      = document.getElementById('otpProgress');
        const epoch    = Math.floor(Date.now() / 1000);
        const elapsed  = epoch % 30;
        const pct      = ((30 - elapsed) / 30) * 100;
        bar.style.width = pct + '%';
        bar.style.background = pct < 33
            ? 'linear-gradient(90deg,#f87171,#fbbf24)'
            : 'linear-gradient(90deg,var(--scd-primary),var(--scd-accent))';
    }
    updateOtpProgress();
    setInterval(updateOtpProgress, 1000);

    // Auto-submit when 6 digits entered
    document.getElementById('code').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
        if (this.value.length === 6) {
            const btn = document.getElementById('tfaSubmit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A verificar...';
            document.getElementById('tfaForm').submit();
        }
    });
</script>
@endpush
