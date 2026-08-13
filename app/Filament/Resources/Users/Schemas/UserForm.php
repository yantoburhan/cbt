<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    // upload foto
                    FileUpload::make('photo_path')
                        ->label('Upload foto profil')
                        ->image() // khusus upload gambar
                        ->avatar()  // rezise otomatis dan circular
                        ->disk('public') // partisi storage
                        ->directory('user-photos') // nama folder
                        ->maxSize(1024) // ukuran max 1 mb
                        ->imageEditor()
                        ->columnSpanFull()
                        ->aligncenter(),
                    TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->columnSpanFull(),
                    TextInput::make('email')
                        ->label('Email address')
                        ->unique('users', 'email')
                        ->prefix('@')
                        ->email(),
                    TextInput::make('username')
                        ->label('Login Username')
                        ->unique(
                            table: 'users',
                            column: 'username'
                        )
                        ->placeholder('Digunakan untuk login Akun')
                        ->required(),
                    TextInput::make('phone')
                        ->label('Nomor Telepon')
                        ->prefixIcon(Heroicon::OutlinedPhone)
                        ->tel(),
                    TextInput::make('password')
                        ->hiddenOn('edit')  //disembunyikan di halaman edit
                        ->revealable() //tampilkan password
                        ->password()
                        ->required(),
                ])
            ]);
    }
}
