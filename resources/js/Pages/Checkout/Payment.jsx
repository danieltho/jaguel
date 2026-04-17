import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { EnvelopeSimple, MapPin, Truck, Info, X, CreditCard, Bank, Storefront } from '@phosphor-icons/react';
import CheckoutLayout from '../../Shared/components/CheckoutLayout/CheckoutLayout';
import { BackButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';
import OrderSummary from '../../Shared/components/CheckoutLayout/OrderSummary';

const SHIPPING_LABELS = {
    punto_retiro: 'Punto de Retiro',
    correo_argentino: 'Correo Argentino',
};

const TYPE_ICONS = {
    credit_card: CreditCard,
    bank_transfer: Bank,
    cash_showroom: Storefront,
};

function ReviewItem({ icon: Icon, title, description, changeHref }) {
    return (
        <div className="flex items-center gap-6 px-5 py-3">
            <Icon size={24} className="text-neutral-500 shrink-0" />
            <div className="flex-1 min-w-0">
                {title && <p className="text-sm font-semibold text-neutral-500">{title}</p>}
                {description && <p className="text-sm text-neutral-500">{description}</p>}
            </div>
            {changeHref && (
                <Link href={changeHref} className="text-sm font-semibold text-neutral-500 hover:underline shrink-0">
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
        router.post('/checkout/pago', { payment_method_id: selectedMethodId }, {
            onFinish: () => setProcessing(false),
        });
    };

    const addressLine = [
        recipient.address,
        recipient.department,
    ].filter(Boolean).join(', ');

    const locationLine = [
        recipient.city,
        recipient.state,
        delivery.postal_code ? `CP ${delivery.postal_code}` : '',
    ].filter(Boolean).join(', ');

    const contactLine = [
        locationLine,
        recipient.phone ? `+${recipient.phone}` : '',
    ].filter(Boolean).join(' - ');

    return (
        <CheckoutLayout currentStep={4}>
            <Head title="Medios de Pago" />
            <div className="max-w-[1320px] mx-auto px-4">
                <div className="flex gap-6">
                    {/* Content */}
                    <div className="flex-1">
                        <BackButton href="/checkout/destinatario" />

                        <div className="mt-6 flex flex-col gap-6">
                            {/* Alert banner */}
                            {showAlert && (
                                <div className="bg-oxido-50 border-l-4 border-oxido-300 rounded-[8px] h-10 flex items-center justify-between px-3">
                                    <div className="flex items-center gap-2">
                                        <Info size={16} className="text-oxido-300" />
                                        <p className="text-xs font-semibold text-oxido-300">
                                            Hacemos Factura A o B. Se lo informaremos via mail.
                                        </p>
                                    </div>
                                    <button onClick={() => setShowAlert(false)}>
                                        <X size={16} className="text-oxido-300" />
                                    </button>
                                </div>
                            )}

                            {/* Review section */}
                            <div className="flex flex-col gap-4">
                                <ReviewItem
                                    icon={EnvelopeSimple}
                                    description={contact.email}
                                />
                                <ReviewItem
                                    icon={MapPin}
                                    title={addressLine}
                                    description={contactLine}
                                    changeHref="/checkout/destinatario"
                                />
                                <ReviewItem
                                    icon={Truck}
                                    title={SHIPPING_LABELS[delivery.shipping_method] || delivery.shipping_method}
                                    description="Listo entre 3-5 dias habiles"
                                    changeHref={contact.delivery_type === 'pickup' ? '/checkout/contacto' : '/checkout/entrega'}
                                />
                            </div>

                            {/* Payment methods */}
                            <div className="pt-3">
                                <h2 className="text-[32px] font-medium text-neutral-500 mb-6">Medios de Pago</h2>

                                <div className="flex flex-col gap-3">
                                    {paymentMethods.map((method) => {
                                        const isSelected = selectedMethodId === method.id;
                                        const Icon = TYPE_ICONS[method.type] || CreditCard;

                                        return (
                                            <label
                                                key={method.id}
                                                className={`flex items-center gap-4 p-4 rounded-[10px] border cursor-pointer transition-colors ${
                                                    isSelected
                                                        ? 'border-oxido-300 bg-oxido-50/30'
                                                        : 'border-neutral-300 hover:border-neutral-400'
                                                }`}
                                            >
                                                <input
                                                    type="radio"
                                                    name="payment_method"
                                                    value={method.id}
                                                    checked={isSelected}
                                                    onChange={() => setSelectedMethodId(method.id)}
                                                    className="sr-only"
                                                />
                                                <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center ${
                                                    isSelected ? 'border-oxido-300' : 'border-neutral-300'
                                                }`}>
                                                    {isSelected && <div className="w-2.5 h-2.5 rounded-full bg-oxido-300" />}
                                                </div>
                                                <Icon size={24} className="text-neutral-500 shrink-0" />
                                                <div className="flex-1">
                                                    <p className="text-sm font-semibold text-neutral-500">{method.title}</p>
                                                    {method.subtitle && (
                                                        <p className="text-sm text-neutral-400">{method.subtitle}</p>
                                                    )}
                                                </div>
                                            </label>
                                        );
                                    })}
                                </div>

                                {isCreditCard && (
                                    <p className="text-sm text-neutral-500 text-center w-full mt-4">
                                        Seras redirigido a Mercado Pago al realizar el pago.
                                    </p>
                                )}

                                <div className="flex justify-end mt-6">
                                    <button
                                        onClick={handlePlaceOrder}
                                        disabled={processing || !selectedMethodId}
                                        className="w-[280px] py-3 bg-oxido-300 text-oxido-50 font-medium text-sm rounded-[8px] hover:opacity-90 transition-opacity disabled:opacity-50"
                                    >
                                        {processing
                                            ? 'Procesando...'
                                            : isCreditCard
                                                ? 'Realizar Pago'
                                                : 'Realizar Pedido'}
                                    </button>
                                </div>
                            </div>
                        </div>
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
