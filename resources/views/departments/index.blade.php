@extends('layouts.app')

@section('title', 'Departamentos')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-scd-primary fw-bold"><i class="bi bi-building text-primary me-2"></i>Departamentos</h1>
            <p class="text-muted mb-0">Organização estrutural da empresa e gestores responsáveis.</p>
        </div>
        @can('manage-users')
        <a href="{{ route('departments.create') }}" class="btn btn-primary px-4">
            <i class="bi bi-plus-lg me-2"></i>Novo Departamento
        </a>
        @endcan
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show border-0 bg-success text-scd-primary" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close " data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 bg-danger text-scd-primary">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="row g-3">
        @forelse($departments as $dept)
        <div class="col-md-6 col-xl-4">
            <div class="card bg-scd-surface border border-secondary h-100 shadow">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;background:rgba(99,102,241,.15);flex-shrink:0;">
                                <i class="bi bi-building-fill text-primary fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 text-scd-primary fw-semibold">{{ $dept->name }}</h5>
                                <small class="text-muted">{{ $dept->users_count }} utilizador(es)</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Gestor responsável</small>
                        @if($dept->manager)
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-scd-primary fw-bold"
                                     style="width:28px;height:28px;background:linear-gradient(135deg,#6366f1,#22d3ee);font-size:.7rem;flex-shrink:0;">
                                    {{ strtoupper(substr($dept->manager->name,0,1)) }}
                                </div>
                                <span class="text-scd-primary small">{{ $dept->manager->name }}</span>
                            </div>
                        @else
                            <span class="text-muted small"><i class="bi bi-dash"></i> Não atribuído</span>
                        @endif
                    </div>

                    @can('manage-users')
                    <div class="d-flex gap-2">
                        <a href="{{ route('departments.edit', $dept) }}"
                           class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-fill">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>
                        <form action="{{ route('departments.destroy', $dept) }}" method="POST"
                              onsubmit="return confirm('Eliminar o departamento «{{ addslashes($dept->name) }}»?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card bg-scd-surface border border-secondary text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-building display-4 d-block mb-3 opacity-25"></i>
                    Nenhum departamento criado ainda.
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($departments->hasPages())
    <div class="mt-4">{{ $departments->links() }}</div>
    @endif
</div>
@endsection
