import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    EnvelopeSimple,
    MapPin,
    Truck,
    Info,
    X,
    CaretRight,
} from '@phosphor-icons/react';
import CheckoutLayout from '../../Shared/components/CheckoutLayout/CheckoutLayout';
import { PrimaryButton, BackButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';
import OrderSummary from '../../Shared/components/CheckoutLayout/OrderSummary';
import { SectionHeading } from '../../Shared/components/CheckoutLayout/CheckoutPrimitives';

const SHIPPING_LABELS = {
    punto_retiro: 'Punto de Retiro',
    correo_argentino: 'Correo Argentino',
};

function ReviewRow({ icon: Icon, title, description, changeHref }) {
    return (
        <div className="flex items-center gap-6 px-5 py-2.5">
            <Icon size={24} className="shrink-0 text-neutral-500" />
            <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                {title && <p className="text-sm font-semibold text-neutral-500">{title}</p>}
                {description && <p className="text-xs text-neutral-500">{description}</p>}
            </div>
            {changeHref && (
                <Link
                    href={changeHref}
                    className="shrink-0 text-sm font-semibold text-neutral-500 hover:underline"
                >
                    Cambiar
                </Link>
            )}
        </div>
    );
}

export default function Payment({ contact, delivery, recipient, paymentMethods, summary }) {
    const [processing, setProcessing] = useState(false);
    const [showAlert, setShowAlert] = useState(true);
    const [selectedMethodId, setSelectedMethodId] = useState(paymentMethods[0]?.id || null);

    const selectedMethod = paymentMethods.find((m) => m.id === selectedMethodId);
    const isCreditCard = selectedMethod?.type === 'credit_card';

    const handlePlaceOrder = () => {
        if (!selectedMethodId) return;
        setProcessing(true);
        router.post(
            '/checkout/pago',
            { payment_method_id: selectedMethodId },
            { onFinish: () => setProcessing(false) },
        );
    };

    const addressLine = [recipient.address, recipient.department].filter(Boolean).join(', ');
    const locationLine = [
        recipient.city,
        recipient.state,
        delivery.postal_code ? `CP ${delivery.postal_code}` : '',
    ]
        .filter(Boolean)
        .join(', ');
    const contactLine = [locationLine, recipient.phone ? `+${recipient.phone}` : '']
        .filter(Boolean)
        .join(' - ');

    return (
        <CheckoutLayout currentStep={3}>
            <Head title="Medios de Pago" />
            <div className="mx-auto w-full max-w-[1320px] px-15 py-[30px]">
                <div className="flex flex-col gap-2.5">
                    <BackButton href="/checkout/destinatario" />

                    <div className="flex items-start justify-between gap-6">
                        <div className="flex w-[872px] flex-col items-end gap-6">
                            {showAlert && (
                                <div className="flex h-10 w-full items-center justify-between rounded-lg border-l-4 border-oxido-300 bg-oxido-50 px-3">
                                    <div className="flex items-center gap-2">
                                        <Info size={16} className="text-oxido-300" />
                                        <p className="text-xs font-semibold text-oxido-300">
                                            Hacemos Factura A o B. Se lo informaremos vía mail.
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => setShowAlert(false)}
                                        aria-label="Cerrar aviso"
                                    >
                                        <X size={16} className="text-oxido-300" />
                                    </button>
                                </div>
                            )}

                            <div className="flex w-full flex-col gap-4">
                                <ReviewRow icon={EnvelopeSimple} description={contact.email} />
                                <ReviewRow
                                    icon={MapPin}
                                    title={addressLine || 'Dirección'}
                                    description={contactLine}
                                    changeHref="/checkout/destinatario"
                                />
                                <ReviewRow
                                    icon={Truck}
                                    title={SHIPPING_LABELS[delivery.shipping_method] || delivery.shipping_method}
                                    description="Listo entre 3-5 días hábiles"
                                    changeHref={
                                        contact.delivery_type === 'pickup'
                                            ? '/checkout/contacto'
                                            : '/checkout/entrega'
                                    }
                                />
                            </div>

                            <div className="w-full">
                                <SectionHeading>Medios de Pago</SectionHeading>
                            </div>

                            <div className="flex w-full flex-col gap-6">
                                {paymentMethods.map((method) => {
                                    const isSelected = selectedMethodId === method.id;
                                    return (
                                        <button
                                            key={method.id}
                                            type="button"
                                            onClick={() => setSelectedMethodId(method.id)}
                                            className={`flex h-14 w-full items-center gap-6 rounded-[10px] border px-5 py-2.5 text-left transition-colors ${
                                                isSelected
                                                    ? 'border-black'
                                                    : 'border-neutral-300 hover:border-neutral-400'
                                            }`}
                                            aria-pressed={isSelected}
                                        >
                                            <CaretRight
                                                size={20}
                                                weight="bold"
                                                className={`shrink-0 text-neutral-500 transition-transform ${
                                                    isSelected ? 'rotate-180' : ''
                                                }`}
                                            />
                                            <div className="flex min-w-0 flex-1 flex-col">
                                                <p className="text-sm font-semibold text-neutral-500">
                                                    {method.title}
                                                </p>
                                            </div>
                                            {method.subtitle && (
                                                <p className="shrink-0 text-sm font-semibold text-neutral-500">
                                                    {method.subtitle}
                                                </p>
                                            )}
                                        </button>
                                    );
                                })}

                                {isCreditCard && (
                                    <p className="w-full text-center text-xs text-neutral-500">
                                        Serás redirigido a Mercado Pago al realizar el pedido.
                                    </p>
                                )}

                                <PrimaryButton
                                    type="button"
                                    onClick={handlePlaceOrder}
                                    disabled={processing || !selectedMethodId}
                                    className="w-[280px] self-end"
                                >
                                    {processing
                                        ? 'Procesando...'
                                        : isCreditCard
                                            ? 'Realizar Pago'
                                            : 'Realizar Pedido'}
                                </PrimaryButton>
                            </div>
                        </div>

                        <aside className="w-[424px] shrink-0 px-2.5 py-4">
                            <OrderSummary {...summary} showCouponInput={false} />
                        </aside>
                    </div>
                </div>
            </div>
        </CheckoutLayout>
    );
}
