<?php

namespace App\Filament\Test\Resources\Exams;

use App\Filament\Test\Resources\Exams\Pages\ManageExams;
use App\Filament\Test\Resources\Exams\Pages\StartingExam;
use App\Filament\Test\Resources\Exams\Pages\ViewExamResult;
use App\Models\Exam;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Sesi Ujian';

    protected static ?string $modelLabel = 'Ujian';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $studentId = Filament::auth()->user()?->student?->id;

                $query
                    ->orderByRaw(
                        'CASE WHEN EXISTS (SELECT 1 FROM exam_attempts WHERE exam_attempts.exam_id = exams.id AND exam_attempts.student_id = ? AND exam_attempts.submitted_at IS NOT NULL) THEN 1 ELSE 0 END',
                        [$studentId]
                    )
                    ->orderBy('started_at');
            })
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Ujian')
                    ->searchable(),
                TextColumn::make('duration')
                    ->label('Durasi')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('threshold')
                    ->label('Min. Score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d F Y, H:i:s')
                    ->sortable(),
                TextColumn::make('student_attempt_status')
                    ->label('Status')
                    ->state(function ($record): string {
                        $attempt = $record->attempts()
                            ->where('student_id', Filament::auth()->user()?->student?->id)
                            ->first();

                        if ($attempt?->submitted_at) {
                            return 'Selesai';
                        }

                        if ($attempt) {
                            return $record->expired_at->isPast()
                                ? 'Waktu berakhir'
                                : 'Sedang dikerjakan';
                        }

                        if (! $record->is_available || $record->expired_at->isPast()) {
                            return 'Tidak tersedia';
                        }

                        return $record->started_at->isFuture() ? 'Belum dimulai' : 'Belum dikerjakan';
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Selesai' => 'success',
                        'Sedang dikerjakan' => 'warning',
                        'Waktu berakhir', 'Tidak tersedia' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                Action::make('start')
                    ->label(fn ($record) => $record->attempts()
                        ->where('student_id', Filament::auth()->user()?->student?->id)
                        ->first()?->submitted_at
                            ? 'Lihat Hasil'
                            : ($record->attempts()
                                ->where('student_id', Filament::auth()->user()?->student?->id)
                                ->exists() ? 'Lanjutkan ujian' : 'Mulai ujian'))
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('primary')
                    ->button()
                    ->disabled(function ($record): bool {
                        $attempt = $record->attempts()
                            ->where('student_id', Filament::auth()->user()?->student?->id)
                            ->first();

                        if ($attempt?->submitted_at) {
                            return false;
                        }

                        return ! $record->is_available
                            || $record->started_at->isFuture()
                            || $record->expired_at->isPast();
                    })
                    ->url(function ($record): string {
                        $attempt = $record->attempts()
                            ->where('student_id', Filament::auth()->user()?->student?->id)
                            ->first();

                        return $attempt?->submitted_at
                            ? route(ViewExamResult::getRouteName(), ['exam' => $record])
                            : route(StartingExam::getRouteName(), ['exam' => $record]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExams::route('/'),
            'mulai' => StartingExam::route('/{exam}/mulai'),
            'hasil' => ViewExamResult::route('/{exam}/hasil'),
        ];
    }
}
