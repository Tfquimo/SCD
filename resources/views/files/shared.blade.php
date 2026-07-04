@extends('layouts.app')

@section('title', 'Ficheiros Partilhados Comigo')
@section('page-title', 'Partilhados Comigo')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1 text-scd-primary fw-bold">Partilhados Comigo</h1>
        <p class="text-muted mb-0">Ficheiros de outros departamentos ou colegas partilhados com acesso seguro temporário ou permanente.</p>
    </div>

    <div class="card bg-scd-surface border-0 shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table  table-hover mb-0 align-middle">
                    <thead class="-header border-bottom border-secondary">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold">Nome do Ficheiro</th>
                            <th class="py-3 text-muted fw-semibold">Tamanho</th>
                            <th class="py-3 text-muted fw-semibold">Tipo</th>
                            <th class="py-3 text-muted fw-semibold">Partilhado Por</th>
                            <th class="py-3 text-muted fw-semibold">Data da Partilha</th>
                            <th class="pe-4 py-3 text-muted fw-semibold text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shares as $share)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="file-icon bg-success bg-opacity-10 text-success p-2 rounded-3 me-3">
                                            <i class="bi bi-file-earmark-lock2 fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-scd-primary">{{ $share->file->name }}</h6>
                                            <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;">{{ $share->file->original_name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-scd-primary">{{ number_format($share->file->size / 1024, 2) }} KB</td>
                                <td class="py-3">
                                    <span class="badge bg-secondary bg-opacity-25 text-scd-primary rounded-pill px-3 py-2 fw-normal">
                                        {{ Str::limit($share->file->mime_type, 20) }}
                                    </span>
                                </td>
                                <td class="py-3 text-scd-primary">
                                    {{ $share->sharedBy->name }}
                                    @if($share->department_id)
                                        <br><span class="badge bg-primary bg-opacity-10 text-primary mt-1" style="font-size: 0.7rem;">Via Departamento</span>
                                    @else
                                        <br><span class="badge bg-info bg-opacity-10 text-info mt-1" style="font-size: 0.7rem;">Diretamente</span>
                                    @endif
                                </td>
                                <td class="py-3 text-scd-primary">{{ $share->created_at->format('d/m/Y H:i') }}</td>
                                <td class="pe-4 py-3 text-end">
                                    <a href="{{ route('shared.download', $share->file) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                        <i class="bi bi-download me-1"></i> Decriptar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state text-muted">
                                        <i class="bi bi-share-fill display-4 mb-3 d-block text-secondary"></i>
                                        <h5>Nenhum ficheiro partilhado</h5>
                                        <p>Ficheiros partilhados por outros utilizadores aparecerão nesta secção.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($shares->hasPages())
            <div class="card-footer bg-scd-surface border-top border-secondary py-3">
                {{ $shares->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
