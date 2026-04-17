<?php

namespace App\Filament\Resources\PaymentMethods;

use App\Enums\PaymentMethodTypeEnum;
use App\Filament\Resources\PaymentMethods\Pages\ManagePaymentMethods;
use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Medios de Pago';

    protected static ?string $modelLabel = 'Medio de Pago';

    protected static ?string $pluralModelLabel = 'Medios de Pago';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo')
                    ->options(PaymentMethodTypeEnum::class)
                    ->required()
                    ->live()
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),

                TextInput::make('subtitle')
                    ->label('Subtitulo')
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Descripcion')
                    ->columnSpanFull(),

                TextInput::make('max_installments')
                    ->label('Cantidad maxima de cuotas')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(12)
                    ->visible(fn ($get) => $get('type') === PaymentMethodTypeEnum::CREDIT_CARD->value)
                    ->helperText('Solo para tarjetas de credito/debito'),

                Section::make('Configuracion Mercado Pago')
                    ->schema([
                        TextInput::make('mercadopago_public_key')
                            ->label('Public Key')
                            ->maxLength(255)
                            ->helperText('Public Key de Mercado Pago'),

                        TextInput::make('mercadopago_access_token')
                            ->label('Access Token')
                            ->password()
                            ->maxLength(255)
                            ->helperText('Access Token de Mercado Pago'),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('type') === PaymentMethodTypeEnum::CREDIT_CARD->value)
                    ->collapsible(),

                TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subtitle')
                    ->label('Subtitulo')
                    ->searchable(),

                TextColumn::make('max_installments')
                    ->label('Cuotas')
                    ->placeholder('-'),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePaymentMethods::route('/'),
        ];
    }
}
