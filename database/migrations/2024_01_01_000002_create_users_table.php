<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the default Laravel users table and recreate with SCD schema
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 180)->unique();
            $table->string('password'); // bcrypt via Hash::make
            $table->enum('role', ['admin', 'manager', 'employee'])->default('employee');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            // 2FA
            $table->text('two_factor_secret')->nullable();          // encrypted
            $table->text('two_factor_recovery_codes')->nullable();  // encrypted
            $table->timestamp('two_factor_confirmed_at')->nullable();

            // Account state
            $table->boolean('active')->default(true);
            $table->unsignedTinyInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Restore FK after users table is recreated
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
