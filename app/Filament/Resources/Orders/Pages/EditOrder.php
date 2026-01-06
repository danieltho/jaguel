<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convertir de centavos a pesos para mostrar
        $data['price'] = ($data['price'] ?? 0) / 100;
        $data['subtotal'] = ($data['subtotal'] ?? 0) / 100;
        $data['discount_amount'] = ($data['discount_amount'] ?? 0) / 100;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convertir a centavos para guardar
        $data['price'] = ($data['price'] ?? 0) * 100;
        $data['subtotal'] = ($data['subtotal'] ?? 0) * 100;
        $data['discount_amount'] = ($data['discount_amount'] ?? 0) * 100;

        return $data;
    }
}
