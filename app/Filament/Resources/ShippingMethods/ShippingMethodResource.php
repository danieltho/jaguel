<?php

namespace App\Filament\Resources\ShippingMethods;

use App\Filament\Resources\ShippingMethods\Pages\ManageShippingMethods;
use App\Models\ShippingMethod;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ShippingMethodResource extends Resource
{
    protected static ?string $model = ShippingMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Medios de envío';

    protected static ?string $modelLabel = 'Medio de envío';

    protected static ?string $pluralModelLabel = 'Medios de envío';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get, ?ShippingMethod $record) {
                                if (! $record && empty($get('code'))) {
                                    $set('code', Str::slug($state, '_'));
                                }
                            }),
                        TextInput::make('code')
                            ->label('Código interno')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Identificador único. Solo letras, números y guiones bajos.')
                            ->rule('regex:/^[a-z0-9_]+$/i'),
                        Select::make('delivery_type')
                            ->label('Tipo de entrega')
                            ->options([
                                'pickup' => 'Retiro en punto',
                                'shipping' => 'Envío a domicilio',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('price')
                            ->label('Costo (ARS)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('Sin decimales. Usá 0 para envío gratis.'),
                        TextInput::make('description')
                            ->label('Descripción')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('days_label')
                            ->label('Plazo estimado')
                            ->placeholder('Ej: 5-7 días hábiles')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('code')->label('Código')->badge()->color('gray'),
                TextColumn::make('delivery_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'pickup' ? 'Retiro' : 'Envío')
                    ->color(fn (string $state) => $state === 'pickup' ? 'gray' : 'info'),
                TextColumn::make('price')->label('Costo')->money('ARS')->sortable(),
                TextColumn::make('days_label')->label('Plazo')->toggleable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->defaultSort('sort_order')
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
            'index' => ManageShippingMethods::route('/'),
        ];
    }
}
