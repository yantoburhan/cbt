<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('exam_attempts')
            ->whereNotNull('submitted_at')
            ->orderBy('id')
            ->chunkById(100, function ($attempts): void {
                foreach ($attempts as $attempt) {
                    $subjectResults = [];

                    $responses = DB::table('exam_responses as responses')
                        ->join('questions', 'questions.id', '=', 'responses.question_id')
                        ->leftJoin('answers as selected', 'selected.id', '=', 'responses.answer_id')
                        ->leftJoin('answers as correct', function ($join): void {
                            $join->on('correct.question_id', '=', 'questions.id')
                                ->where('correct.is_correct', '=', true);
                        })
                        ->where('responses.exam_attempt_id', $attempt->id)
                        ->select([
                            'responses.id',
                            'responses.answer_id',
                            'questions.subject_id',
                            'questions.content',
                            'questions.description',
                            'questions.score',
                            'selected.text as answer_text',
                            'selected.is_correct as selected_is_correct',
                            'correct.text as correct_answer_text',
                        ])
                        ->get();

                    foreach ($responses as $response) {
                        $isCorrect = (bool) $response->selected_is_correct;

                        DB::table('exam_responses')
                            ->where('id', $response->id)
                            ->update([
                                'question_content' => $response->content,
                                'question_description' => $response->description,
                                'answer_text' => $response->answer_text,
                                'correct_answer_text' => $response->correct_answer_text,
                                'is_correct' => $isCorrect,
                            ]);

                        $subjectId = $response->subject_id;
                        $subjectResults[$subjectId] ??= [
                            'exam_attempt_id' => $attempt->id,
                            'subject_id' => $subjectId,
                            'score' => 0,
                            'correct_count' => 0,
                            'wrong_count' => 0,
                            'total_questions' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $subjectResults[$subjectId]['total_questions']++;

                        if ($isCorrect) {
                            $subjectResults[$subjectId]['correct_count']++;
                            $subjectResults[$subjectId]['score'] += (int) $response->score;
                        } else {
                            $subjectResults[$subjectId]['wrong_count']++;
                        }
                    }

                    if ($subjectResults !== []) {
                        DB::table('exam_subject_results')->upsert(
                            array_values($subjectResults),
                            ['exam_attempt_id', 'subject_id'],
                            ['score', 'correct_count', 'wrong_count', 'total_questions', 'updated_at']
                        );
                    }
                }
            });
    }

    public function down(): void
    {
    }
};
