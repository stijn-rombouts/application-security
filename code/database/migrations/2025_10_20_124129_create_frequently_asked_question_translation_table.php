<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::create('frequently_asked_question_translation', function (Blueprint $table) {
            $table->id('faq_translation_id');
            $table->foreignId('faq_id')
                ->constrained('frequently_asked_question', 'faq_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('language_id')
                ->constrained('language', 'language_id')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('question');
            $table->string('answer');
            $table->timestamps();

            $table->unique(['faq_id', 'language_id'], 'faq_lang_unique');
        });
    }


    /**
     * Reverse the migrations.
    */
    public function down()
    {
        Schema::dropIfExists('frequently_asked_question_translation');
    }
};
