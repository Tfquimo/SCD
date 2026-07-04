<!DOCTYPE html>
<html lang="pt" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SCD — Sistema de Criptografia de Dados para Empresas">
    <title>@yield('title', 'Autenticação') — SCD</title>

    {{-- Bootstrap 5.3 + Bootstrap Icons + SCD — todos offline --}}
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/scd.css') }}">

    @stack('styles')
</head>
<body class="auth-bg">

    <div class="auth-card scd-card">
        {{-- Logo --}}
        <a href="{{ url('/') }}" class="auth-logo">
            <div class="auth-logo-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <div class="auth-logo-name">SCD</div>
                <div class="auth-logo-sub">Sistema de Criptografia de Dados</div>
            </div>
        </a>

        {{-- Flash messages --}}
        @if (session('status'))
            <div class="alert-scd-success mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            </div>
        @endif

        @if ($errors->has('email') && ! $errors->has('password'))
            <div class="alert-scd-danger mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first('email') }}
            </div>
        @endif

        @yield('content')

        {{-- Security badge --}}
        <div class="security-badge">
            <i class="bi bi-shield-check-fill"></i>
            AES-256-CBC &bull; TLS 1.3 &bull; 2FA
        </div>
    </div>

    {{-- Bootstrap JS -- offline --}}
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
