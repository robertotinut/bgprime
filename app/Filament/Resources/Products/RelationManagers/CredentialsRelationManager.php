<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\ProductCredential;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CredentialsRelationManager extends RelationManager
{
    protected static string $relationship = 'credentials';

    protected static ?string $title = 'Preloaded Account Credentials (Instant Pool)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label('Email / Username')
                    ->required(),

                TextInput::make('password')
                    ->label('Password')
                    ->required(),

                Textarea::make('notes')
                    ->label('Catatan Akun')
                    ->placeholder('Contoh: Private profile, jangan ubah email.')
                    ->columnSpanFull(),

                Toggle::make('is_used')
                    ->label('Sudah Terpakai')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                TextColumn::make('username')
                    ->label('Email / Username')
                    ->searchable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30),

                IconColumn::make('is_used')
                    ->label('Used')
                    ->boolean(),

                TextColumn::make('order.invoice_number')
                    ->label('Used For Order')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->label('Added At'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Stok Akun')
                    ->after(function (): void {
                        $this->getOwnerRecord()->syncStockFromCredentials();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function (): void {
                        $this->getOwnerRecord()->syncStockFromCredentials();
                    }),
                DeleteAction::make()
                    ->after(function (): void {
                        $this->getOwnerRecord()->syncStockFromCredentials();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function (): void {
                            $this->getOwnerRecord()->syncStockFromCredentials();
                        }),
                ]),
            ]);
    }
}
