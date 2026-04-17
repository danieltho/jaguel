<?php

namespace Database\Seeders;

use App\Enums\PaymentMethodTypeEnum;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'type' => PaymentMethodTypeEnum::CREDIT_CARD,
                'title' => 'Tarjeta de Credito / Debito',
                'subtitle' => 'Hasta 6 cuotas sin interes',
                'description' => 'Pago seguro a traves de Mercado Pago.',
                'sort_order' => 1,
            ],
            [
                'type' => PaymentMethodTypeEnum::BANK_TRANSFER,
                'title' => 'Transferencia Bancaria',
                'subtitle' => 'Acreditacion en 24-48hs',
                'description' => "CBU: 0000000000000000000000\nAlias: eljaguel.pagos\nTitular: El Jaguel SRL\nCUIT: 00-00000000-0\n\nEnvia el comprobante por mail a pagos@eljaguel.com",
                'sort_order' => 2,
            ],
            [
                'type' => PaymentMethodTypeEnum::CASH_SHOWROOM,
                'title' => 'Efectivo en Showroom',
                'subtitle' => 'Paga al retirar tu pedido',
                'description' => "Abonalo al momento de retirar en nuestro showroom.\n\nDireccion: Calle 37 N° 1242, Miramar, Buenos Aires\nHorarios: Lunes a Viernes de 9 a 18hs",
                'sort_order' => 3,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['type' => $method['type']],
                array_merge($method, ['is_active' => true])
            );
        }
    }
}
