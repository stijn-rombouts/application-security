<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates tables:
     * - role
     * - user_role
     */
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role')->unique();
            $table->boolean('receive_emails')->default(false);
            $table->timestamps();
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->id('user_role_id');
            $table->foreignId('user_id') // Foreign key to user table
                ->constrained('users', 'user_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('role_id') // Foreign key to role table
                ->constrained('role', 'role_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role');
    }
};
