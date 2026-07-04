<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/sobre', fn() => view('about'))->name('about');

// Dashboard — requires auth + active account + session timeout + 2FA
Route::middleware(['auth', 'account.active', 'session.timeout', 'prevent.back.history'])->group(function () {
    Route::middleware('two-factor')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

        // Ficheiros Seguros
        Route::get('/files', [\App\Http\Controllers\FileController::class, 'index'])->name('files.index');
        Route::post('/files', [\App\Http\Controllers\FileController::class, 'store'])->name('files.store');
        Route::get('/files/{file}/download', [\App\Http\Controllers\FileController::class, 'download'])->name('files.download');
        Route::delete('/files/{file}', [\App\Http\Controllers\FileController::class, 'destroy'])->name('files.destroy');

        // Partilha de Ficheiros
        Route::get('/shared', [\App\Http\Controllers\FileShareController::class, 'index'])->name('shared.index');
        Route::post('/files/{file}/share', [\App\Http\Controllers\FileShareController::class, 'store'])->name('files.share');
        Route::delete('/shares/{share}', [\App\Http\Controllers\FileShareController::class, 'destroy'])->name('shares.destroy');
        Route::get('/shared/{file}/download', [\App\Http\Controllers\FileShareController::class, 'download'])->name('shared.download');

        // Gestão de Utilizadores (Admin + Gestor)
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/activate', [\App\Http\Controllers\UserController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/deactivate', [\App\Http\Controllers\UserController::class, 'deactivate'])->name('users.deactivate');

        // Departamentos (Admin)
        Route::resource('departments', \App\Http\Controllers\DepartmentController::class);

        // Auditoria (Admin only)
        Route::get('/audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');

        // Cópias de Segurança / Backups (Admin only)
        Route::get('/backups', [\App\Http\Controllers\BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [\App\Http\Controllers\BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{filename}/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{filename}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('backups.destroy');
    });
});

// Include authentication routes
require __DIR__ . '/auth.php';
