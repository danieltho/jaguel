<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-8 flex justify-end gap-2">
            <x-filament::button type="submit">
                Guardar
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
