<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the following tables:
     * - organisation
     * - language
     * - user
     * - question_translation
     * - user_test
     * - organisation_test
     * - test_attempt
     * - answer
     * - interest_field_translation
     */
    public function up(): void
    {

        Schema::create('organisation', function (Blueprint $table) {
            $table->id('organisation_id');
            $table->string('name');
            $table->boolean('active')->default(false);
            $table->date('expire_date')->nullable();
            $table->timestamps();
        });

        Schema::create('language', function (Blueprint $table) {
            $table->id('language_id');
            $table->string('language_code');
            $table->string('language_name');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->boolean('is_sound_on')->default(false);
            $table->string('vision_type');
            $table->foreignId('mentor_id')->nullable() // Foreign key to users table
                ->constrained('users', 'user_id')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->foreignId('organisation_id') // Foreign key to organisation table
                ->constrained('organisation', 'organisation_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('language_id') // Foreign key to language table
                ->constrained('language', 'language_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->boolean('first_login')->default(true);
            $table->boolean('active')->default(true);
            $table->string('profile_picture_url')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('username')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });


        Schema::create('question_translation', function (Blueprint $table) {
            $table->id('question_translation_id');
            $table->foreignId('question_id') // Foreign key to question table
                ->constrained('question', 'question_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('language_id') // Foreign key to language table
                ->constrained('language', 'language_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('question');
            $table->string('media_link')->nullable();
            $table->string('sound_link')->nullable();
            $table->string('image_description')->nullable();
            $table->timestamps();
        });


        Schema::create('user_test', function (Blueprint $table) {
            $table->id('user_test_id');
            $table->foreignId('user_id') // Foreign key to user table
                ->constrained('users', 'user_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('test_id') // Foreign key to test table
                ->constrained('test', 'test_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
        });

        Schema::create('organisation_test', function (Blueprint $table) {
            $table->id('organisation_test_id');
            $table->foreignId('test_id') // Foreign key to test table
                ->constrained('test', 'test_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('organisation_id') // Foreign key to organisation table
                ->constrained('organisation', 'organisation_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
        });

        Schema::create('test_attempt', function (Blueprint $table) {
            $table->id('test_attempt_id');
            $table->foreignId('test_id') // Foreign key to test table
                ->constrained('test', 'test_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('user_id') // Foreign key to user table
                ->constrained('users', 'user_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->boolean('finished')->default(false);
            $table->timestamps();
        });

        Schema::create('answer', function (Blueprint $table) {
            $table->id('answer_id');
            $table->boolean('answer')->nullable();
            $table->integer('response_time');
            $table->boolean('unclear')->default(false);
            $table->foreignId('question_id') // Foreign key to question table
                ->constrained('question', 'question_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('test_attempt_id') // Foreign key to test_attempt table
                ->constrained('test_attempt', 'test_attempt_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
        });

        // FK interest_field_id to interest_field table
        Schema::create('interest_field_translation', function (Blueprint $table) {
            $table->id('interest_field_translation_id');
            $table->foreignId('interest_field_id') // Foreign key to interest_field table
                ->constrained('interest_field', 'interest_field_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('language_id') // Foreign key to language table
                ->constrained('language', 'language_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_field_translation');
        Schema::dropIfExists('answer');
        Schema::dropIfExists('test_attempt');
        Schema::dropIfExists('organisation_test');
        Schema::dropIfExists('user_test');
        Schema::dropIfExists('question_translation');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('language');
        Schema::dropIfExists('organisation');
    }
};
