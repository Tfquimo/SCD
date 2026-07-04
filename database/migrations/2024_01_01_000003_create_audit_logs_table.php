<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);           // e.g. login, logout, upload, download
            $table->string('entity_type', 100)->nullable(); // e.g. App\Models\File
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();     // extra context (old/new values, filenames, etc.)
            $table->timestamp('created_at')->useCurrent(); // immutable — no updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
