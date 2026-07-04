@extends('layouts.auth')

@section('title', 'Nova Senha')

@section('content')
    <h1 class="auth-title">Definir nova senha</h1>
    <p class="auth-subtitle">Escolha uma senha forte para proteger a sua conta.</p>

    <form method="POST" action="{{ route('password.update') }}" id="resetForm" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- E-mail (hidden from user but needed for reset) --}}
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $email) }}"
                    readonly
                    style="background:rgba(255,255,255,.03);">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- New password --}}
        <div class="mb-3">
            <label for="password" class="form-label">Nova Senha</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" id="password" name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    autocomplete="new-password"
                    required
                    placeholder="Mínimo 8 caracteres"
                    id="password"
                >
                <button type="button" class="btn-scd-ghost" id="togglePwd"
                        style="border-radius:0 var(--scd-radius) var(--scd-radius) 0;border:1px solid rgba(255,255,255,.1);border-left:none;padding:.65rem .9rem;">
                    <i class="bi bi-eye" id="togglePwdIcon"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            {{-- Strength meter --}}
            <div style="margin-top:.5rem;">
                <div style="height:4px;background:rgba(255,255,255,.07);border-radius:4px;overflow:hidden;">
                    <div id="strengthBar" style="height:100%;width:0;border-radius:4px;transition:width .3s,background .3s;"></div>
                </div>
                <div id="strengthLabel" style="font-size:.73rem;color:var(--scd-text-muted);margin-top:.3rem;"></div>
            </div>
        </div>

        {{-- Confirm password --}}
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="form-control"
                    autocomplete="new-password"
                    required
                    placeholder="Repita a senha"
                >
            </div>
        </div>

        {{-- Password requirements --}}
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:var(--scd-radius);padding:.85rem 1rem;margin-bottom:1.25rem;">
            <div style="font-size:.75rem;font-weight:600;color:var(--scd-text-muted);margin-bottom:.5rem;">Requisitos da senha:</div>
            <ul style="margin:0;padding-left:1.2rem;font-size:.78rem;color:var(--scd-text-muted);line-height:1.8;">
                <li id="req-length">Mínimo 8 caracteres</li>
                <li id="req-upper">Pelo menos 1 maiúscula</li>
                <li id="req-lower">Pelo menos 1 minúscula</li>
                <li id="req-number">Pelo menos 1 número</li>
                <li id="req-symbol">Pelo menos 1 símbolo (!@#$...)</li>
            </ul>
        </div>

        <button type="submit" class="btn-scd-primary" id="resetBtn">
            <i class="bi bi-key me-2"></i>Redefinir senha
        </button>
    </form>
@endsection

@push('scripts')
<script>
    // Password visibility toggle
    document.getElementById('togglePwd').addEventListener('click', () => {
        const input = document.getElementById('password');
        const icon  = document.getElementById('togglePwdIcon');
        input.type  = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    });

    // Real-time strength meter
    document.getElementById('password').addEventListener('input', function () {
        const val  = this.value;
        const bar  = document.getElementById('strengthBar');
        const lbl  = document.getElementById('strengthLabel');

        const checks = {
            'req-length': val.length >= 8,
            'req-upper':  /[A-Z]/.test(val),
            'req-lower':  /[a-z]/.test(val),
            'req-number': /\d/.test(val),
            'req-symbol': /[^A-Za-z0-9]/.test(val),
        };

        let score = Object.values(checks).filter(Boolean).length;

        // Colour requirements
        Object.entries(checks).forEach(([id, ok]) => {
            const el = document.getElementById(id);
            el.style.color = ok ? 'var(--scd-success)' : 'var(--scd-text-muted)';
        });

        const levels = [
            { pct:  0,  color: 'transparent',        label: '' },
            { pct: 20,  color: '#f87171',             label: 'Muito fraca' },
            { pct: 40,  color: '#fbbf24',             label: 'Fraca' },
            { pct: 60,  color: '#f59e0b',             label: 'Razoável' },
            { pct: 80,  color: 'var(--scd-accent)',   label: 'Boa' },
            { pct: 100, color: 'var(--scd-success)',  label: 'Excelente' },
        ];

        bar.style.width      = levels[score].pct + '%';
        bar.style.background = levels[score].color;
        lbl.textContent      = levels[score].label;
        lbl.style.color      = levels[score].color === 'transparent' ? 'var(--scd-text-muted)' : levels[score].color;
    });

    // Loading state
    document.getElementById('resetForm').addEventListener('submit', function () {
        const btn = document.getElementById('resetBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A redefinir...';
    });
</script>
@endpush

