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
        Schema::table('interest_field', function (Blueprint $table) {
            if (! Schema::hasColumn('interest_field', 'sound_link')) {
                $table->string('sound_link')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interest_field', function (Blueprint $table) {
            if (Schema::hasColumn('interest_field', 'sound_link')) {
                $table->dropColumn('sound_link');
            }
        });
    }
};
