<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->extraAttributes([
                'wire:loading.attr' => 'disabled',
                'wire:target' => 'upload,removeUploadedFile',
            ]);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->extraAttributes([
                'wire:loading.attr' => 'disabled',
                'wire:target' => 'upload,removeUploadedFile',
            ]);
    }
}
