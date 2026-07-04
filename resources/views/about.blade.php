<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SCD — Visão do Sistema e Essência">
    <title>Visão do Sistema — SCD</title>
    
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/scd.css') }}">

    <style>
        body {
            background-color: var(--scd-surface);
            margin: 0;
            color: var(--scd-text);
        }

        /* Navbar */
        header {
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--scd-border);
            background: var(--scd-surface);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand-custom {
            display: flex;
            align-items: center;
            gap: .75rem;
            text-decoration: none;
            color: var(--scd-text);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .navbar-brand-custom .icon-wrapper {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: var(--scd-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        /* Content */
        .content-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 4rem 1.5rem;
            animation: fade-up .5s ease both;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        .section-title span { color: var(--scd-primary); }

        .lead-text {
            font-size: 1.15rem;
            color: var(--scd-text-muted);
            line-height: 1.7;
            margin-bottom: 3rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: var(--scd-bg);
            border: 1px solid var(--scd-border);
            padding: 2rem;
            border-radius: 16px;
        }

        .feature-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }

        .feature-icon.green { background: rgba(126,184,164,.15); color: var(--scd-primary); }
        .feature-icon.purple { background: rgba(91,79,207,.15); color: var(--scd-accent); }
        .feature-icon.orange { background: rgba(237,137,54,.15); color: var(--scd-warning); }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: .75rem;
            color: var(--scd-text);
        }

        .feature-desc {
            font-size: .95rem;
            color: var(--scd-text-muted);
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .feature-grid { grid-template-columns: 1fr; }
            .section-title { font-size: 2rem; }
        }
    </style>
</head>
<body>

    <header>
        <a href="/" class="navbar-brand-custom">
            <div class="icon-wrapper">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            SCD
        </a>
        <a href="{{ route('home') }}" class="btn-scd-ghost"><i class="bi bi-arrow-left me-2"></i>Voltar</a>
    </header>

    <main class="content-container">
        <h1 class="section-title">A essência do <span>SCD</span></h1>
        <p class="lead-text">
            No panorama digital atual, a privacidade não é um luxo, é uma necessidade absoluta. O Sistema de Criptografia de Dados (SCD) nasceu da visão de que partilhar ficheiros dentro de uma organização não deve implicar o compromisso da sua integridade ou segurança.
        </p>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon green">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3 class="feature-title">Segurança Blindada (AES-256)</h3>
                <p class="feature-desc">
                    Utilizamos criptografia simétrica AES de 256 bits — o mesmo algoritmo adotado por governos e forças armadas. Os seus ficheiros são bloqueados a nível atómico antes de atingirem o armazenamento permanente.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon purple">
                    <i class="bi bi-person-bounding-box"></i>
                </div>
                <h3 class="feature-title">Autenticação de Zero Confiança</h3>
                <p class="feature-desc">
                    Através da integração com Autenticação de Dois Fatores (2FA) e do princípio de privilégio mínimo, o sistema exige provas inequívocas da sua identidade antes de lhe entregar as chaves do cofre.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon orange">
                    <i class="bi bi-diagram-3-fill"></i>
                </div>
                <h3 class="feature-title">Partilha Isolada e Controlada</h3>
                <p class="feature-desc">
                    Quer esteja a partilhar com um único colega ou com um departamento inteiro, os acessos são compartimentados. O emissor detém controlo total e pode revogar o acesso instantaneamente.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon green">
                    <i class="bi bi-journal-check"></i>
                </div>
                <h3 class="feature-title">Transparência Total (Auditoria)</h3>
                <p class="feature-desc">
                    Cada clique, cada ficheiro cifrado e cada login é registado de forma imutável. Acreditamos que a verdadeira segurança exige visibilidade completa sobre quem faz o quê no ecossistema.
                </p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 4rem; padding-top: 3rem; border-top: 1px solid var(--scd-border);">
            <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:1.5rem;">Pronto para proteger os seus dados?</h2>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-scd-primary" style="padding: .8rem 2rem; font-size: 1.1rem; border-radius: 12px;">Entrar no Cofre Segura</a>
                @else
                    <a href="{{ route('login') }}" class="btn-scd-primary" style="padding: .8rem 2rem; font-size: 1.1rem; border-radius: 12px;">Fazer Login</a>
                @endauth
            @endif
        </div>
    </main>

</body>
</html>
