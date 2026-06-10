import { Head, router, useForm, usePage } from '@inertiajs/react';
import { MapPin, Truck } from '@phosphor-icons/react';
import { useEffect, useState } from 'react';
import CheckoutLayout from '../../Shared/components/CheckoutLayout/CheckoutLayout';
import CheckoutInput from '../../Shared/components/CheckoutLayout/CheckoutInput';
import { PrimaryButton, BackButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';
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

const RESEND_COOLDOWN_SECONDS = 60;

export default function Contact({ customer, contact, summary }) {
    const page = usePage();
    const { flash } = page.props || {};

    const { data, setData, post, processing, errors } = useForm({
        email: contact?.email || customer.email || '',
        wants_newsletter: contact?.wants_newsletter || false,
        delivery_type: contact?.delivery_type || 'pickup',
        verification_code: '',
    });

    const [verificationRequired, setVerificationRequired] = useState(false);
    const [cooldown, setCooldown] = useState(0);

    useEffect(() => {
        // El input se muestra cuando el backend lo marca por flash O cuando
        // sigue habiendo un error de verificación (sobrevive a recargas).
        if (flash?.verification_required || errors.verification_code) {
            setVerificationRequired(true);
            if (cooldown === 0) setCooldown(RESEND_COOLDOWN_SECONDS);
        }
        if (flash?.verification_resent) {
            setCooldown(RESEND_COOLDOWN_SECONDS);
        }
    }, [flash?.verification_required, flash?.verification_resent, errors.verification_code]);

    useEffect(() => {
        if (cooldown <= 0) return;
        const timer = setTimeout(() => setCooldown(cooldown - 1), 1000);
        return () => clearTimeout(timer);
    }, [cooldown]);

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/checkout/contacto', {
            preserveScroll: true,
        });
    };

    const handleResend = () => {
        if (cooldown > 0) return;
        router.post(
            '/checkout/contacto/reenviar-codigo',
            { email: data.email },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => setCooldown(RESEND_COOLDOWN_SECONDS),
            },
        );
    };

    const handleChangeEmail = () => {
        setVerificationRequired(false);
        setData('verification_code', '');
        setCooldown(0);
    };

    return (
        <CheckoutLayout currentStep={1}>
            <Head title="Datos de Contacto" />
            <div className="mx-auto w-full max-w-[1320px] px-4 py-[30px] sm:px-8 lg:px-15">
                <form onSubmit={handleSubmit} className="flex flex-col gap-2.5">
                    <BackButton href="/carrito" />

                    <div className="flex flex-col items-start justify-between gap-6 lg:flex-row">
                        <div className="flex w-full flex-col items-end gap-4 lg:w-[872px]">
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
                                    disabled={verificationRequired}
                                />

                                {verificationRequired && (
                                    <div className="flex flex-col gap-2 rounded-md border border-amber-200 bg-amber-50 p-3">
                                        <p className="text-sm text-amber-900">
                                            Te enviamos un código de 6 dígitos a <strong>{data.email}</strong>.
                                            Revisá tu bandeja de entrada y la carpeta de spam.
                                        </p>

                                        <CheckoutInput
                                            label="Código de verificación*"
                                            type="text"
                                            inputMode="numeric"
                                            maxLength={6}
                                            value={data.verification_code}
                                            onChange={(e) =>
                                                setData(
                                                    'verification_code',
                                                    e.target.value.replace(/\D/g, '').slice(0, 6),
                                                )
                                            }
                                            placeholder="000000"
                                            error={errors.verification_code}
                                            required
                                        />

                                        <div className="flex items-center gap-3 text-sm">
                                            <button
                                                type="button"
                                                onClick={handleResend}
                                                disabled={cooldown > 0}
                                                className="text-blue-700 underline disabled:cursor-not-allowed disabled:text-gray-400"
                                            >
                                                {cooldown > 0
                                                    ? `Reenviar código en ${cooldown}s`
                                                    : 'Reenviar código'}
                                            </button>
                                            <span className="text-gray-400">·</span>
                                            <button
                                                type="button"
                                                onClick={handleChangeEmail}
                                                className="text-blue-700 underline"
                                            >
                                                Cambiar email
                                            </button>
                                        </div>
                                    </div>
                                )}

                                <Checkbox
                                    id="wants-newsletter"
                                    checked={data.wants_newsletter}
                                    onChange={(e) => setData('wants_newsletter', e.target.checked)}
                                    label="Quiero recibir ofertas y novedades por e-mail"
                                />
                            </div>

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

                            <PrimaryButton type="submit" disabled={processing} className="w-full sm:w-[280px]">
                                {verificationRequired ? 'Verificar y continuar' : 'Continuar'}
                            </PrimaryButton>
                        </div>

                        <aside className="w-full shrink-0 py-4 lg:w-[424px] lg:px-2.5">
                            <OrderSummary {...summary} />
                        </aside>
                    </div>
                </form>
            </div>
        </CheckoutLayout>
    );
}
