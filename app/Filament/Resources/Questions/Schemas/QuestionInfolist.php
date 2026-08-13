<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuestionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('subject.name')
                    ->label('Mata Pelajaran'),

                TextEntry::make('score')
                    ->label('Skor')
                    ->badge()
                    ->color('warning'),

                IconEntry::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),

                TextEntry::make('content')
                    ->label('Isi Soal')
                    ->columnSpanFull(),

                TextEntry::make('description')
                    ->label('Pembahasan')
                    ->placeholder('-')
                    ->columnSpanFull(),

                RepeatableEntry::make('answers')
                    ->label('Pilihan Jawaban')
                    ->schema([
                        TextEntry::make('text')
                            ->label('Pilihan'),
                        IconEntry::make('is_correct')
                            ->label('Benar')
                            ->boolean(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
