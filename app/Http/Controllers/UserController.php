<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * List all users (Admin sees all, Manager sees own department).
     * Lista todos os utilizadores consoante a permissão do autor da requisição.
     */
    public function index(Request $request)
    {
        Gate::authorize('admin-or-manager');

        $query = User::with(['roles', 'department'])->withTrashed();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->user()->hasRole('Gestor') && ! $request->user()->hasRole('Admin')) {
            $query->where('department_id', $request->user()->department_id);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Mostra o formulário de criação de um utilizador (apenas Admin).
     */
    public function create(Request $request)
    {
        Gate::authorize('manage-users');

        $departments = Department::orderBy('name')->get();
        
        $rolesQuery = Role::orderBy('name');
        if (! $request->user()->isAdmin()) {
            $rolesQuery->where('name', '!=', 'admin');
        }
        $roles = $rolesQuery->get();

        return view('users.create', compact('departments', 'roles'));
    }

    /**
     * Guarda um novo utilizador na base de dados (apenas Admin).
     */
    public function store(Request $request)
    {
        Gate::authorize('manage-users');

        if (! $request->user()->isAdmin() && $request->input('role') === 'admin') {
            abort(403, 'Acesso negado: Gestores não podem adicionar administradores.');
        }

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:180', 'unique:users,email'],
            'password'      => [
                'required', 
                // Definir mínimo de 8 caracteres e requisitos de segurança
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
            'role'          => ['required', 'string', 'exists:roles,name'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'active'        => ['boolean'],
        ]);

        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'role'          => $data['role'],
            'department_id' => $data['department_id'] ?? null,
            'active'        => $data['active'] ?? true,
        ]);

        $user->assignRole($data['role']);

        AuditLog::create([
            'user_id'     => $request->user()->id,
            'action'      => 'user_created',
            'entity_type' => User::class,
            'entity_id'   => $user->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'metadata'    => ['name' => $user->name, 'email' => $user->email, 'role' => $data['role']],
        ]);

        return redirect()->route('users.index')
            ->with('status', "Utilizador {$user->name} criado com sucesso.");
    }

    /**
     * Mostra o formulário de edição de um utilizador (apenas Admin).
     */
    public function edit(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        if (! $request->user()->isAdmin() && $user->isAdmin()) {
            abort(403, 'Acesso negado: Gestores não podem editar administradores.');
        }

        $departments = Department::orderBy('name')->get();
        
        $rolesQuery = Role::orderBy('name');
        if (! $request->user()->isAdmin()) {
            $rolesQuery->where('name', '!=', 'admin');
        }
        $roles = $rolesQuery->get();

        return view('users.edit', compact('user', 'departments', 'roles'));
    }

    /**
     * Actualiza os detalhes de um utilizador (apenas Admin).
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        if (! $request->user()->isAdmin()) {
            if ($user->isAdmin()) {
                abort(403, 'Acesso negado: Gestores não podem editar administradores.');
            }
            if ($request->input('role') === 'admin') {
                abort(403, 'Acesso negado: Gestores não podem promover utilizadores a administrador.');
            }
        }

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:180', 'unique:users,email,' . $user->id],
            'role'          => ['required', 'string', 'exists:roles,name'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'password'      => [
                'nullable', 
                // Definir mínimo de 8 caracteres e requisitos de segurança
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
        ]);

        $user->update([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'role'          => $data['role'],
            'department_id' => $data['department_id'] ?? null,
            'password'      => isset($data['password']) ? Hash::make($data['password']) : $user->password,
        ]);

        $user->syncRoles([$data['role']]);

        AuditLog::create([
            'user_id'     => $request->user()->id,
            'action'      => 'user_updated',
            'entity_type' => User::class,
            'entity_id'   => $user->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'metadata'    => ['name' => $user->name, 'role' => $data['role']],
        ]);

        return redirect()->route('users.index')
            ->with('status', "Utilizador {$user->name} actualizado com sucesso.");
    }

    /**
     * Activa a conta de um utilizador (apenas Admin).
     */
    public function activate(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        if (! $request->user()->isAdmin() && $user->isAdmin()) {
            abort(403, 'Acesso negado: Gestores não podem activar administradores.');
        }

        $user->update(['active' => true, 'failed_login_attempts' => 0, 'locked_until' => null]);

        AuditLog::create([
            'user_id'     => $request->user()->id,
            'action'      => AuditLog::ACTION_ACCOUNT_ACTIVATED,
            'entity_type' => User::class,
            'entity_id'   => $user->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return redirect()->route('users.index')
            ->with('status', "Conta de {$user->name} activada.");
    }

    /**
     * Desactiva a conta de um utilizador (apenas Admin).
     */
    public function deactivate(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        if (! $request->user()->isAdmin() && $user->isAdmin()) {
            abort(403, 'Acesso negado: Gestores não podem desactivar administradores.');
        }

        // Prevent deactivating own account
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'Não pode desactivar a sua própria conta.']);
        }

        $user->update(['active' => false]);

        AuditLog::create([
            'user_id'     => $request->user()->id,
            'action'      => AuditLog::ACTION_ACCOUNT_DEACTIVATED,
            'entity_type' => User::class,
            'entity_id'   => $user->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return redirect()->route('users.index')
            ->with('status', "Conta de {$user->name} desactivada.");
    }
}
