<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Pendientes en cola</x-slot>
        <x-slot name="description">
            Correos encolados esperando ser enviados por el worker de la cola.
        </x-slot>

        @if (count($this->pendingJobs) === 0)
            <p class="text-sm text-gray-500 dark:text-gray-400">No hay correos pendientes en la cola.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 dark:text-gray-400">
                        <th class="py-2">Tipo</th>
                        <th class="py-2">Cola</th>
                        <th class="py-2">Intentos</th>
                        <th class="py-2">Disponible</th>
                        <th class="py-2">Creado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->pendingJobs as $job)
                        <tr class="border-t border-gray-200 dark:border-white/10">
                            <td class="py-2">{{ $job['mailable'] ?? $job['display_name'] ?? 'Correo' }}</td>
                            <td class="py-2">{{ $job['queue'] }}</td>
                            <td class="py-2">{{ $job['attempts'] }}</td>
                            <td class="py-2">{{ $job['available_at']->format('d/m/Y H:i') }}</td>
                            <td class="py-2">{{ $job['created_at']->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Fallidos</x-slot>
        <x-slot name="description">
            Correos que no pudieron enviarse. Podés reintentarlos o eliminarlos.
        </x-slot>

        @if (count($this->failedJobs) === 0)
            <p class="text-sm text-gray-500 dark:text-gray-400">No hay correos fallidos.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 dark:text-gray-400">
                        <th class="py-2">Tipo</th>
                        <th class="py-2">Error</th>
                        <th class="py-2">Fecha</th>
                        <th class="py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->failedJobs as $job)
                        <tr class="border-t border-gray-200 dark:border-white/10 align-top">
                            <td class="py-2">{{ $job['mailable'] ?? 'Correo' }}</td>
                            <td class="py-2 max-w-md truncate" title="{{ $job['exception'] }}">{{ $job['exception'] }}</td>
                            <td class="py-2 whitespace-nowrap">{{ $job['failed_at']->format('d/m/Y H:i') }}</td>
                            <td class="py-2">
                                <div class="flex justify-end gap-2">
                                    <x-filament::button size="xs" wire:click="retry('{{ $job['uuid'] }}')">
                                        Reintentar
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="danger" wire:click="forget('{{ $job['uuid'] }}')">
                                        Eliminar
                                    </x-filament::button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-panels::page>
