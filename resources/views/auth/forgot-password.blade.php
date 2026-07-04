@extends('layouts.auth')

@section('title', 'Recuperar Senha')

@section('content')
    <h1 class="auth-title">Recuperar senha</h1>
    <p class="auth-subtitle">
        Introduza o seu endereço de e-mail e enviaremos um link de recuperação, caso esteja registado.
    </p>

    <form method="POST" action="{{ route('password.email') }}" id="forgotForm" novalidate>
        @csrf

        <div class="mb-4">
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

        <button type="submit" class="btn-scd-primary mb-3" id="sendBtn">
            <i class="bi bi-send me-2"></i>Enviar link de recuperação
        </button>

        <div style="text-align:center;">
            <a href="{{ route('login') }}" class="scd-link" style="font-size:.85rem;">
                <i class="bi bi-arrow-left me-1"></i>Voltar ao login
            </a>
        </div>
    </form>

    {{-- Security note --}}
    <div class="auth-divider">Nota de segurança</div>
    <div class="alert-scd-info" style="font-size:.8rem;">
        <i class="bi bi-info-circle-fill me-2"></i>
        Por motivos de segurança, a resposta é sempre a mesma independentemente de o endereço estar ou não registado.
        O link expira em <strong>60 minutos</strong> e é de uso único.
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('forgotForm').addEventListener('submit', function () {
        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A enviar...';
    });
</script>
@endpush
