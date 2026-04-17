import { Head, useForm } from '@inertiajs/react';
import { MapPin, Truck } from '@phosphor-icons/react';
import CheckoutLayout from '../../Shared/components/CheckoutLayout/CheckoutLayout';
import CheckoutInput from '../../Shared/components/CheckoutLayout/CheckoutInput';
import { PrimaryButton, BackButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';
import OrderSummary from '../../Shared/components/CheckoutLayout/OrderSummary';

const DELIVERY_OPTIONS = [
    {
        id: 'pickup',
        label: 'Retiro en Punto de Venta',
        icon: MapPin,
        description: 'Retira tu pedido en nuestro local',
    },
    {
        id: 'shipping',
        label: 'Envio a domicilio',
        icon: Truck,
        description: 'Recibilo en la puerta de tu casa',
    },
];

export default function Contact({ customer, summary }) {
    const { data, setData, post, processing, errors } = useForm({
        email: customer.email || '',
        delivery_type: 'pickup',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/checkout/contacto');
    };

    return (
        <CheckoutLayout currentStep={1}>
            <Head title="Datos de Contacto" />
            <div className="max-w-[1320px] mx-auto px-4">
                <div className="flex gap-6">
                    {/* Form */}
                    <div className="flex-1">
                        <BackButton href="/carrito" />

                        <form onSubmit={handleSubmit} className="mt-6 flex flex-col gap-6">
                            {/* Contact */}
                            <div>
                                <h2 className="text-[32px] font-medium text-neutral-500 mb-4">Datos de Contacto</h2>
                                <CheckoutInput
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="Email"
                                    error={errors.email}
                                />
                            </div>

                            {/* Delivery type */}
                            <div>
                                <h2 className="text-[32px] font-medium text-neutral-500 mb-4">Tipo de Entrega</h2>
                                <div className="flex flex-col gap-3">
                                    {DELIVERY_OPTIONS.map((option) => {
                                        const Icon = option.icon;
                                        const isSelected = data.delivery_type === option.id;

                                        return (
                                            <label
                                                key={option.id}
                                                className={`flex items-center gap-4 p-4 rounded-[8px] border cursor-pointer transition-colors ${
                                                    isSelected
                                                        ? 'border-oxido-300 bg-oxido-50/30'
                                                        : 'border-neutral-300 hover:border-neutral-400'
                                                }`}
                                            >
                                                <input
                                                    type="radio"
                                                    name="delivery_type"
                                                    value={option.id}
                                                    checked={isSelected}
                                                    onChange={() => setData('delivery_type', option.id)}
                                                    className="sr-only"
                                                />
                                                <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                                                    isSelected ? 'bg-oxido-300 text-oxido-50' : 'bg-neutral-100 text-neutral-400'
                                                }`}>
                                                    <Icon size={20} weight="bold" />
                                                </div>
                                                <div className="flex-1">
                                                    <p className="text-sm font-semibold text-neutral-500">{option.label}</p>
                                                    <p className="text-sm text-neutral-400">{option.description}</p>
                                                </div>
                                            </label>
                                        );
                                    })}
                                </div>
                                {errors.delivery_type && <p className="text-carmesi-300 text-xs mt-1">{errors.delivery_type}</p>}
                            </div>

                            <PrimaryButton type="submit" disabled={processing}>
                                Continuar
                            </PrimaryButton>
                        </form>
                    </div>

                    {/* Summary sidebar */}
                    <div className="w-[424px] shrink-0 hidden lg:block">
                        <OrderSummary {...summary} />
                    </div>
                </div>
            </div>
        </CheckoutLayout>
    );
}
