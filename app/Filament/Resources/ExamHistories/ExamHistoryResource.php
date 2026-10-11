<?php

namespace App\Filament\Resources\ExamHistories;

use App\Filament\Resources\ExamHistories\Pages\ListExamHistories;
use App\Filament\Resources\ExamHistories\Pages\ViewExamHistory;
use App\Models\ExamAttempt;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ExamHistoryResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Rekap & Histori Ujian';

    protected static ?string $modelLabel = 'rekap ujian';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['student', 'exam', 'subjectResults.subject', 'responses.subject'])
            ->withSum('subjectResults as total_score', 'score')
            ->withSum('subjectResults as total_correct', 'correct_count')
            ->withSum('subjectResults as total_wrong', 'wrong_count');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->whereNotNull('submitted_at')
                ->with(['student', 'exam', 'subjectResults.subject', 'responses.subject']))
            ->columns([
                TextColumn::make('student.nis')
                    ->label('NIS')
                    ->searchable(),
                TextColumn::make('student.name')
                    ->label('Nama siswa')
                    ->searchable(),
                TextColumn::make('exam.title')
                    ->label('Ujian')
                    ->searchable(),
                TextColumn::make('subjects_summary')
                    ->label('Mata pelajaran')
                    ->state(fn (ExamAttempt $record): string => $record->subjectResults
                        ->pluck('subject.name')
                        ->filter()
                        ->unique()
                        ->implode(', ')),
                TextColumn::make('submitted_at')
                    ->label('Tanggal ujian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('total_score')
                    ->label('Skor total')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('total_correct')
                    ->label('Benar')
                    ->sortable(),
                TextColumn::make('total_wrong')
                    ->label('Salah')
                    ->sortable(),
                TextColumn::make('total_questions')
                    ->label('Jumlah soal')
                    ->state(fn (ExamAttempt $record): int => $record->subjectResults->sum('total_questions')),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('student.nis')->label('NIS'),
            TextEntry::make('student.name')->label('Nama siswa'),
            TextEntry::make('exam.title')->label('Ujian'),
            TextEntry::make('submitted_at')->label('Tanggal ujian')->dateTime('d F Y, H:i'),
            TextEntry::make('total_score')->label('Skor total')->numeric(decimalPlaces: 2),
            TextEntry::make('total_correct')->label('Jawaban benar'),
            TextEntry::make('total_wrong')->label('Jawaban salah'),
            RepeatableEntry::make('subjectResults')
                ->label('Rekap per mata pelajaran')
                ->columns(5)
                ->schema([
                    TextEntry::make('subject.name')->label('Mata pelajaran'),
                    TextEntry::make('score')->label('Skor')->numeric(decimalPlaces: 2),
                    TextEntry::make('correct_count')->label('Benar'),
                    TextEntry::make('wrong_count')->label('Salah'),
                    TextEntry::make('total_questions')->label('Jumlah soal'),
                ])
                ->columnSpanFull(),
            RepeatableEntry::make('responses')
                ->label('Rincian jawaban')
                ->columnSpanFull()
                ->state(fn (ExamAttempt $record) => $record->responses->sortBy('position')->values())
                ->schema([
                    TextEntry::make('subject.name')->label('Mata pelajaran')->placeholder('Mapel dihapus'),
                    TextEntry::make('question_content')->label('Soal')->html()->columnSpanFull(),
                    TextEntry::make('answer_text')->label('Jawaban siswa')->placeholder('Tidak dijawab'),
                    TextEntry::make('correct_answer_text')->label('Kunci jawaban')->placeholder('Kunci tidak tersedia'),
                    IconEntry::make('is_correct')->label('Hasil')->boolean(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamHistories::route('/'),
            'view' => ViewExamHistory::route('/{record}'),
        ];
    }
}
