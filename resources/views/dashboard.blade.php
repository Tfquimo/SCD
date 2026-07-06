@extends('layouts.app')

@section('title', 'Visão Geral')
@section('page-title', 'Visão Geral')

@section('content')

{{-- ── Boas-vindas ──────────────────────────────────────────────────── --}}
<div class="scd-card p-4 mb-4 anim-fade-up" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
    <div>
        <h2 style="font-size:1.5rem;font-weight:700;margin:0 0 .3rem;color:var(--scd-text);">
            Bem-vindo, {{ auth()->user()->name }}
        </h2>
        <p style="margin:0;font-size:.87rem;color:var(--scd-text-muted);">
            {{ now()->format('l, d \d\e F \d\e Y') }} &mdash; Sistema SCD operacional.
        </p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 mt-md-0 justify-content-start justify-content-md-end">
        <a href="{{ route('files.index') }}" class="btn-scd-primary" style="text-decoration:none;">
            <i class="bi bi-cloud-upload"></i> Upload Seguro
        </a>
        @if(!auth()->user()->hasVerified2FA())
        <a href="{{ route('two-factor.setup') }}"
           style="display:flex;align-items:center;gap:.4rem;padding:.5rem 1.1rem;background:rgba(237,137,54,.1);border:1px solid rgba(237,137,54,.25);border-radius:var(--scd-radius);color:var(--scd-warning);text-decoration:none;font-size:.85rem;font-weight:500;">
            <i class="bi bi-shield-exclamation"></i> Activar 2FA
        </a>
        @endif
    </div>
</div>

