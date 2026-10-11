<?php

namespace App\Filament\Test\Pages;

use App\StudentStatusEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SubjectHistory extends Page
{
    protected string $view = 'filament.test.pages.subject-history';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Riwayat';

    protected static ?string $navigationLabel = 'Histori Pelajaran';

    protected static ?string $title = 'Histori Pelajaran';

    public array $subjects = [];

    public array $summary = [];

    public function mount(): void
    {
        $student = Filament::auth()->user()?->student;
        abort_unless($student && $student->status === StudentStatusEnum::Active, 403);

        $attempts = $student->examAttempts()
            ->whereNotNull('submitted_at')
            ->with(['exam', 'subjectResults.subject'])
            ->orderByDesc('submitted_at')
            ->get();

        $this->subjects = $attempts
            ->map(fn ($attempt): array => [
                'exam' => $attempt->exam->title,
                'date' => $attempt->submitted_at->format('d/m/Y H:i'),
                'score' => (float) $attempt->subjectResults->sum('score'),
                'correct' => $attempt->subjectResults->sum('correct_count'),
                'wrong' => $attempt->subjectResults->sum('wrong_count'),
                'total' => $attempt->subjectResults->sum('total_questions'),
                'subjects' => $attempt->subjectResults->map(fn ($result): array => [
                    'name' => $result->subject?->name ?? 'Mapel dihapus',
                    'score' => (float) $result->score,
                    'correct' => $result->correct_count,
                    'wrong' => $result->wrong_count,
                    'total' => $result->total_questions,
                ])->all(),
            ])
            ->all();

        $this->summary = [
            'exam_count' => $attempts->count(),
            'average_score' => count($this->subjects)
                ? array_sum(array_column($this->subjects, 'score')) / count($this->subjects)
                : 0,
            'answered_correct' => $attempts->sum(fn ($attempt) => $attempt->subjectResults->sum('correct_count')),
            'answered_wrong' => $attempts->sum(fn ($attempt) => $attempt->subjectResults->sum('wrong_count')),
        ];
    }
}
