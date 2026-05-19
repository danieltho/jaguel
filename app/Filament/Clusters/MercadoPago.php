<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class MercadoPago extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Mercado Pago';

    protected static ?string $clusterBreadcrumb = 'Mercado Pago';

    protected static string|null|\UnitEnum $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 20;
}
