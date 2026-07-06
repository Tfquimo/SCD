@extends('layouts.app')

@section('title', 'Editar Utilizador')

@section('content')
<div class="container py-4" style="max-width:680px;">
    <div class="mb-4">
        <a href="{{ route('users.index') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>Voltar à lista
        </a>
        <h1 class="h3 mt-2 mb-1 text-scd-primary fw-bold">Editar Utilizador</h1>
        <p class="text-muted mb-0">A editar: <span class="fw-semibold text-scd-text">{{ $user->name }}</span></p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 bg-danger bg-opacity-20 text-danger mb-4">
            @foreach($errors->all() as $e)<div><i class="bi bi-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card bg-scd-surface border border-secondary shadow-lg">
        <div class="card-body p-4">
            <form action="{{ route('users.update', $user) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-4">
                    <label for="name" class="form-label text-muted">Nome Completo *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                           class="form-control bg-scd-surface text-scd-primary border-secondary @error('name') is-invalid @enderror"
                           required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="form-label text-muted">E-mail *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-control bg-scd-surface text-scd-primary border-secondary @error('email') is-invalid @enderror"
                           required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label for="password" class="form-label text-muted">Nova Senha <span class="text-muted fw-normal">(deixar vazio para manter)</span></label>
                    <input type="password" id="password" name="password"
                           class="form-control bg-scd-surface text-scd-primary border-secondary @error('password') is-invalid @enderror"
                           placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Role --}}
                <div class="mb-4">
                    <label for="role" class="form-label text-muted">Nível de Acesso *</label>
                    <select id="role" name="role"
                            class="form-select bg-scd-surface text-scd-primary border-secondary @error('role') is-invalid @enderror" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}"
                                {{ (old('role', $user->roles->first()?->name) === $role->name) ? 'selected' : '' }}>
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
                            <option value="{{ $dept->id }}"
                                {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info mt-4 mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Para activar ou desactivar a conta, utilize os botões na listagem de utilizadores.
                    @if(!$user->two_factor_secret)
                        O utilizador <strong>não tem 2FA</strong> configurado.
                    @endif
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3 mt-4">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-save me-2"></i>Guardar Alterações
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-4 text-center">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
