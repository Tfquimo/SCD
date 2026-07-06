@extends('layouts.auth')

@section('title', 'Iniciar Sessão')

@section('content')
    <h1 class="auth-title">Bem-vindo de volta</h1>
    <p class="auth-subtitle">Inicie sessão para aceder ao sistema seguro.</p>

    <form method="POST" action="{{ route('login.store') }}" id="loginForm" novalidate>
        @csrf

        {{-- E-mail --}}
        <div class="mb-3">
            <label for="email" class="form-label">Endereço de E-mail</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    autofocus
                    required
                    placeholder="utilizador@empresa.pt"
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label mb-0">Senha</label>
                <a href="{{ route('password.request') }}" class="scd-link" style="font-size:.8rem;">
                    Esqueceu a senha?
                </a>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    autocomplete="current-password"
                    required
                    placeholder="••••••••••••"
                >
                <button type="button"
                        class="btn-scd-ghost"
                        id="togglePassword"
                        style="border-radius:0 var(--scd-radius) var(--scd-radius) 0;border:1px solid rgba(255,255,255,.1);border-left:none;padding:.65rem .9rem;"
                        aria-label="Mostrar/ocultar senha">
                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Remember + Submit --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check" style="margin:0;">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);">
                <label class="form-check-label" for="remember" style="font-size:.83rem;color:var(--scd-text-muted);">
                    Manter sessão
                </label>
            </div>
        </div>

        <button type="submit" class="btn-scd-primary" id="submitBtn">
            <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
        </button>

    </form>

    {{-- Security info --}}
    <div class="auth-divider">Informação de segurança</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));gap:.6rem;">
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:.65rem .8rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-shield-fill-check" style="color:var(--scd-success);font-size:.9rem;"></i>
            <span style="font-size:.75rem;color:var(--scd-text-muted);">AES-256-CBC</span>
        </div>
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:.65rem .8rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-lock-fill" style="color:var(--scd-accent);font-size:.9rem;"></i>
            <span style="font-size:.75rem;color:var(--scd-text-muted);">TLS 1.3</span>
        </div>
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:.65rem .8rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-phone-fill" style="color:var(--scd-primary-h);font-size:.9rem;"></i>
            <span style="font-size:.75rem;color:var(--scd-text-muted);">2FA Google</span>
        </div>
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:.65rem .8rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-eye-slash-fill" style="color:var(--scd-warning);font-size:.9rem;"></i>
            <span style="font-size:.75rem;color:var(--scd-text-muted);">5 tentativas</span>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    const toggleBtn  = document.getElementById('togglePassword');
    const pwdInput   = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    toggleBtn.addEventListener('click', () => {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type    = isPassword ? 'text' : 'password';
        toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    // Loading state on submit
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A autenticar...';
    });
</script>
@endpush
