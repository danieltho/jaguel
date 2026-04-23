import { Head, useForm } from '@inertiajs/react';
import CheckoutLayout from '../../Shared/components/CheckoutLayout/CheckoutLayout';
import CheckoutInput from '../../Shared/components/CheckoutLayout/CheckoutInput';
import { PrimaryButton, BackButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';
import OrderSummary from '../../Shared/components/CheckoutLayout/OrderSummary';
import {
    SectionHeading,
    RadioOption,
} from '../../Shared/components/CheckoutLayout/CheckoutPrimitives';
import { formatPrice } from '../../Shared/utils/formatPrice';

export default function Delivery({ shippingOptions, summary }) {
    const { data, setData, post, processing, errors } = useForm({
        postal_code: '',
        shipping_method: shippingOptions[0]?.id || 'punto_retiro',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/checkout/entrega');
    };

    return (
        <CheckoutLayout currentStep={2}>
            <Head title="Datos de Entrega" />
            <div className="mx-auto w-full max-w-[1320px] px-15 py-[30px]">
                <form onSubmit={handleSubmit} className="flex flex-col gap-2.5">
                    <BackButton href="/checkout/contacto" />

                    <div className="flex items-start justify-between gap-6">
                        <div className="flex w-[872px] flex-col items-end gap-4">
                            <div className="flex w-full flex-col gap-2.5">
                                <SectionHeading>Entrega</SectionHeading>

                                <div className="flex w-full items-end gap-6">
                                    <div className="w-[300px]">
                                        <CheckoutInput
                                            label="Código Postal"
                                            type="text"
                                            value={data.postal_code}
                                            onChange={(e) => setData('postal_code', e.target.value)}
                                            placeholder="7600"
                                            error={errors.postal_code}
                                        />
                                    </div>
                                    <a
                                        href="https://www.correoargentino.com.ar/formularios/cpa"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="px-5 py-2.5 text-sm font-semibold text-neutral-500 hover:underline"
                                    >
                                        No sé mi código postal
                                    </a>
                                </div>

                                {shippingOptions.map((option) => (
                                    <RadioOption
                                        key={option.id}
                                        name="shipping_method"
                                        checked={data.shipping_method === option.id}
                                        onChange={() => setData('shipping_method', option.id)}
                                        title={option.name}
                                        description={option.description || option.days}
                                        priceLabel={option.price === 0 ? 'Gratis' : formatPrice(option.price)}
                                    />
                                ))}
                                {errors.shipping_method && (
                                    <p className="pl-5 text-xs text-carmesi-300">{errors.shipping_method}</p>
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
