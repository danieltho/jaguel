<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Shipping;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ShippingSenderSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Datos de envío (remitente)';

    protected static ?string $cluster = Shipping::class;

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.shipping-sender-settings';

    public ?array $data = [];

    public const GROUP = 'shipping_sender';

    public function mount(SettingsService $settings): void
    {
        $current = $settings->group(self::GROUP);

        $this->form->fill([
            'name' => $current['name'] ?? null,
            'phone' => $current['phone'] ?? null,
            'address' => $current['address'] ?? null,
            'city' => $current['city'] ?? null,
            'state' => $current['state'] ?? null,
            'postal_code' => $current['postal_code'] ?? null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Remitente')
                    ->description('Estos datos aparecerán en la etiqueta de envío de cada pedido.')
                    ->schema([
                        TextInput::make('name')->label('Nombre / Razón social')->required()->maxLength(255),
                        TextInput::make('phone')->label('Teléfono')->maxLength(50),
                        TextInput::make('address')->label('Dirección')->required()->maxLength(255),
                        TextInput::make('city')->label('Ciudad')->maxLength(255),
                        TextInput::make('state')->label('Provincia')->maxLength(255),
                        TextInput::make('postal_code')->label('Código postal')->maxLength(20),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(SettingsService $settings): void
    {
        $values = $this->form->getState();

        $settings->setMany(self::GROUP, $values);

        Notification::make()
            ->title('Datos del remitente guardados')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Guardar')->submit('save'),
        ];
    }
}
