<?php

namespace App\Filament\Resources\SentEmails;

use App\Enums\SentEmailStatusEnum;
use App\Filament\Resources\SentEmails\Pages\ListSentEmails;
use App\Filament\Resources\SentEmails\Pages\ViewSentEmail;
use App\Models\SentEmail;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SentEmailResource extends Resource
{
    protected static ?string $model = SentEmail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Correos enviados';

    protected static ?string $modelLabel = 'Correo enviado';

    protected static ?string $pluralModelLabel = 'Correos enviados';

    protected static string|null|\UnitEnum $navigationGroup = 'Notificaciones';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Correo')
                    ->schema([
                        TextEntry::make('to_address')
                            ->label('Destinatario')
                            ->copyable(),
                        TextEntry::make('subject')
                            ->label('Asunto')
                            ->placeholder('-'),
                        TextEntry::make('mailable')
                            ->label('Tipo')
                            ->placeholder('-'),
                        TextEntry::make('mailer')
                            ->label('Mailer'),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge(),
                        TextEntry::make('message_id')
                            ->label('Message ID')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('created_at')
                            ->label('Registrado')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('sent_at')
                            ->label('Enviado')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Error')
                    ->schema([
                        TextEntry::make('error')
                            ->label('')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (SentEmail $record): bool => filled($record->error))
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('to_address')
                    ->label('Destinatario')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('-'),

                TextColumn::make('mailable')
                    ->label('Tipo')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('mailer')
                    ->label('Mailer')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label('Enviado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(SentEmailStatusEnum::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSentEmails::route('/'),
            'view' => ViewSentEmail::route('/{record}'),
        ];
    }
}
