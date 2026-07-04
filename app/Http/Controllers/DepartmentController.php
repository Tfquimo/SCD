<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    /**
     * List all departments (Admin or Manager).
     */
    public function index()
    {
        Gate::authorize('admin-or-manager');

        $departments = Department::withCount('users')
            ->with('manager')
            ->orderBy('name')
            ->paginate(15);

        return view('departments.index', compact('departments'));
    }

    /**
     * Show create department form (Admin only).
     */
    public function create()
    {
        Gate::authorize('manage-users');

        $managers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['Admin', 'Gestor']))
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('departments.create', compact('managers'));
    }

    /**
     * Store a new department (Admin only).
     */
    public function store(Request $request)
    {
        Gate::authorize('manage-users');

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255', 'unique:departments,name'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $department = Department::create($data);

        AuditLog::create([
            'user_id'     => $request->user()->id,
            'action'      => 'department_created',
            'entity_type' => Department::class,
            'entity_id'   => $department->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'metadata'    => ['name' => $department->name],
        ]);

        return redirect()->route('departments.index')
            ->with('status', "Departamento \"{$department->name}\" criado com sucesso.");
    }

    /**
     * Show edit department form (Admin only).
     */
    public function edit(Department $department)
    {
        Gate::authorize('manage-users');

        $managers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['Admin', 'Gestor']))
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('departments.edit', compact('department', 'managers'));
    }

    /**
     * Update department (Admin only).
     */
    public function update(Request $request, Department $department)
    {
        Gate::authorize('manage-users');

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255', 'unique:departments,name,' . $department->id],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $department->update($data);

        AuditLog::create([
            'user_id'     => $request->user()->id,
            'action'      => 'department_updated',
            'entity_type' => Department::class,
            'entity_id'   => $department->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'metadata'    => ['name' => $department->name],
        ]);

        return redirect()->route('departments.index')
            ->with('status', "Departamento \"{$department->name}\" actualizado.");
    }

    /**
     * Delete department (Admin only — only if no users assigned).
     */
    public function destroy(Request $request, Department $department)
    {
        Gate::authorize('manage-users');

        if ($department->users()->count() > 0) {
            return back()->withErrors([
                'error' => 'Não é possível eliminar um departamento com utilizadores associados.'
            ]);
        }

        $name = $department->name;
        $department->delete();

        AuditLog::create([
            'user_id'     => $request->user()->id,
            'action'      => 'department_deleted',
            'entity_type' => Department::class,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'metadata'    => ['name' => $name],
        ]);

        return redirect()->route('departments.index')
            ->with('status', "Departamento \"{$name}\" eliminado.");
    }
}
