<?php

namespace App;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Override;

enum StudentStatusEnum: int implements HasLabel
{
    case Active = 1;
    case Graduated = 2;
    case Leaved = 3;
    case Moved = 4;
    case DroppedOut = 5;

    #[Override]
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Graduated => 'Alumni',
            self::Leaved => 'Cuti',
            self::Moved => 'Pindah',
            self::DroppedOut => 'Dikeluarkan',
            default => 'Invalid',
        };
    }
}
