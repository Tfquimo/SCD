@extends('layouts.app')

@section('title', 'Perfil & Segurança')
@section('page-title', 'Perfil & Segurança')

@section('content')

<div style="max-width:720px;">

    {{-- ── Profile info ────────────────────────────────────────── --}}
    <div class="scd-card p-4 mb-4 anim-fade-up">
        <h2 style="font-size:1rem;font-weight:600;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-person-circle text-scd-primary"></i> Informação da Conta
        </h2>

        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid var(--scd-border);">
            <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--scd-primary),var(--scd-accent));display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:1.05rem;font-weight:600;">{{ $user->name }}</div>
                <div style="font-size:.85rem;color:var(--scd-text-muted);">{{ $user->email }}</div>
                <div style="margin-top:.35rem;">
                    @if($user->isAdmin())
                        <span class="badge-role-admin">Administrador</span>
                    @elseif($user->isManager())
                        <span class="badge-role-manager">Gestor</span>
                    @else
                        <span class="badge-role-employee">Funcionário</span>
                    @endif
                    @if($user->isActive())
                        <span class="badge-active ms-1">Activa</span>
                    @else
                        <span class="badge-inactive ms-1">Inactiva</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Update name --}}
        <form method="POST" action="{{ route('profile.update-name') }}" novalidate>
            @csrf @method('PATCH')
            <div class="mb-3">
                <label for="name" class="form-label">Nome de exibição</label>
                <input type="text" id="name" name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}"
                    required minlength="2" maxlength="150">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn-scd-primary" style="width:auto;padding:.55rem 1.2rem;">
                <i class="bi bi-check-lg me-2"></i>Guardar nome
            </button>
        </form>
    </div>

    {{-- ── Change password ─────────────────────────────────────── --}}
    <div class="scd-card p-4 mb-4 anim-fade-up" style="animation-delay:.05s;">
        <h2 style="font-size:1rem;font-weight:600;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-lock-fill text-scd-accent"></i> Alterar Senha
        </h2>

        <form method="POST" action="{{ route('profile.update-password') }}" id="pwdForm" novalidate>
            @csrf @method('PUT')

            <div class="mb-3">
                <label for="current_password" class="form-label">Senha actual</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="current_password" name="current_password"
                        class="form-control @error('current_password') is-invalid @enderror"
                        autocomplete="current-password" required placeholder="••••••••••••">
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Nova senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" id="new_password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        autocomplete="new-password" required placeholder="Mínimo 8 caracteres">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div style="margin-top:.4rem;height:3px;background:rgba(255,255,255,.07);border-radius:3px;">
                    <div id="pwdStrBar" style="height:100%;width:0;border-radius:3px;transition:width .3s,background .3s;"></div>
                </div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control" autocomplete="new-password" required placeholder="Repita a nova senha">
                </div>
            </div>

            <button type="submit" class="btn-scd-primary" style="width:auto;padding:.55rem 1.2rem;" id="pwdBtn">
                <i class="bi bi-shield-lock me-2"></i>Alterar senha
            </button>
        </form>
    </div>

    {{-- ── Two-Factor Auth ─────────────────────────────────────── --}}
    <div class="scd-card p-4 anim-fade-up" style="animation-delay:.1s;">
        <h2 style="font-size:1rem;font-weight:600;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-phone-fill" style="color:var(--scd-warning);"></i> Autenticação em Dois Passos (2FA)
        </h2>

        @if ($user->hasVerified2FA())
            <div class="alert-scd-success mb-4" role="status">
                <i class="bi bi-shield-check-fill me-2"></i>
                <strong>2FA activo.</strong> A sua conta está protegida com autenticação em dois passos.
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1rem;background:rgba(52,211,153,.06);border:1px solid rgba(52,211,153,.15);border-radius:var(--scd-radius);">
                <div>
                    <div style="font-size:.85rem;font-weight:600;">Google Authenticator</div>
                    <div style="font-size:.75rem;color:var(--scd-text-muted);">
                        Activado em {{ $user->two_factor_confirmed_at?->format('d/m/Y \à\s H:i') }}
                    </div>
                </div>
                <form method="POST" action="{{ route('two-factor.disable') }}"
                      onsubmit="return confirm('Tem a certeza que quer desactivar o 2FA? Isto reduz a segurança da sua conta.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-scd-danger">
                        <i class="bi bi-shield-x me-1"></i>Desactivar
                    </button>
                </form>
            </div>
        @else
            <div class="alert-scd-warning mb-4" role="status">
                <i class="bi bi-shield-exclamation me-2"></i>
                <strong>2FA não activado.</strong> Recomendamos activar para maior segurança.
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1rem;background:rgba(251,191,36,.06);border:1px solid rgba(251,191,36,.15);border-radius:var(--scd-radius);">
                <div>
                    <div style="font-size:.85rem;font-weight:600;">Google Authenticator</div>
                    <div style="font-size:.75rem;color:var(--scd-text-muted);">Protecção adicional com código TOTP</div>
                </div>
                <a href="{{ route('two-factor.setup') }}" class="btn-scd-primary" style="width:auto;padding:.55rem 1.1rem;text-decoration:none;">
                    <i class="bi bi-shield-plus me-1"></i>Activar
                </a>
            </div>
        @endif

        {{-- Last login info --}}
        @if ($user->last_login_at)
        <div style="margin-top:1.2rem;padding:.75rem 1rem;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:var(--scd-radius);display:flex;gap:1.5rem;font-size:.8rem;color:var(--scd-text-muted);">
            <div>
                <i class="bi bi-clock-history me-1"></i>
                Último acesso: <strong style="color:var(--scd-text);">{{ $user->last_login_at->format('d/m/Y H:i') }}</strong>
            </div>
            <div>
                <i class="bi bi-geo-alt me-1"></i>
                IP: <strong style="color:var(--scd-text);">{{ $user->last_login_ip ?? 'N/D' }}</strong>
            </div>
        </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
    // Password strength bar on profile page
    document.getElementById('new_password').addEventListener('input', function () {
        const val  = this.value;
        const bar  = document.getElementById('pwdStrBar');
        const checks = [val.length >= 8, /[A-Z]/.test(val), /[a-z]/.test(val), /\d/.test(val), /[^A-Za-z0-9]/.test(val)];
        const score  = checks.filter(Boolean).length;
        const colors = ['transparent','#f87171','#fbbf24','#f59e0b','var(--scd-accent)','var(--scd-success)'];
        bar.style.width      = (score * 20) + '%';
        bar.style.background = colors[score];
    });

    document.getElementById('pwdForm').addEventListener('submit', function () {
        const btn = document.getElementById('pwdBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A guardar...';
    });
</script>
@endpush

