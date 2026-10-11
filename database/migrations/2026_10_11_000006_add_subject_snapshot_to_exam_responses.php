<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_responses', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->after('question_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
        });

        DB::table('exam_responses')
            ->join('questions', 'questions.id', '=', 'exam_responses.question_id')
            ->update(['exam_responses.subject_id' => DB::raw('questions.subject_id')]);
    }

    public function down(): void
    {
        Schema::table('exam_responses', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }
};
