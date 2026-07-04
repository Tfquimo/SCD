<?php

namespace Tests\Feature\User;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Gestor']);
        Role::create(['name' => 'Funcionário']);
    }

    private function adminUser(): User
    {
        $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
        $user->assignRole('Admin');
        $this->actingAs($user)->withSession(['auth.2fa_verified' => true]);
        return $user;
    }

    private function managerUser(?Department $dept = null): User
    {
        $user = User::factory()->create([
            'department_id' => $dept?->id,
            'two_factor_confirmed_at' => now(),
        ]);
        $user->assignRole('Gestor');
        $this->actingAs($user)->withSession(['auth.2fa_verified' => true]);
        return $user;
    }

    private function employeeUser(): User
    {
        $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
        $user->assignRole('Funcionário');
        $this->actingAs($user)->withSession(['auth.2fa_verified' => true]);
        return $user;
    }

    // ─── Listing ────────────────────────────────────────────────────

    public function test_admin_can_view_users_list(): void
    {
        $this->adminUser();
        $this->get('/users')->assertStatus(200)->assertViewIs('users.index');
    }

    public function test_manager_can_view_users_list(): void
    {
        $dept = Department::factory()->create();
        $this->managerUser($dept);
        $this->get('/users')->assertStatus(200);
    }

    public function test_employee_cannot_view_users_list(): void
    {
        $this->employeeUser();
        $this->get('/users')->assertStatus(403);
    }

    // ─── Create ─────────────────────────────────────────────────────

    public function test_admin_can_create_user(): void
    {
        $this->adminUser();
        $dept = Department::factory()->create();

        $response = $this->post('/users', [
            'name'          => 'Novo Colaborador',
            'email'         => 'novo@empresa.pt',
            'password'      => 'Senha@Segura123',
            'role'          => 'Funcionário',
            'department_id' => $dept->id,
            'active'        => '1',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', ['email' => 'novo@empresa.pt']);
    }

    public function test_employee_cannot_create_user(): void
    {
        $this->employeeUser();
        $response = $this->post('/users', [
            'name' => 'Hacker', 'email' => 'hack@x.pt', 'password' => 'Senha@Segura123', 'role' => 'Admin',
        ]);
        $response->assertStatus(403);
    }

    public function test_create_user_requires_strong_password(): void
    {
        $this->adminUser();
        $response = $this->post('/users', [
            'name'     => 'Test',
            'email'    => 'test@empresa.pt',
            'password' => '12345',   // too weak
            'role'     => 'Funcionário',
        ]);
        $response->assertSessionHasErrors('password');
    }

    public function test_create_user_requires_unique_email(): void
    {
        $this->adminUser();
        User::factory()->create(['email' => 'exists@empresa.pt']);
        $response = $this->post('/users', [
            'name'     => 'Duplicate',
            'email'    => 'exists@empresa.pt',
            'password' => 'Senha@Segura123',
            'role'     => 'Funcionário',
        ]);
        $response->assertSessionHasErrors('email');
    }

    // ─── Update ─────────────────────────────────────────────────────

    public function test_admin_can_update_user(): void
    {
        $this->adminUser();
        $target = User::factory()->create();
        $target->assignRole('Funcionário');

        $this->put("/users/{$target->id}", [
            'name'  => 'Nome Actualizado',
            'email' => $target->email,
            'role'  => 'Gestor',
        ])->assertRedirect('/users');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Nome Actualizado']);
        $this->assertTrue($target->fresh()->hasRole('Gestor'));
    }

    // ─── Activate / Deactivate ──────────────────────────────────────

    public function test_admin_can_deactivate_user(): void
    {
        $this->adminUser();
        $target = User::factory()->create(['active' => true]);
        $target->assignRole('Funcionário');

        $this->post("/users/{$target->id}/deactivate")->assertRedirect('/users');
        $this->assertFalse((bool) $target->fresh()->active);
    }

    public function test_admin_can_activate_user(): void
    {
        $this->adminUser();
        $target = User::factory()->create(['active' => false]);
        $target->assignRole('Funcionário');

        $this->post("/users/{$target->id}/activate")->assertRedirect('/users');
        $this->assertTrue((bool) $target->fresh()->active);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = $this->adminUser();
        $this->post("/users/{$admin->id}/deactivate")->assertSessionHasErrors('error');
        $this->assertTrue((bool) $admin->fresh()->active);
    }

    // ─── Departments ────────────────────────────────────────────────

    public function test_admin_can_create_department(): void
    {
        $this->adminUser();
        $this->post('/departments', ['name' => 'Contabilidade'])->assertRedirect('/departments');
        $this->assertDatabaseHas('departments', ['name' => 'Contabilidade']);
    }

    public function test_cannot_delete_department_with_users(): void
    {
        $this->adminUser();
        $dept = Department::factory()->create();
        User::factory()->create(['department_id' => $dept->id])->assignRole('Funcionário');

        $this->delete("/departments/{$dept->id}")->assertSessionHasErrors('error');
        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
    }

    public function test_can_delete_empty_department(): void
    {
        $this->adminUser();
        $dept = Department::factory()->create();

        $this->delete("/departments/{$dept->id}")->assertRedirect('/departments');
        $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
    }
}
