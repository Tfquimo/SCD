@extends('layouts.app')
@section('title', 'Novo Departamento')
@section('content')
<div class="container py-4" style="max-width:580px;">
    <div class="mb-4">
        <a href="{{ route('departments.index') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
        <h1 class="h3 mt-2 mb-1 text-scd-primary fw-bold">Novo Departamento</h1>
    </div>
    @if($errors->any())
        <div class="alert alert-danger border-0 bg-danger bg-opacity-20 text-danger mb-4">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif
    <div class="card bg-scd-surface border border-secondary shadow-lg">
        <div class="card-body p-4">
            <form action="{{ route('departments.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="form-label text-muted">Nome do Departamento *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="form-control bg-scd-surface text-scd-primary border-secondary @error('name') is-invalid @enderror"
                           placeholder="Ex: Recursos Humanos" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label for="manager_id" class="form-label text-muted">Gestor Responsável</label>
                    <select id="manager_id" name="manager_id"
                            class="form-select bg-scd-surface text-scd-primary border-secondary @error('manager_id') is-invalid @enderror">
                        <option value="">— Não atribuído —</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}" {{ old('manager_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->name }} ({{ $m->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3 mt-2">
                    <button type="submit" class="btn btn-primary px-5"><i class="bi bi-plus-lg me-2"></i>Criar</button>
                    <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary px-4 text-center">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
