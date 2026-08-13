<?php

namespace App\Filament\Resources\Subjects;

use App\Filament\Resources\Subjects\Pages\ManageSubjects;
use App\Models\Subject;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmarkSquare;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Database';

    protected static ?string $modelLabel = 'Subjects';

    protected static ?string $navigationLabel = 'Subjects Database';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique('subjects', 'name')
                    ->label('Subject Name')
                    ->columnSpanFull()
                    ->placeholder('Chemistry'),
                Textarea::make('description')
                    ->default(null)
                    ->label('Description')
                    ->placeholder('This subject is about chemistry')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->inline(false)
                    ->label('Active')
                    ->default(true)
                    ->live()
                    ->helperText(
                        fn ($state) => $state
                        ? 'This subject is can be used in the examination'
                        : 'This subject is cannot be used in the examination'),
                    // ->helperText('The active subject can be used in the examination.'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Subject Name'),
                IconEntry::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextEntry::make('description')
                    ->label('Description')
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Subject $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime('d F Y H:i:s')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime('d F Y H:i:s')
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('No')
                    ->rowIndex()
                    ->width('40'),
                TextColumn::make('name')
                    ->label('Subject Name')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Subject Status')
                    ->placeholder('Select Subject Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
                    ->native(false),
                TrashedFilter::make()
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSubjects::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
