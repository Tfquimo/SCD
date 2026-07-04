<?php

namespace Tests\Feature\File;

use App\Models\Department;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('local');

        // Create Roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Gestor']);
        Role::create(['name' => 'Funcionário']);
    }

    private function authenticateUser($role = 'Funcionário', $department = null)
    {
        $user = User::factory()->create([
            'department_id' => $department ? $department->id : null,
            'two_factor_confirmed_at' => now(), // Assume 2FA is done
        ]);
        $user->assignRole($role);
        
        $this->actingAs($user)->withSession(['auth.2fa_verified' => true]);

        return $user;
    }

    public function test_user_can_view_files_list(): void
    {
        $user = $this->authenticateUser();

        $response = $this->get('/files');

        $response->assertStatus(200);
        $response->assertViewIs('files.index');
    }

    public function test_user_can_upload_file_securely(): void
    {
        $user = $this->authenticateUser();
        $file = UploadedFile::fake()->create('documento-secreto.pdf', 100); // 100 KB

        $response = $this->post('/files', [
            'file' => $file,
            'name' => 'Meu Documento Confidencial',
        ]);

        $response->assertRedirect('/files');
        $response->assertSessionHas('status', 'Ficheiro carregado e encriptado com sucesso.');

        $this->assertDatabaseHas('files', [
            'user_id' => $user->id,
            'name' => 'Meu Documento Confidencial',
            'original_name' => 'documento-secreto.pdf',
        ]);

        $fileRecord = File::first();
        
        // Assert the file was physically saved (encrypted path)
        Storage::disk('local')->assertExists($fileRecord->path);
    }

    public function test_user_can_download_their_own_file(): void
    {
        $user = $this->authenticateUser();
        $file = UploadedFile::fake()->create('test.txt', 10, 'text/plain');

        $this->post('/files', [
            'file' => $file,
            'name' => 'Teste Download',
        ]);

        $fileRecord = File::first();

        $response = $this->get("/files/{$fileRecord->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=test.txt');
    }

    public function test_users_cannot_access_files_from_other_departments(): void
    {
        $deptA = Department::factory()->create();
        $deptB = Department::factory()->create();

        // User A uploads
        $userA = $this->authenticateUser('Funcionário', $deptA);
        $file = UploadedFile::fake()->create('secretA.pdf', 10);
        $this->post('/files', ['file' => $file]);
        $fileRecord = File::first();

        // User B tries to download
        $userB = $this->authenticateUser('Funcionário', $deptB);
        $response = $this->get("/files/{$fileRecord->id}/download");

        // Should be forbidden
        $response->assertStatus(403);
    }

    public function test_manager_can_access_files_from_their_department(): void
    {
        $deptA = Department::factory()->create();

        // Employee uploads
        $employee = $this->authenticateUser('Funcionário', $deptA);
        $file = UploadedFile::fake()->create('secretA.pdf', 10);
        $this->post('/files', ['file' => $file]);
        $fileRecord = File::first();

        // Manager from same dept tries to download
        $manager = $this->authenticateUser('Gestor', $deptA);
        $response = $this->get("/files/{$fileRecord->id}/download");

        // Should succeed
        $response->assertStatus(200);
    }

    public function test_admin_can_access_any_file(): void
    {
        $deptA = Department::factory()->create();

        // Employee uploads
        $employee = $this->authenticateUser('Funcionário', $deptA);
        $file = UploadedFile::fake()->create('secretA.pdf', 10);
        $this->post('/files', ['file' => $file]);
        $fileRecord = File::first();

        // Admin tries to download
        $admin = $this->authenticateUser('Admin');
        $response = $this->get("/files/{$fileRecord->id}/download");

        // Should succeed
        $response->assertStatus(200);
    }

    public function test_user_can_delete_their_own_file(): void
    {
        $user = $this->authenticateUser();
        $file = UploadedFile::fake()->create('test.txt', 10);
        $this->post('/files', ['file' => $file]);
        
        $fileRecord = File::first();
        $path = $fileRecord->path;
        
        Storage::disk('local')->assertExists($path);

        $response = $this->delete("/files/{$fileRecord->id}");

        $response->assertRedirect('/files');
        
        // Assert record is soft deleted
        $this->assertSoftDeleted($fileRecord);
        
        // Assert physical encrypted file is deleted
        Storage::disk('local')->assertMissing($path);
    }
}
