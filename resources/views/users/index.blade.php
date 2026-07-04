@extends('layouts.app')

@section('title', 'Gestão de Utilizadores')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-scd-primary fw-bold"><i class="bi bi-people-fill text-primary me-2"></i>Utilizadores</h1>
            <p class="text-muted mb-0">Gestão de contas, permissões e departamentos.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form action="{{ route('users.index') }}" method="GET" class="d-flex align-items-center position-relative">
                <i class="bi bi-search position-absolute text-muted ms-3"></i>
                <input type="text" name="search" class="form-control rounded-pill ps-5" placeholder="Pesquisar utilizador..." value="{{ request('search') }}" style="width: 250px;">
            </form>
            @can('manage-users')
            <a href="{{ route('users.create') }}" class="btn btn-primary px-4 rounded-pill">
                <i class="bi bi-person-plus-fill me-2"></i>Novo
            </a>
            @endcan
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show border-0 bg-success text-scd-primary" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close " data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 bg-danger text-scd-primary">
            @foreach($errors->all() as $error)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="card bg-scd-surface border-0 shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table  table-hover mb-0 align-middle">
                    <thead class="border-bottom border-secondary">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold">Utilizador</th>
                            <th class="py-3 text-muted fw-semibold">Nível de Acesso</th>
                            <th class="py-3 text-muted fw-semibold">Departamento</th>
                            <th class="py-3 text-muted fw-semibold">Estado</th>
                            <th class="py-3 text-muted fw-semibold">2FA</th>
                            <th class="pe-4 py-3 text-muted fw-semibold text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr class="{{ $user->trashed() ? 'opacity-50' : '' }}">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-scd-primary"
                                         style="width:38px;height:38px;background:var(--scd-surface-2); border: 1px solid var(--scd-border);font-size:.85rem;flex-shrink:0;">
                                        {{ strtoupper(substr($user->name,0,1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-scd-primary">{{ $user->name }}</div>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                @foreach($user->roles as $role)
                                    @php
                                        $colors = ['admin'=>'danger','manager'=>'warning','employee'=>'secondary'];
                                        $c = $colors[$role->name] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $c }} bg-opacity-25 text-{{ $c }} rounded-pill px-3">{{ $role->name == 'admin' ? 'Administrador' : ($role->name == 'manager' ? 'Gestor' : ($role->name == 'employee' ? 'Funcionário' : $role->name)) }}</span>
                                @endforeach
                            </td>
                            <td class="py-3 text-scd-primary">{{ $user->department?->name ?? '—' }}</td>
                            <td class="py-3">
                                @if($user->trashed())
                                    <span class="badge bg-scd-surface border border-secondary text-muted rounded-pill px-3">Eliminado</span>
                                @elseif($user->isLocked())
                                    <span class="badge bg-danger bg-opacity-20 text-danger rounded-pill px-3"><i class="bi bi-lock-fill me-1"></i>Bloqueado</span>
                                @elseif($user->active)
                                    <span class="badge bg-success bg-opacity-20 text-success rounded-pill px-3"><i class="bi bi-check-circle me-1"></i>Activo</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-20 text-secondary rounded-pill px-3"><i class="bi bi-dash-circle me-1"></i>Inactivo</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($user->hasVerified2FA())
                                    <span class="text-success"><i class="bi bi-shield-fill-check"></i></span>
                                @else
                                    <span class="text-muted"><i class="bi bi-shield-slash"></i></span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 text-end">
                                @if(!$user->trashed())
                                @can('manage-users')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->active)
                                <form action="{{ route('users.deactivate', $user) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Desactivar a conta de {{ addslashes($user->name) }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('users.activate', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-4 d-block mb-3 opacity-25"></i>
                                Nenhum utilizador encontrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-scd-surface border-top border-secondary py-3">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
