import { Head, useForm } from '@inertiajs/react';
import CheckoutLayout from '../../Shared/components/CheckoutLayout/CheckoutLayout';
import CheckoutInput from '../../Shared/components/CheckoutLayout/CheckoutInput';
import { PrimaryButton, BackButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';
import OrderSummary from '../../Shared/components/CheckoutLayout/OrderSummary';
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
            <div className="max-w-[1320px] mx-auto px-4">
                <div className="flex gap-6">
                    {/* Form */}
                    <div className="flex-1">
                        <BackButton href="/checkout/contacto" />

                        <form onSubmit={handleSubmit} className="mt-6 flex flex-col gap-6">
                            {/* Postal code */}
                            <div>
                                <h2 className="text-[32px] font-medium text-neutral-500 mb-4">Datos de Entrega</h2>
                                <CheckoutInput
                                    type="text"
                                    value={data.postal_code}
                                    onChange={(e) => setData('postal_code', e.target.value)}
                                    placeholder="Codigo Postal"
                                    error={errors.postal_code}
                                />
                            </div>

                            {/* Shipping options */}
                            <div>
                                <h2 className="text-[32px] font-medium text-neutral-500 mb-4">Opciones de Envio</h2>
                                <div className="flex flex-col gap-3">
                                    {shippingOptions.map((option) => {
                                        const isSelected = data.shipping_method === option.id;

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
                                                    name="shipping_method"
                                                    value={option.id}
                                                    checked={isSelected}
                                                    onChange={() => setData('shipping_method', option.id)}
                                                    className="sr-only"
                                                />
                                                <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center ${
                                                    isSelected ? 'border-oxido-300' : 'border-neutral-300'
                                                }`}>
                                                    {isSelected && <div className="w-2.5 h-2.5 rounded-full bg-oxido-300" />}
                                                </div>
                                                <div className="flex-1">
                                                    <p className="text-sm font-semibold text-neutral-500">{option.name}</p>
                                                    <p className="text-sm text-neutral-400">{option.description}</p>
                                                    {option.days && (
                                                        <p className="text-xs text-neutral-300 mt-0.5">{option.days}</p>
                                                    )}
                                                </div>
                                                <span className="text-sm font-semibold text-neutral-500">
                                                    {option.price === 0 ? 'Gratis' : formatPrice(option.price)}
                                                </span>
                                            </label>
                                        );
                                    })}
                                </div>
                                {errors.shipping_method && <p className="text-carmesi-300 text-xs mt-1">{errors.shipping_method}</p>}
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
