<?php

namespace App\Filament\Pages;

use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ContactSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $navigationLabel = 'Contacto';

    protected static ?string $title = 'Datos de contacto';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 45;

    protected string $view = 'filament.pages.contact-settings';

    public ?array $data = [];

    public const GROUP = 'contact';

    public function mount(SettingsService $settings): void
    {
        $current = $settings->group(self::GROUP);

        $this->form->fill([
            'whatsapp' => $current['whatsapp'] ?? null,
            'email' => $current['email'] ?? null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contacto de la tienda')
                    ->description('Estos datos se muestran en el pie de los correos enviados a los clientes.')
                    ->schema([
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->placeholder('+54 9 223 312-3981')
                            ->helperText('Número con código de país, ej: +54 9 223 312-3981.')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email de contacto')
                            ->email()
                            ->placeholder('hola@midominio.com')
                            ->maxLength(255),
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
            ->title('Datos de contacto guardados')
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