{{-- ── Grelha de estatísticas gerais ───────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.5rem;">
    @php
        $stats = [
            ['icon'=>'bi-file-earmark-lock2-fill', 'label'=>'Ficheiros Cifrados',   'value'=>$totalFiles ?? '0',   'color'=>'var(--scd-primary)', 'bg'=>'rgba(126,184,164,.12)'],
            ['icon'=>'bi-hdd-fill',                'label'=>'Armazenamento Usado',   'value'=>$storageUsed ?? '0 B','color'=>'var(--scd-accent)',  'bg'=>'rgba(91,79,207,.10)'],
            ['icon'=>'bi-journal-text',            'label'=>'Eventos de Auditoria', 'value'=>$auditEvents ?? '0',  'color'=>'var(--scd-warning)', 'bg'=>'rgba(237,137,54,.10)'],
            ['icon'=>'bi-shield-check-fill',       'label'=>'Backups Realizados',   'value'=>$totalBackups ?? '0', 'color'=>'var(--scd-primary)', 'bg'=>'rgba(126,184,164,.12)'],
        ];
    @endphp
    @foreach($stats as $i => $stat)
    <div class="scd-card p-3 anim-fade-up" style="animation-delay:{{ $i * 0.06 }}s;">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <div style="width:40px;height:40px;border-radius:10px;background:{{ $stat['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi {{ $stat['icon'] }}" style="font-size:1rem;color:{{ $stat['color'] }};"></i>
            </div>
            <div>
                <div style="font-size:1.4rem;font-weight:700;line-height:1;color:var(--scd-text);">{{ $stat['value'] }}</div>
                <div style="font-size:.73rem;color:var(--scd-text-muted);margin-top:.15rem;">{{ $stat['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     PAINEL CRIPTOGRÁFICO — Secção principal do TCC
═══════════════════════════════════════════════════════════════════════ --}}
<div class="scd-card p-4 anim-fade-up mb-4" style="animation-delay:.1s;border:1px solid rgba(126,184,164,.25);background:linear-gradient(135deg,rgba(126,184,164,.04) 0%,rgba(91,79,207,.04) 100%);">

    {{-- Cabeçalho do painel --}}
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--scd-border);">
        <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--scd-primary),var(--scd-accent));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi bi-shield-lock-fill" style="color:#fff;font-size:1.1rem;"></i>
        </div>
        <div>
            <h3 style="margin:0;font-size:1rem;font-weight:700;color:var(--scd-text);">Painel Criptográfico</h3>
            <p style="margin:0;font-size:.78rem;color:var(--scd-text-muted);">Estado em tempo real da arquitectura de criptografia do sistema</p>
        </div>
        <div style="margin-left:auto;">
            <span style="display:inline-flex;align-items:center;gap:.35rem;font-size:.75rem;font-weight:600;padding:.3rem .75rem;background:rgba(126,184,164,.15);border:1px solid rgba(126,184,164,.35);border-radius:20px;color:var(--scd-primary);">
                <span style="width:7px;height:7px;border-radius:50%;background:var(--scd-primary);animation:pulse 2s ease-in-out infinite;"></span>
                Sistema Seguro
            </span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%, 280px),1fr));gap:1.25rem;">

        {{-- ── Algoritmo de Encriptação ─────────────────────────── --}}
        <div style="background:var(--scd-surface);border:1px solid var(--scd-border);border-radius:var(--scd-radius);padding:1.1rem;">
            <div style="font-size:.75rem;font-weight:600;color:var(--scd-text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.85rem;">
                <i class="bi bi-cpu me-1"></i> Algoritmo de Encriptação
            </div>
            <div style="display:flex;flex-direction:column;gap:.6rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:.84rem;color:var(--scd-text-muted);">Cifra</span>
                    <span style="font-size:.84rem;font-weight:700;color:var(--scd-text);font-family:monospace;background:rgba(126,184,164,.12);padding:.15rem .5rem;border-radius:4px;">AES-256-CBC</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:.84rem;color:var(--scd-text-muted);">Integridade</span>
                    <span style="font-size:.84rem;font-weight:700;color:var(--scd-text);font-family:monospace;background:rgba(91,79,207,.10);padding:.15rem .5rem;border-radius:4px;">HMAC-SHA-256</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:.84rem;color:var(--scd-text-muted);">Padrão</span>
                    <span style="font-size:.84rem;font-weight:700;color:var(--scd-text);font-family:monospace;background:rgba(237,137,54,.10);padding:.15rem .5rem;border-radius:4px;">Encrypt-Then-MAC</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:.84rem;color:var(--scd-text-muted);">Chave (tamanho)</span>
                    <span style="font-size:.84rem;font-weight:700;color:var(--scd-text);font-family:monospace;background:rgba(126,184,164,.12);padding:.15rem .5rem;border-radius:4px;">256 bits</span>
                </div>
            </div>
        </div>

        {{-- ── Envelope Encryption (DEK/KEK) ────────────────────── --}}
        <div style="background:var(--scd-surface);border:1px solid var(--scd-border);border-radius:var(--scd-radius);padding:1.1rem;">
            <div style="font-size:.75rem;font-weight:600;color:var(--scd-text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.85rem;">
                <i class="bi bi-key-fill me-1"></i> Envelope Encryption (DEK/KEK)
            </div>
            {{-- Diagrama visual DEK/KEK --}}
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;font-size:.78rem;">
                <div style="flex:1;text-align:center;background:rgba(126,184,164,.12);border:1px solid rgba(126,184,164,.3);border-radius:8px;padding:.5rem .4rem;">
                    <div style="font-size:.65rem;color:var(--scd-text-muted);margin-bottom:.2rem;">FICHEIRO</div>
                    <i class="bi bi-file-earmark-binary" style="font-size:1.2rem;color:var(--scd-primary);"></i>
                </div>
                <div style="color:var(--scd-primary);font-size:1rem;">→</div>
                <div style="flex:1;text-align:center;background:rgba(91,79,207,.10);border:1px solid rgba(91,79,207,.25);border-radius:8px;padding:.5rem .4rem;">
                    <div style="font-size:.65rem;color:var(--scd-text-muted);margin-bottom:.2rem;">DEK</div>
                    <i class="bi bi-key" style="font-size:1.2rem;color:var(--scd-accent);"></i>
                </div>
                <div style="color:var(--scd-accent);font-size:1rem;">→</div>
                <div style="flex:1;text-align:center;background:rgba(237,137,54,.10);border:1px solid rgba(237,137,54,.25);border-radius:8px;padding:.5rem .4rem;">
                    <div style="font-size:.65rem;color:var(--scd-text-muted);margin-bottom:.2rem;">KEK</div>
                    <i class="bi bi-shield-lock" style="font-size:1.2rem;color:var(--scd-warning);"></i>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:.55rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:.83rem;color:var(--scd-text-muted);">DEKs activas</span>
                    <span style="font-size:1rem;font-weight:700;color:var(--scd-primary);">{{ $deksActive ?? 0 }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:.83rem;color:var(--scd-text-muted);">Ficheiros legados</span>
                    <span style="font-size:1rem;font-weight:700;color:var(--scd-warning);">{{ $filesLegacy ?? 0 }}</span>
                </div>
                @if(($filesLegacy ?? 0) > 0)
                <div style="margin-top:.25rem;padding:.4rem .6rem;background:rgba(237,137,54,.08);border-radius:6px;font-size:.75rem;color:var(--scd-warning);">
                    <i class="bi bi-info-circle me-1"></i> Ficheiros criados antes do Envelope Encryption.
                </div>
                @endif
            </div>
        </div>

        {{-- ── Estado de Segurança ───────────────────────────────── --}}
        <div style="background:var(--scd-surface);border:1px solid var(--scd-border);border-radius:var(--scd-radius);padding:1.1rem;">
            <div style="font-size:.75rem;font-weight:600;color:var(--scd-text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.85rem;">
                <i class="bi bi-shield-fill-check me-1"></i> Estado de Segurança
            </div>
            <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.55rem;">
                <li style="display:flex;align-items:center;justify-content:space-between;font-size:.83rem;">
                    <span style="color:var(--scd-text-muted);">Criptografia AES-256</span>
                    <span style="color:var(--scd-primary);font-weight:600;"><i class="bi bi-check-circle-fill me-1"></i>Activa</span>
                </li>
                <li style="display:flex;align-items:center;justify-content:space-between;font-size:.83rem;">
                    <span style="color:var(--scd-text-muted);">Verificação HMAC</span>
                    <span style="color:var(--scd-primary);font-weight:600;"><i class="bi bi-check-circle-fill me-1"></i>Activa</span>
                </li>
                <li style="display:flex;align-items:center;justify-content:space-between;font-size:.83rem;">
                    <span style="color:var(--scd-text-muted);">Autenticação 2FA</span>
                    @if(auth()->user()->hasVerified2FA())
                        <span style="color:var(--scd-primary);font-weight:600;"><i class="bi bi-check-circle-fill me-1"></i>Activa</span>
                    @else
                        <a href="{{ route('two-factor.setup') }}" style="color:var(--scd-warning);font-weight:600;text-decoration:none;font-size:.83rem;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>Activar
                        </a>
                    @endif
                </li>
                <li style="display:flex;align-items:center;justify-content:space-between;font-size:.83rem;">
                    <span style="color:var(--scd-text-muted);">Rate Limiting</span>
                    <span style="color:var(--scd-primary);font-weight:600;"><i class="bi bi-check-circle-fill me-1"></i>Activo</span>
                </li>
                <li style="display:flex;align-items:center;justify-content:space-between;font-size:.83rem;">
                    <span style="color:var(--scd-text-muted);">Log de Auditoria</span>
                    <span style="color:var(--scd-primary);font-weight:600;"><i class="bi bi-check-circle-fill me-1"></i>Activo</span>
                </li>
                <li style="display:flex;align-items:center;justify-content:space-between;font-size:.83rem;">
                    <span style="color:var(--scd-text-muted);">Alertas (7 dias)</span>
                    @if(($securityEvents ?? 0) > 0)
                        <span style="color:var(--scd-danger,#e53e3e);font-weight:700;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $securityEvents }}
                        </span>
                    @else
                        <span style="color:var(--scd-primary);font-weight:600;"><i class="bi bi-check-circle-fill me-1"></i>Nenhum</span>
                    @endif
                </li>
            </ul>
        </div>

        @if(auth()->user()->hasRole('Admin'))
        {{-- ── Utilizadores (Admin) ──────────────────────────────── --}}
        <div style="background:var(--scd-surface);border:1px solid var(--scd-border);border-radius:var(--scd-radius);padding:1.1rem;">
            <div style="font-size:.75rem;font-weight:600;color:var(--scd-text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.85rem;">
                <i class="bi bi-people-fill me-1"></i> Utilizadores do Sistema
            </div>
            <div style="display:flex;gap:.75rem;margin-bottom:.85rem;">
                <div style="flex:1;text-align:center;background:rgba(126,184,164,.10);border-radius:10px;padding:.75rem .5rem;">
                    <div style="font-size:1.6rem;font-weight:800;color:var(--scd-primary);">{{ $activeUsers ?? 0 }}</div>
                    <div style="font-size:.7rem;color:var(--scd-text-muted);margin-top:.15rem;">Activos</div>
                </div>
                <div style="flex:1;text-align:center;background:rgba(237,137,54,.10);border-radius:10px;padding:.75rem .5rem;">
                    <div style="font-size:1.6rem;font-weight:800;color:var(--scd-warning);">{{ $lockedUsers ?? 0 }}</div>
                    <div style="font-size:.7rem;color:var(--scd-text-muted);margin-top:.15rem;">Bloqueados</div>
                </div>
            </div>
            <a href="{{ route('users.index') }}" style="display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.5rem;background:rgba(126,184,164,.08);border:1px solid rgba(126,184,164,.2);border-radius:8px;font-size:.8rem;color:var(--scd-primary);text-decoration:none;font-weight:600;">
                <i class="bi bi-arrow-right-circle"></i> Gerir Utilizadores
            </a>
        </div>
        @endif

    </div>
</div>

{{-- ── Gráfico real de uploads por dia ─────────────────────────────── --}}
<div class="scd-card p-4 anim-fade-up mb-4" style="animation-delay:.18s;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <h3 style="font-size:.95rem;font-weight:600;margin:0;color:var(--scd-text);">
            <i class="bi bi-graph-up-arrow" style="color:var(--scd-primary);margin-right:.5rem;"></i>Uploads Cifrados — Últimos 7 dias
        </h3>
        <span style="font-size:.75rem;color:var(--scd-text-muted);">Ficheiros por dia</span>
    </div>
    <div style="position:relative;height:200px;width:100%;">
        <canvas id="storageChart"></canvas>
    </div>
</div>

{{-- ── Actividade recente ───────────────────────────────────────────── --}}
<div class="scd-card anim-fade-up" style="animation-delay:.25s;">
    <div style="padding:1rem 1.2rem;border-bottom:1px solid var(--scd-border);display:flex;align-items:center;justify-content:space-between;">
        <h3 style="font-size:.88rem;font-weight:600;margin:0;color:var(--scd-text);">
            <i class="bi bi-clock-history me-1" style="color:var(--scd-text-muted);"></i> Actividade Recente
        </h3>
        <a href="{{ route('audit.index') }}" style="font-size:.78rem;color:var(--scd-accent);text-decoration:none;">Ver tudo</a>
    </div>
    <div style="padding:.4rem 0;">
        @forelse($recentActivities ?? [] as $activity)
            @php
                $iconMap = [
                    'file_uploaded'           => ['bi-cloud-upload-fill', 'rgba(126,184,164,.12)', '#7EB8A4'],
                    'file_downloaded'         => ['bi-cloud-download-fill','rgba(91,79,207,.10)',  '#5B4FCF'],
                    'file_deleted'            => ['bi-trash3-fill',        'rgba(229,62,62,.10)',   '#e53e3e'],
                    'file_shared'             => ['bi-share-fill',         'rgba(237,137,54,.10)', '#ED8936'],
                    'shared_file_downloaded'  => ['bi-cloud-arrow-down-fill','rgba(91,79,207,.10)','#5B4FCF'],
                    'login'                   => ['bi-box-arrow-in-right', 'rgba(126,184,164,.12)','#7EB8A4'],
                    'login_failed'            => ['bi-x-circle-fill',      'rgba(229,62,62,.10)',   '#e53e3e'],
                    'account_locked'          => ['bi-lock-fill',          'rgba(237,137,54,.10)', '#ED8936'],
                    'password_reset'          => ['bi-key-fill',           'rgba(91,79,207,.10)',  '#5B4FCF'],
                    'two_factor_enabled'      => ['bi-shield-check-fill',  'rgba(126,184,164,.12)','#7EB8A4'],
                ];
                [$icon, $bg, $color] = $iconMap[$activity->action] ?? ['bi-activity', 'rgba(126,184,164,.12)', '#7EB8A4'];
            @endphp
            <div style="padding:.65rem 1.2rem;display:flex;align-items:center;gap:.8rem;font-size:.83rem;border-bottom:1px solid var(--scd-border);">
                <div style="width:34px;height:34px;border-radius:8px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi {{ $icon }}" style="color:{{ $color }};font-size:.85rem;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:500;color:var(--scd-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                    </div>
                    <div style="font-size:.73rem;color:var(--scd-text-muted);">
                        {{ $activity->user?->name ?? 'Sistema' }}
                    </div>
                </div>
                <span style="font-size:.72rem;color:var(--scd-text-muted);white-space:nowrap;">
                    {{ $activity->created_at->diffForHumans() }}
                </span>
            </div>
        @empty
            <div style="text-align:center;padding:2rem;color:var(--scd-text-muted);font-size:.82rem;">
                <i class="bi bi-clock-history" style="font-size:1.6rem;display:block;margin-bottom:.5rem;opacity:.25;"></i>
                Sem actividade recente a registar.
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('storageChart').getContext('2d');

    // Dados reais do servidor
    const labels = @json($weeklyLabels ?? []);
    const data   = @json($weeklyUploads ?? []);

    let gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(126,184,164, 0.5)');
    gradient.addColorStop(1, 'rgba(126,184,164, 0)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ficheiros Cifrados',
                data: data,
                backgroundColor: gradient,
                borderColor: '#7EB8A4',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1A202C',
                    titleFont: { family: 'Inter', size: 12 },
                    bodyFont: { family: 'Inter', size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(c) { return c.raw + ' ficheiro(s) cifrado(s)'; }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: { family: 'Inter', size: 11 },
                        color: '#718096'
                    },
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#718096' }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
