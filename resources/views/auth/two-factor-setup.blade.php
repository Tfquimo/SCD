@extends('layouts.auth')

@section('title', 'Activar 2FA')

@section('content')
    <h1 class="auth-title">Configurar autenticação 2FA</h1>
    <p class="auth-subtitle">Digitalize o QR code com o Google Authenticator e confirme com o primeiro código.</p>

    {{-- Step 1: QR Code --}}
    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:var(--scd-radius);padding:1.25rem;margin-bottom:1.25rem;">
        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--scd-text-muted);margin-bottom:.75rem;">
            <i class="bi bi-1-circle-fill me-2" style="color:var(--scd-primary-h);"></i>Digitalize o QR code
        </div>
        <div style="display:flex;justify-content:center;">
            {{-- QR code rendered via data URI from the controller --}}
            <img src="{{ $qrUrl }}" alt="QR Code 2FA"
                 style="width:180px;height:180px;border-radius:10px;background:#fff;padding:8px;">
        </div>
        <div style="font-size:.75rem;color:var(--scd-text-muted);text-align:center;margin-top:.75rem;">
            Não consegue digitalizar? Use o código manual:
        </div>
        <div style="background:rgba(0,0,0,.3);border-radius:8px;padding:.5rem 1rem;text-align:center;margin-top:.4rem;font-family:monospace;font-size:.85rem;letter-spacing:.15em;color:var(--scd-accent);word-break:break-all;">
            {{ $secret }}
        </div>
    </div>

    {{-- Step 2: Confirm --}}
    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:var(--scd-radius);padding:1.25rem;">
        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--scd-text-muted);margin-bottom:.75rem;">
            <i class="bi bi-2-circle-fill me-2" style="color:var(--scd-accent);"></i>Confirme com o primeiro código
        </div>

        @error('code')
            <div class="alert-scd-danger mb-3" role="alert">
                <i class="bi bi-shield-x me-2"></i>{{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('two-factor.enable') }}" id="enableForm">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Código do Google Authenticator</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    class="form-control"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    autofocus
                    required
                    placeholder="000000"
                    style="font-size:1.4rem;letter-spacing:.4em;text-align:center;font-weight:600;"
                >
            </div>
            <button type="submit" class="btn-scd-primary" id="enableBtn">
                <i class="bi bi-shield-check me-2"></i>Activar 2FA
            </button>
        </form>
    </div>

    <div style="text-align:center;margin-top:1rem;">
        <a href="{{ route('profile.show') }}" class="scd-link" style="font-size:.83rem;">
            <i class="bi bi-arrow-left me-1"></i>Cancelar e voltar ao perfil
        </a>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('code').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });
    document.getElementById('enableForm').addEventListener('submit', function () {
        const btn = document.getElementById('enableBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A activar...';
    });
</script>
@endpush
