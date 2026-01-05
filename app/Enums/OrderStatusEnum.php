<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case IN_PREPARATION = 'En preparacion';
    case PREPARATED = 'Preparado'; // ?
    case PREPARATED_PENDING_SHIPPING = 'Preparado sin despachar'; // ?
    case SHIPPING = 'Despachada a transporte';

    case READY_PICKUP = 'Lista para retiro';

    case DELIVERED = 'Entregado';

}
