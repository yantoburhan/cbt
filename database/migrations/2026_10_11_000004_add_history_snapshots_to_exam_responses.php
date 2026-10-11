<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_responses', function (Blueprint $table) {
            $table->longText('question_content')->nullable();
            $table->text('question_description')->nullable();
            $table->string('answer_text')->nullable();
            $table->string('correct_answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('exam_responses', function (Blueprint $table) {
            $table->dropColumn([
                'question_content',
                'question_description',
                'answer_text',
                'correct_answer_text',
                'is_correct',
            ]);
        });
    }
};
