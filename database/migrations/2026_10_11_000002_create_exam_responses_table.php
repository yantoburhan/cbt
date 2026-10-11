<?php

use App\Models\Answer;
use App\Models\ExamAttempt;
use App\Models\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExamAttempt::class, 'exam_attempt_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Answer::class)->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('position');
            $table->boolean('is_doubtful')->default(false);
            $table->timestamps();
            $table->unique(['exam_attempt_id', 'question_id']);
            $table->unique(['exam_attempt_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_responses');
    }
};
