import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { MapPin, Truck } from '@phosphor-icons/react';
import CheckoutLayout from '../../Shared/components/CheckoutLayout/CheckoutLayout';
import CheckoutInput from '../../Shared/components/CheckoutLayout/CheckoutInput';
import { PrimaryButton, OutlineButton, BackButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';
import OrderSummary from '../../Shared/components/CheckoutLayout/OrderSummary';
import {
    SectionHeading,
    Checkbox,
    RadioOption,
} from '../../Shared/components/CheckoutLayout/CheckoutPrimitives';

const DELIVERY_OPTIONS = [
    {
        id: 'pickup',
        label: 'Retiro en Punto de Venta',
        icon: MapPin,
        description: 'Calle 37 N° 1242, Miramar, Buenos Aires.',
    },
    {
        id: 'shipping',
        label: 'Envío a domicilio',
        icon: Truck,
        description: 'Recibilo en la puerta de tu casa.',
    },
];

export default function Contact({ customer, summary }) {
    const { auth } = usePage().props || {};
    const isAuthenticated = !!auth?.customer;

    const { data, setData, post, processing, errors } = useForm({
        email: customer.email || '',
        wants_newsletter: false,
        delivery_type: 'pickup',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/checkout/contacto');
    };

    return (
        <CheckoutLayout currentStep={1}>
            <Head title="Datos de Contacto" />
            <div className="mx-auto w-full max-w-[1320px] px-15 py-[30px]">
                <form onSubmit={handleSubmit} className="flex flex-col gap-2.5">
                    <BackButton href="/carrito" />

                    <div className="flex items-start justify-between gap-6">
                        <div className="flex w-[872px] flex-col items-end gap-4">
                            <div className="flex w-full flex-col gap-2.5">
                                <SectionHeading>Datos de Contacto</SectionHeading>

                                <CheckoutInput
                                    label="Email*"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="ejemplo@correo.com"
                                    error={errors.email}
                                    required
                                />

                                <Checkbox
                                    id="wants-newsletter"
                                    checked={data.wants_newsletter}
                                    onChange={(e) => setData('wants_newsletter', e.target.checked)}
                                    label="Quiero recibir ofertas y novedades por e-mail"
                                />
                            </div>

                            {!isAuthenticated && (
                                <div className="self-start">
                                    <OutlineButton as={Link} href="/cuenta/registro">
                                        Quiero ser Usuario
                                    </OutlineButton>
                                </div>
                            )}

                            <div className="flex w-full flex-col">
                                <SectionHeading>Entrega</SectionHeading>

                                {DELIVERY_OPTIONS.map((option) => (
                                    <RadioOption
                                        key={option.id}
                                        name="delivery_type"
                                        checked={data.delivery_type === option.id}
                                        onChange={() => setData('delivery_type', option.id)}
                                        icon={option.icon}
                                        title={option.label}
                                        description={option.description}
                                    />
                                ))}
                                {errors.delivery_type && (
                                    <p className="pl-5 text-xs text-carmesi-300">{errors.delivery_type}</p>
                                )}
                            </div>

                            <PrimaryButton type="submit" disabled={processing} className="w-[280px]">
                                Continuar
                            </PrimaryButton>
                        </div>

                        <aside className="w-[424px] shrink-0 px-2.5 py-4">
                            <OrderSummary {...summary} />
                        </aside>
                    </div>
                </form>
            </div>
        </CheckoutLayout>
    );
}
