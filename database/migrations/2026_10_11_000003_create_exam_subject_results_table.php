<?php

use App\Models\ExamAttempt;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subject_results', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExamAttempt::class, 'exam_attempt_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subject::class)->constrained()->cascadeOnDelete();
            $table->decimal('score', 8, 2)->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->timestamps();
            $table->unique(['exam_attempt_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subject_results');
    }
};
