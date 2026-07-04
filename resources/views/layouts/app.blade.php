<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SCD — Sistema de Criptografia de Dados">
    <title>@yield('title', 'Visão Geral') — SCD</title>

    {{-- Bootstrap 5.3 + Bootstrap Icons + SCD — todos offline --}}
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/scd.css') }}">

    @stack('styles')
</head>
<body>

{{-- ════════════════════════════════════════════════
     SIDEBAR — Estilo charcoal escuro (inspirado na imagem de referência)
════════════════════════════════════════════════ --}}
<aside class="scd-sidebar" id="scdSidebar">

    {{-- Logo / Marca --}}
    <div style="padding: 1.4rem 1rem 1.1rem; border-bottom: 1px solid var(--scd-sidebar-border);">
        <a href="{{ route('dashboard') }}" style="display:flex;align-items:center;gap:.65rem;text-decoration:none;" title="Visão Geral">
            {{-- Ícone redondo do logo —  inspirado no avatar circular da imagem --}}
            <div style="width:38px;height:38px;border-radius:50%;background:var(--scd-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-shield-lock-fill" style="color:#fff;font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-size:1.05rem;font-weight:700;color:#fff;line-height:1.1;">SCD</div>
                <div style="font-size:.62rem;color:var(--scd-sidebar-text);opacity:.6;">Criptografia de Dados</div>
            </div>
        </a>
    </div>

    {{-- Navegação principal --}}
    <nav style="flex:1;padding:.85rem .7rem;overflow-y:auto;">

        <div class="nav-section-label">Principal</div>
        <a href="{{ route('dashboard') }}"
           class="nav-link-scd {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Visão Geral
        </a>

        <div class="nav-section-label">Ficheiros</div>
        <a href="{{ route('files.index') }}" class="nav-link-scd {{ request()->routeIs('files.*') ? 'active' : '' }}">
            <i class="bi bi-folder2-open"></i> Os Meus Ficheiros
        </a>
        <a href="{{ route('shared.index') }}" class="nav-link-scd {{ request()->routeIs('shared.*') ? 'active' : '' }}">
            <i class="bi bi-share"></i> Partilhados Comigo
        </a>

        @can('admin-or-manager')
        <div class="nav-section-label">Gestão</div>
        <a href="{{ route('users.index') }}" class="nav-link-scd {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Utilizadores
        </a>
        <a href="{{ route('departments.index') }}" class="nav-link-scd {{ request()->routeIs('departments.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Departamentos
        </a>
        @endcan

        @can('view-audit-logs')
        <div class="nav-section-label">Segurança</div>
        <a href="{{ route('audit.index') }}" class="nav-link-scd {{ request()->routeIs('audit.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> Auditoria
        </a>
        @endcan

        @can('admin-only')
        <a href="{{ route('backups.index') }}" class="nav-link-scd {{ request()->routeIs('backups.*') ? 'active' : '' }}">
            <i class="bi bi-database-gear"></i> Backups
        </a>
        @endcan

        <div class="nav-section-label">Conta</div>
        <a href="{{ route('profile.show') }}"
           class="nav-link-scd {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> Perfil & Segurança
        </a>

    </nav>

    {{-- Utilizador autenticado no fundo da sidebar --}}
    <div style="padding:.9rem 1rem; border-top:1px solid var(--scd-sidebar-border);">
        {{-- Botão "Adicionar Ficheiro" (inspirado no "Add files" da imagem) --}}
        <a href="{{ route('files.index') }}"
           style="display:flex;align-items:center;gap:.55rem;padding:.7rem .9rem;border:1.5px dashed rgba(255,255,255,.2);border-radius:10px;text-decoration:none;color:var(--scd-sidebar-text);font-size:.82rem;font-weight:500;transition:background .15s;margin-bottom:.85rem;"
           onmouseover="this.style.background='rgba(255,255,255,.07)'" onmouseout="this.style.background='transparent'">
            <div style="width:26px;height:26px;border-radius:6px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-plus" style="font-size:.9rem;"></i>
            </div>
            <div>
                <div style="font-size:.8rem;">Adicionar Ficheiros</div>
                <div style="font-size:.65rem;opacity:.5;">Até 50 MB</div>
            </div>
        </a>

        {{-- Info do utilizador --}}
        <div style="display:flex;align-items:center;gap:.6rem;">
            {{-- Avatar com inicial do nome --}}
            <div style="width:32px;height:32px;border-radius:50%;background:var(--scd-primary);display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div style="min-width:0;flex:1;">
                <div style="font-size:.8rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ auth()->user()->name }}
                </div>
                <div style="font-size:.68rem;color:var(--scd-sidebar-text);opacity:.55;">
                    @if(auth()->user()->isAdmin())
                        Administrador
                    @elseif(auth()->user()->isManager())
                        Gestor
                    @else
                        Funcionário
                    @endif
                </div>
            </div>
            {{-- Botão logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        style="background:transparent;border:none;color:rgba(203,213,224,.55);cursor:pointer;padding:.3rem;font-size:.9rem;line-height:1;transition:color .15s;"
                        onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(203,213,224,.55)'"
                        title="Terminar sessão">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ════════════════════════════════════════════════
     CONTEÚDO PRINCIPAL
════════════════════════════════════════════════ --}}
<div class="scd-main">

    {{-- Topbar / Barra superior --}}
    <header class="scd-topbar">
        {{-- Botão menu mobile --}}
        <button class="btn-scd-ghost d-lg-none me-3"
                id="sidebarToggle"
                style="padding:.35rem .5rem;font-size:1.1rem;border:none;"
                aria-label="Abrir menu">
            <i class="bi bi-list"></i>
        </button>

        {{-- Título da página --}}
        <h1 style="font-size:.95rem;font-weight:600;color:var(--scd-text);margin:0;flex:1;">
            @yield('page-title', 'Visão Geral')
        </h1>

        {{-- Ações da direita --}}
        <div style="display:flex;align-items:center;gap:.75rem;">

            {{-- Indicador de 2FA --}}
            @if(auth()->user()->hasVerified2FA())
                <span title="2FA activo" style="font-size:.85rem;color:var(--scd-primary);">
                    <i class="bi bi-shield-check-fill"></i>
                </span>
            @else
                <a href="{{ route('two-factor.setup') }}" title="Activar 2FA"
                   style="font-size:.85rem;color:var(--scd-warning);text-decoration:none;">
                    <i class="bi bi-shield-exclamation"></i>
                </a>
            @endif

            {{-- Separador vertical --}}
            <div style="width:1px;height:20px;background:var(--scd-border);"></div>

            {{-- Avatar do utilizador com link para o perfil --}}
            <a href="{{ route('profile.show') }}"
               style="width:32px;height:32px;border-radius:50%;background:var(--scd-primary);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#fff;text-decoration:none;flex-shrink:0;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </a>
        </div>
    </header>

    {{-- Mensagens flash --}}
    <div style="padding:.85rem 1.5rem 0;">
        @if (session('status'))
            <div class="alert-scd-success anim-fade-up" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert-scd-danger anim-fade-up" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Conteúdo da página --}}
    <main class="scd-content anim-fade-up">
        @yield('content')
    </main>

</div>{{-- /.scd-main --}}

{{-- Bootstrap JS — offline --}}
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script>
    // Toggle da sidebar no mobile
    const sidebar = document.getElementById('scdSidebar');
    const toggle  = document.getElementById('sidebarToggle');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
        document.addEventListener('click', e => {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }
</script>
@stack('scripts')
</body>
</html>
