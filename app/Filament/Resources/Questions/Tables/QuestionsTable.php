<?php

namespace App\Filament\Resources\Questions\Tables;

use App\Models\Subject;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('No.')
                    ->rowIndex()
                    ->width(40),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('content')
                    ->label('Soal')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('score')
                    ->label('Skor')
                    ->badge()
                    ->color('warning')
                    ->alignCenter()
                    ->width(60)
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y, H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('subject_id')
                    ->label('Filter Mata Pelajaran')
                    ->options(Subject::pluck('name', 'id'))
                    ->searchable(),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif Saja')
                    ->falseLabel('Nonaktif Saja'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
