<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MailQueue extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'Cola de correos';

    protected static ?string $title = 'Cola de correos';

    protected static string|null|\UnitEnum $navigationGroup = 'Notificaciones';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.mail-queue';

    /**
     * Correos pendientes de envío en la cola (tabla jobs).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPendingJobsProperty(): array
    {
        return DB::table('jobs')
            ->where('payload', 'like', '%SendQueuedMailable%')
            ->orderByDesc('id')
            ->get()
            ->map(function ($job): array {
                $payload = json_decode($job->payload, true) ?: [];

                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'mailable' => $this->extractMailable($job->payload),
                    'attempts' => $job->attempts,
                    'available_at' => Carbon::createFromTimestamp($job->available_at),
                    'created_at' => Carbon::createFromTimestamp($job->created_at),
                    'display_name' => $payload['displayName'] ?? null,
                ];
            })
            ->all();
    }

    /**
     * Correos que fallaron al enviarse (tabla failed_jobs).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFailedJobsProperty(): array
    {
        return DB::table('failed_jobs')
            ->where('payload', 'like', '%SendQueuedMailable%')
            ->orderByDesc('failed_at')
            ->get()
            ->map(function ($job): array {
                return [
                    'uuid' => $job->uuid,
                    'queue' => $job->queue,
                    'mailable' => $this->extractMailable($job->payload),
                    'exception' => strtok($job->exception, "\n"),
                    'failed_at' => Carbon::parse($job->failed_at),
                ];
            })
            ->all();
    }

    public function retry(string $uuid): void
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        Notification::make()
            ->title('Correo reencolado para reintento')
            ->success()
            ->send();
    }

    public function forget(string $uuid): void
    {
        Artisan::call('queue:forget', ['id' => $uuid]);

        Notification::make()
            ->title('Correo eliminado de la cola de fallidos')
            ->success()
            ->send();
    }

    /**
     * Extrae el nombre de la clase Mailable del payload serializado, sin
     * deserializar (evita resolver modelos contra la base de datos).
     */
    private function extractMailable(string $payload): ?string
    {
        if (preg_match('/App\\\\\\\\Mail\\\\\\\\[A-Za-z0-9_]+/', $payload, $matches)) {
            return str_replace('\\\\', '\\', $matches[0]);
        }

        return null;
    }
}
