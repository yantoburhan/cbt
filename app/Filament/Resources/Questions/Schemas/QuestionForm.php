<?php

namespace App\Filament\Resources\Questions\Schemas;

use App\Models\Subject;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Soal')
                    ->columns(2)
                    ->schema([
                        Select::make('subject_id')
                            ->label('Mata Pelajaran')
                            ->options(Subject::query()->where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        TextInput::make('score')
                            ->label('Skor')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),

                        Textarea::make('content')
                            ->label('Pertanyaan')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->nullable()
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),

                Section::make('Pilihan Jawaban')
                    ->schema([
                        Repeater::make('answers')
                            ->relationship('answers')
                            ->schema([
                                TextInput::make('text')
                                    ->label('Teks Pilihan')
                                    ->required()
                                    ->columnSpan(2),
                                Toggle::make('is_correct')
                                    ->label('Jawaban Benar')
                                    ->default(false),
                            ])
                            ->columns(3)
                            ->defaultItems(5)
                            ->minItems(2)
                            ->maxItems(5),
                    ]),
            ]);
    }
}
