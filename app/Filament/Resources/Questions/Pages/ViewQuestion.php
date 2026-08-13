<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestion extends ViewRecord
{
    protected static string $resource = QuestionResource::class;

    public function getTitle(): string
    {
        return 'Lihat Soal ' . $this->record->subject->name . ' - ' . $this->record->getKey();
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
