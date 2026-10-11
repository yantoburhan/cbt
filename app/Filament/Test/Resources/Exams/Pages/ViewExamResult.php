<?php

namespace App\Filament\Test\Resources\Exams\Pages;

use App\Filament\Test\Resources\Exams\ExamResource;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\StudentStatusEnum;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;

class ViewExamResult extends Page
{
    protected static string $resource = ExamResource::class;

    protected string $view = 'filament.test.resources.exams.pages.view-exam-result';

    public array $history = [];

    public function mount(Exam $exam): void
    {
        $student = Filament::auth()->user()?->student;
        abort_unless($student && $student->status === StudentStatusEnum::Active, 403);

        $attempt = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->with(['exam', 'subjectResults.subject', 'responses.subject'])
            ->firstOrFail();

        $this->history = [
            'exam' => $attempt->exam->title,
            'submitted_at' => $attempt->submitted_at->format('d/m/Y H:i'),
            'score' => (float) $attempt->subjectResults->sum('score'),
            'correct' => $attempt->subjectResults->sum('correct_count'),
            'wrong' => $attempt->subjectResults->sum('wrong_count'),
            'total' => $attempt->subjectResults->sum('total_questions'),
            'subjects' => $attempt->subjectResults
                ->map(fn ($result): array => [
                    'name' => $result->subject?->name ?? 'Mapel dihapus',
                    'score' => (float) $result->score,
                    'correct' => $result->correct_count,
                    'wrong' => $result->wrong_count,
                    'total' => $result->total_questions,
                    'questions' => $attempt->responses
                        ->filter(fn ($response) => $response->subject_id === $result->subject_id)
                        ->sortBy('position')
                        ->map(fn ($response): array => [
                            'content' => $response->question_content,
                            'answer' => $response->answer_text,
                            'correct_answer' => $response->correct_answer_text,
                            'is_correct' => $response->is_correct,
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
        ];
    }
}
