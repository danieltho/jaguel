<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Previsualizar en la web')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(fn (): string => route('products.show', ['slug' => $this->record->slug]))
                ->openUrlInNewTab(),
            DeleteAction::make()
                ->before(function (Product $record, DeleteAction $action): void {
                    if ($record->orderItems()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede eliminar el producto')
                            ->body('Este producto tiene compras realizadas y no puede ser eliminado.')
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->extraAttributes([
                'wire:loading.attr' => 'disabled',
                'wire:target' => 'upload,removeUploadedFile',
            ]);
    }
}
