@extends('layouts.app')

@section('title', 'Novo Utilizador')

@section('content')
<div class="container py-4" style="max-width:680px;">
    <div class="mb-4">
        <a href="{{ route('users.index') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>Voltar à lista
        </a>
        <h1 class="h3 mt-2 mb-1 text-scd-primary fw-bold">Criar Novo Utilizador</h1>
        <p class="text-muted mb-0">Preencha os campos abaixo. A senha deve ter no mínimo 8 caracteres.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 bg-danger bg-opacity-20 text-danger mb-4">
            @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card bg-scd-surface border border-secondary shadow-lg">
        <div class="card-body p-4">
            <form action="{{ route('users.store') }}" method="POST" autocomplete="off">
                @csrf

                {{-- Name --}}
                <div class="mb-4">
                    <label for="name" class="form-label text-muted">Nome Completo *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="form-control bg-scd-surface text-scd-primary border-secondary @error('name') is-invalid @enderror"
                           placeholder="Ex: João Silva" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="form-label text-muted">E-mail *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control bg-scd-surface text-scd-primary border-secondary @error('email') is-invalid @enderror"
                           placeholder="joao@empresa.pt" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label for="password" class="form-label text-muted">Senha *</label>
                    <input type="password" id="password" name="password"
                           class="form-control bg-scd-surface text-scd-primary border-secondary @error('password') is-invalid @enderror"
                           placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                    <div class="form-text text-muted">Deve conter maiúsculas, minúsculas, números e símbolos.</div>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Role --}}
                <div class="mb-4">
                    <label for="role" class="form-label text-muted">Nível de Acesso *</label>
                    <select id="role" name="role"
                            class="form-select bg-scd-surface text-scd-primary border-secondary @error('role') is-invalid @enderror" required>
                        <option value="">— Seleccionar —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                {{ $role->name == 'admin' ? 'Administrador' : ($role->name == 'manager' ? 'Gestor' : ($role->name == 'employee' ? 'Funcionário' : $role->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Department --}}
                <div class="mb-4">
                    <label for="department_id" class="form-label text-muted">Departamento</label>
                    <select id="department_id" name="department_id"
                            class="form-select bg-scd-surface text-scd-primary border-secondary @error('department_id') is-invalid @enderror">
                        <option value="">— Sem departamento —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Active --}}
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                               {{ old('active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="active">Conta activa</label>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-person-check-fill me-2"></i>Criar Utilizador
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
