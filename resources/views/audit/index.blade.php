@extends('layouts.app')

@section('title', 'Logs de Auditoria')

@section('content')
<div class="container py-4">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1 text-scd-primary fw-bold"><i class="bi bi-journal-text text-primary me-2"></i>Auditoria</h1>
            <p class="text-muted mb-0">Registo imutável de todos os eventos de segurança do sistema.</p>
        </div>
        <span class="badge bg-primary bg-opacity-20 text-primary rounded-pill px-3 py-2 align-self-start align-self-sm-center">
            {{ $logs->total() }} eventos
        </span>
    </div>

    {{-- Filters --}}
    <div class="card bg-scd-surface border border-secondary shadow mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('audit.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label text-muted small mb-1">Utilizador</label>
                    <select name="user_id" class="form-select form-select-sm bg-scd-surface text-scd-primary border-secondary">
                        <option value="">Todos</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label text-muted small mb-1">Acção</label>
                    <select name="action" class="form-select form-select-sm bg-scd-surface text-scd-primary border-secondary">
                        <option value="">Todas</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small mb-1">De</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="form-control form-control-sm bg-scd-surface text-scd-primary border-secondary">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small mb-1">Até</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="form-control form-control-sm bg-scd-surface text-scd-primary border-secondary">
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                    <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card bg-scd-surface border-0 shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table  table-hover mb-0 align-middle" style="font-size:.85rem;">
                    <thead class="border-bottom border-secondary">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold">Data / Hora</th>
                            <th class="py-3 text-muted fw-semibold">Utilizador</th>
                            <th class="py-3 text-muted fw-semibold">Acção</th>
                            <th class="py-3 text-muted fw-semibold">Entidade</th>
                            <th class="py-3 text-muted fw-semibold">IP</th>
                            <th class="pe-4 py-3 text-muted fw-semibold">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        @php
                            $actionColors = [
                                'login'              => 'success',
                                'logout'             => 'secondary',
                                'login_failed'       => 'warning',
                                'account_locked'     => 'danger',
                                '2fa_verified'       => 'info',
                                '2fa_failed'         => 'warning',
                                '2fa_enabled'        => 'success',
                                '2fa_disabled'       => 'secondary',
                                'file_uploaded'      => 'primary',
                                'file_downloaded'    => 'info',
                                'file_deleted'       => 'danger',
                                'user_created'       => 'success',
                                'user_updated'       => 'primary',
                                'account_deactivated'=> 'danger',
                                'account_activated'  => 'success',
                            ];
                            $color = $actionColors[$log->action] ?? 'secondary';
                        @endphp
                        <tr>
                            <td class="ps-4 py-2 text-muted" style="white-space:nowrap;">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-2">
                                @if($log->user)
                                    <div class="fw-semibold text-scd-primary">{{ $log->user->name }}</div>
                                    <small class="text-muted">{{ $log->user->email }}</small>
                                @else
                                    <span class="text-muted">Sistema</span>
                                @endif
                            </td>
                            <td class="py-2">
                                <span class="badge bg-{{ $color }} bg-opacity-20 text-{{ $color }} rounded-pill px-3">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-2 text-muted" style="font-size:.78rem;">
                                @if($log->entity_type)
                                    {{ class_basename($log->entity_type) }}
                                    @if($log->entity_id)
                                        <span class="text-muted opacity-50">#{{ Str::limit($log->entity_id, 8) }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-2 text-muted" style="font-size:.78rem;">{{ $log->ip_address ?? '—' }}</td>
                            <td class="pe-4 py-2">
                                @if($log->metadata)
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0"
                                            data-bs-toggle="collapse" data-bs-target="#meta-{{ $log->id }}">
                                        <i class="bi bi-chevron-down" style="font-size:.7rem;"></i>
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @if($log->metadata)
                        <tr class="collapse" id="meta-{{ $log->id }}">
                            <td colspan="6" class="px-4 pb-3 pt-0 bg-scd-surface">
                                <pre class="text-muted small mb-0 p-2 rounded"
                                     style="background:rgba(255,255,255,.04);font-size:.75rem;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x display-4 d-block mb-3 opacity-25"></i>
                                Nenhum evento encontrado com os filtros seleccionados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-scd-surface border-top border-secondary py-3">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
