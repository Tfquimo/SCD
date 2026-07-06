@extends('layouts.app')

@section('title', 'Gestão de Cópias de Segurança')
@section('page-title', 'Cópias de Segurança (Backups)')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 text-scd-primary fw-bold">Cópias de Segurança</h1>
            <p class="text-muted mb-0">Criação, descarga e gestão de arquivos ZIP de backup do sistema (Base de Dados e Ficheiros Encriptados).</p>
        </div>
        <form action="{{ route('backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary px-4 py-2 w-100 w-md-auto" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm\' role=\'status\' aria-hidden=\'true\'></span> A Gerar Backup...'; this.disabled=true; this.form.submit();">
                <i class="bi bi-database-add me-2"></i> Criar Cópia de Segurança
            </button>
        </form>
    </div>

    <div class="alert alert-info bg-info bg-opacity-10 border-0 text-info mb-4">
        <i class="bi bi-info-circle-fill me-2"></i> <strong>Nota de Segurança:</strong> Os backups são gerados como arquivos ZIP e contêm todos os ficheiros da diretoria de armazenamento (os quais permanecem encriptados por segurança) e a base de dados. Guarde os seus backups descarregados num local seguro e fora da máquina servidora.
    </div>

    <div class="card bg-scd-surface border-0 shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table  table-hover mb-0 align-middle">
                    <thead class="-header border-bottom border-secondary">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold">Nome do Ficheiro</th>
                            <th class="py-3 text-muted fw-semibold">Tamanho</th>
                            <th class="py-3 text-muted fw-semibold">Data de Criação</th>
                            <th class="pe-4 py-3 text-muted fw-semibold text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="file-icon bg-warning bg-opacity-10 text-warning p-2 rounded-3 me-3">
                                            <i class="bi bi-file-zip-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-scd-primary">{{ $backup['name'] }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-scd-primary">
                                    @if($backup['size'] >= 1048576)
                                        {{ number_format($backup['size'] / 1048576, 2) }} MB
                                    @else
                                        {{ number_format($backup['size'] / 1024, 2) }} KB
                                    @endif
                                </td>
                                <td class="py-3 text-scd-primary">
                                    {{ $backup['created_at']->format('d/m/Y H:i:s') }} 
                                    <small class="text-muted">({{ $backup['created_at']->diffForHumans() }})</small>
                                </td>
                                <td class="pe-4 py-3">
                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                        <a href="{{ route('backups.download', $backup['name']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-download"></i> <span class="d-none d-sm-inline">Descarregar</span>
                                        </a>
                                        <form action="{{ route('backups.destroy', $backup['name']) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja eliminar permanentemente esta cópia de segurança? Esta acção não pode ser revertida.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="empty-state text-muted">
                                        <i class="bi bi-database-slash display-4 mb-3 d-block"></i>
                                        <h5>Nenhuma cópia de segurança encontrada</h5>
                                        <p>Clique no botão acima para gerar a sua primeira cópia de segurança.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
