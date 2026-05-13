<?php

namespace App\Filament\Pages;

use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class MercadoPagoSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Mercado Pago';

    protected static ?string $title = 'Configuración Mercado Pago';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.mercado-pago-settings';

    public ?array $data = [];

    public const GROUP = 'mercadopago';

    public const ENCRYPTED_KEYS = ['access_token', 'webhook_secret'];

    public function mount(SettingsService $settings): void
    {
        $current = $settings->group(self::GROUP);

        $this->form->fill([
            'environment' => $current['environment'] ?? 'sandbox',
            'public_key' => $current['public_key'] ?? null,
            'access_token' => $current['access_token'] ?? null,
            'webhook_secret' => $current['webhook_secret'] ?? null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Entorno')
                    ->schema([
                        Select::make('environment')
                            ->label('Ambiente')
                            ->options([
                                'sandbox' => 'Sandbox (pruebas)',
                                'production' => 'Producción',
                            ])
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Credenciales')
                    ->description('Obtené las credenciales desde el panel de Mercado Pago. El Access Token se guarda encriptado.')
                    ->schema([
                        TextInput::make('public_key')
                            ->label('Public Key')
                            ->maxLength(255)
                            ->helperText('Comienza con APP_USR- o TEST-'),

                        TextInput::make('access_token')
                            ->label('Access Token')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('Se almacena encriptado. Comienza con APP_USR- o TEST-'),

                        TextInput::make('webhook_secret')
                            ->label('Webhook Secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('Secret para validar firma de webhooks. Configurado en panel MP > Webhooks.'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(SettingsService $settings): void
    {
        $values = $this->form->getState();

        $settings->setMany(self::GROUP, $values, self::ENCRYPTED_KEYS);

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->submit('save'),
        ];
    }
}
