<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('shared_by_user_id')->constrained('users')->cascadeOnDelete();
            // Can share with a specific user...
            $table->foreignId('shared_with_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            // ...or share with a whole department
            $table->foreignId('department_id')->nullable()->constrained('departments')->cascadeOnDelete();
            $table->timestamps();

            // At least one target is required
            // We'll enforce that via validation in the controller
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_shares');
    }
};
