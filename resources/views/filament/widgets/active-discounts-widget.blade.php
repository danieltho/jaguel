<x-filament-widgets::widget>
    <x-filament::section>
        @foreach($this->getDiscounts() as $discount)
            <div class="flex items-center gap-4 p-4 rounded-lg bg-success-50 dark:bg-success-950 border border-success-200 dark:border-success-800 mb-2 last:mb-0">
                <div class="flex-shrink-0">
                    <x-filament::icon
                        icon="heroicon-o-tag"
                        class="h-8 w-8 text-success-500"
                    />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-success-700 dark:text-success-300">
                        {{ $discount->name }}
                    </h3>
                    <p class="text-sm text-success-600 dark:text-success-400">
                        <span class="font-bold text-xl">{{ $this->formatDiscount($discount) }}</span> de descuento en todos los productos
                    </p>
                    <p class="text-xs text-success-500 dark:text-success-500 mt-1">
                        {{ $this->formatExpiration($discount) }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                        Activo
                    </span>
                </div>
            </div>
        @endforeach
    </x-filament::section>
</x-filament-widgets::widget>
