<?php

namespace App\Filament\Pages;

use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class SmtpSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Correo (SMTP)';

    protected static ?string $title = 'Configuración de correo (SMTP)';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.pages.smtp-settings';

    public ?array $data = [];

    public const GROUP = 'smtp';

    public const ENCRYPTED_KEYS = ['password'];

    public function mount(SettingsService $settings): void
    {
        $current = $settings->group(self::GROUP);

        $this->form->fill([
            'enabled' => filter_var($current['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'host' => $current['host'] ?? null,
            'port' => $current['port'] ?? 587,
            'username' => $current['username'] ?? null,
            'password' => $current['password'] ?? null,
            'encryption' => $current['encryption'] ?? 'tls',
            'from_address' => $current['from_address'] ?? null,
            'from_name' => $current['from_name'] ?? null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Estado')
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Habilitar envío por SMTP')
                            ->helperText('Si está apagado, los correos se escriben en el log (driver "log").'),
                    ]),

                Section::make('Servidor SMTP')
                    ->schema([
                        TextInput::make('host')
                            ->label('Host')
                            ->placeholder('smtp.gmail.com')
                            ->maxLength(255),
                        TextInput::make('port')
                            ->label('Puerto')
                            ->numeric()
                            ->placeholder('587'),
                        Select::make('encryption')
                            ->label('Cifrado')
                            ->options([
                                'tls' => 'TLS (puerto 587)',
                                'ssl' => 'SSL (puerto 465)',
                                'none' => 'Ninguno',
                            ])
                            ->native(false),
                        TextInput::make('username')
                            ->label('Usuario')
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('Se almacena encriptada.'),
                    ])
                    ->columns(2),

                Section::make('Remitente por defecto')
                    ->schema([
                        TextInput::make('from_address')
                            ->label('Email')
                            ->email()
                            ->placeholder('hola@midominio.com')
                            ->maxLength(255),
                        TextInput::make('from_name')
                            ->label('Nombre')
                            ->placeholder('El Jaguel')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(SettingsService $settings): void
    {
        $values = $this->form->getState();

        $settings->setMany(self::GROUP, $values, self::ENCRYPTED_KEYS);

        Notification::make()
            ->title('Configuración SMTP guardada')
            ->body('Los cambios pueden tardar unos segundos en aplicarse.')
            ->success()
            ->send();
    }

    public function sendTest(SettingsService $settings): void
    {
        $values = $this->form->getState();
        $settings->setMany(self::GROUP, $values, self::ENCRYPTED_KEYS);

        app(\App\Services\MailConfigurator::class)->apply();

        $to = $values['from_address'] ?? null;

        if (! $to) {
            Notification::make()
                ->title('Falta el email remitente para enviar la prueba')
                ->warning()
                ->send();

            return;
        }

        try {
            Mail::raw('Este es un correo de prueba enviado desde el panel de administración.', function ($message) use ($to) {
                $message->to($to)->subject('Prueba SMTP');
            });

            Notification::make()
                ->title('Correo de prueba enviado a '.$to)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error enviando el correo')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Guardar')->submit('save'),
            Action::make('sendTest')
                ->label('Enviar correo de prueba')
                ->color('gray')
                ->action('sendTest'),
        ];
    }
}
