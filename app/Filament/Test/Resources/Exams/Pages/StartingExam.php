<?php

namespace App\Filament\Test\Resources\Exams\Pages;

use App\Filament\Test\Resources\Exams\ExamResource;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamResponse;
use App\Models\ExamSubjectResult;
use App\StudentStatusEnum;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class StartingExam extends Page
{
    protected static string $resource = ExamResource::class;

    protected string $view = 'filament.test.resources.exams.pages.starting-exam';

    #[Locked]
    public int $examId;

    #[Locked]
    public array $questions = [];

    #[Locked]
    public int $currentIndex = 0;

    #[Locked]
    public int $remainingSeconds = 0;

    public array $selectedAnswers = [];

    public array $doubtfulQuestions = [];

    public int $answeredCount = 0;

    public bool $isSubmitted = false;

    public function mount(Exam $exam): void
    {
        $student = Filament::auth()->user()?->student;

        abort_unless(
            $student && $student->status === StudentStatusEnum::Active,
            403
        );
        abort_unless(
            $exam->is_available
                && $exam->started_at->isPast()
                && $exam->expired_at->isFuture(),
            403
        );

        $this->examId = $exam->id;

        $this->remainingSeconds = max(0, (int) now()->diffInSeconds($exam->expired_at, false));

        $attempt = DB::transaction(function () use ($exam, $student): ExamAttempt {
            $attempt = ExamAttempt::query()->firstOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                ],
                ['started_at' => now()]
            );

            if ($attempt->responses()->doesntExist()) {
                $position = 0;

                foreach ($exam->subjects as $subject) {
                    $questions = $subject->questions()
                        ->where('is_active', true)
                        ->inRandomOrder()
                        ->limit($subject->pivot->qty)
                        ->get();

                    foreach ($questions as $question) {
                        if (! $question->answers()->where('is_active', true)->exists()) {
                            continue;
                        }

                        $attempt->responses()->create([
                            'question_id' => $question->id,
                            'position' => $position++,
                            'is_doubtful' => false,
                        ]);
                    }
                }
            }

            return $attempt;
        });

        abort_if(
            $attempt->submitted_at !== null,
            403,
            'Ujian ini sudah diselesaikan dan tidak dapat dimulai kembali.'
        );

        abort_unless($attempt->responses()->exists(), 422, 'Ujian ini belum memiliki soal aktif.');

        $this->isSubmitted = $attempt->submitted_at !== null;
        $responses = $attempt->responses()
            ->with(['question.answers' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('position')
            ->get();

        $this->questions = $responses
            ->filter(fn (ExamResponse $response) => $response->question !== null)
            ->map(fn (ExamResponse $response): array => [
                'response_id' => $response->id,
                'id' => $response->question->id,
                'content' => $response->question->content,
                'subject' => $response->question->subject?->name,
                'answers' => $response->question->answers
                    ->shuffle()
                    ->map(fn (Answer $answer): array => [
                        'id' => $answer->id,
                        'text' => preg_replace('/^[A-E][.)]\s*/u', '', $answer->text),
                    ])
                    ->values()
                    ->map(fn (array $answer, int $index): array => [
                        ...$answer,
                        'letter' => chr(65 + $index),
                    ])
                    ->all(),
            ])
            ->values()
            ->all();

        foreach ($responses as $index => $response) {
            $questionId = $response->question_id;
            $this->selectedAnswers[$questionId] = $response->answer_id;
            $this->doubtfulQuestions[$questionId] = $response->is_doubtful;
        }

        $this->answeredCount = $responses->whereNotNull('answer_id')->count();

        abort_unless(count($this->questions) > 0, 422, 'Ujian ini belum memiliki soal aktif.');
    }

    public function chooseAnswer(int $answerId): void
    {
        $this->ensureAttemptIsEditable();

        $question = $this->currentQuestion();
        $answer = Answer::query()
            ->where('question_id', $question['id'])
            ->where('is_active', true)
            ->findOrFail($answerId);

        $response = $this->currentResponse();
        if ($response->answer_id === null) {
            $this->answeredCount++;
        }
        $response->update(['answer_id' => $answer->id]);
        $this->selectedAnswers[$question['id']] = $answer->id;
    }

    public function previousQuestion(): void
    {
        $this->currentIndex = max(0, $this->currentIndex - 1);
    }

    public function nextQuestion(): void
    {
        $this->currentIndex = min(count($this->questions) - 1, $this->currentIndex + 1);
    }

    public function goToQuestion(int $index): void
    {
        abort_unless(array_key_exists($index, $this->questions), 404);

        $this->currentIndex = $index;
    }

    public function toggleDoubtful(): void
    {
        $this->ensureAttemptIsEditable();

        $questionId = $this->currentQuestion()['id'];
        $isDoubtful = ! ($this->doubtfulQuestions[$questionId] ?? false);
        $this->currentResponse()->update(['is_doubtful' => $isDoubtful]);
        $this->doubtfulQuestions[$questionId] = $isDoubtful;
    }

    public function submitExam(): void
    {
        $this->ensureAttemptIsEditable();

        $attempt = $this->attempt();
        $responses = $attempt->responses()
            ->with(['question.subject', 'answer', 'question.answers'])
            ->get();

        if ($responses->contains(fn (ExamResponse $response) => $response->answer_id === null)) {
            throw ValidationException::withMessages([
                'exam' => 'Jawab semua soal sebelum menyimpan ujian.',
            ]);
        }

        DB::transaction(function () use ($attempt, $responses): void {
            $results = [];

            foreach ($responses as $response) {
                $question = $response->question;
                $answer = $response->answer;
                $correctAnswer = $question->answers->firstWhere('is_correct', true);
                $isCorrect = $answer?->is_correct ?? false;
                $subjectId = $question->subject_id;
                $score = (float) $question->score;

                $response->update([
                    'subject_id' => $subjectId,
                    'question_content' => $question->content,
                    'question_description' => $question->description,
                    'answer_text' => $answer?->text,
                    'correct_answer_text' => $correctAnswer?->text,
                    'is_correct' => $isCorrect,
                ]);

                $results[$subjectId] ??= [
                    'correct_count' => 0,
                    'wrong_count' => 0,
                    'total_questions' => 0,
                    'score' => 0,
                ];
                $results[$subjectId]['total_questions']++;

                if ($isCorrect) {
                    $results[$subjectId]['correct_count']++;
                    $results[$subjectId]['score'] += $score;
                } else {
                    $results[$subjectId]['wrong_count']++;
                }
            }

            foreach ($results as $subjectId => $result) {
                ExamSubjectResult::query()->updateOrCreate(
                    [
                        'exam_attempt_id' => $attempt->id,
                        'subject_id' => $subjectId,
                    ],
                    $result
                );
            }

            $attempt->update(['submitted_at' => now()]);
        });

        $this->isSubmitted = true;
    }

    private function currentQuestion(): array
    {
        return $this->questions[$this->currentIndex];
    }

    private function currentResponse(): ExamResponse
    {
        return ExamResponse::query()->findOrFail(
            $this->currentQuestion()['response_id']
        );
    }

    private function attempt(): ExamAttempt
    {
        return ExamAttempt::query()
            ->where('exam_id', $this->examId)
            ->where('student_id', Filament::auth()->user()->student->id)
            ->firstOrFail();
    }

    private function ensureAttemptIsEditable(): void
    {
        abort_if($this->isSubmitted, 403, 'Ujian sudah disimpan.');
        abort_if($this->attempt()->submitted_at !== null, 403, 'Ujian sudah disimpan.');
        abort_if(now()->greaterThanOrEqualTo($this->attempt()->exam->expired_at), 403, 'Waktu ujian telah berakhir.');
    }
}
