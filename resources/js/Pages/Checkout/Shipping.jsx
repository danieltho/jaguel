import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Package, Truck, Lightning } from '@phosphor-icons/react';
import Template from '../../Shared/components/layout';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import { formatPrice } from '../../Shared/utils/formatPrice';

const ICONS = {
    free: Package,
    standard: Truck,
    express: Lightning,
};

function CheckoutSummary({ summary }) {
    return (
        <div className="bg-white rounded-2xl p-6 sticky top-6">
            <h2 className="text-lg font-semibold text-neutral-500 mb-4">Resumen de Compra</h2>
            <div className="flex flex-col gap-3 text-base">
                <div className="flex justify-between">
                    <span className="text-neutral-400">Subtotal</span>
                    <span className="text-neutral-500 font-medium">{formatPrice(summary.subtotal)}</span>
                </div>
                {summary.discount > 0 && (
                    <div className="flex justify-between">
                        <span className="text-neutral-400">Descuento</span>
                        <span className="text-carmesi-300 font-medium">-{formatPrice(summary.discount)}</span>
                    </div>
                )}
                <div className="flex justify-between">
                    <span className="text-neutral-400">Envio</span>
                    <span className="text-neutral-500 font-medium">
                        {summary.shipping > 0 ? formatPrice(summary.shipping) : 'Gratis'}
                    </span>
                </div>
                <hr className="border-neutral-100" />
                <div className="flex justify-between text-lg font-semibold">
                    <span className="text-neutral-500">Total</span>
                    <span className="text-neutral-500">{formatPrice(summary.total)}</span>
                </div>
            </div>
        </div>
    );
}

export default function Shipping({ shippingOptions, summary }) {
    const [selected, setSelected] = useState(shippingOptions[0]?.id || '');
    const [processing, setProcessing] = useState(false);

    const breadcrumbItems = [
        { label: 'Home', href: '/' },
        { label: 'Carrito', href: '/carrito' },
        { label: 'Direccion', href: '/checkout/direccion' },
        { label: 'Datos de Envio' },
    ];

    const handleSubmit = (e) => {
        e.preventDefault();
        setProcessing(true);
        router.post('/checkout/datos-envio', { shipping_option: selected }, {
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <Template>
            <Head title="Datos de Envio" />
            <div className="max-w-[1080px] mx-auto px-4 py-6">
                <Breadcrumb items={breadcrumbItems} />

                {/* Steps indicator */}
                <div className="flex items-center gap-3 mt-6 mb-8">
                    <span className="flex items-center gap-2 text-sm text-neutral-400">
                        <span className="w-7 h-7 rounded-full bg-moss-300 text-white flex items-center justify-center text-xs font-bold">&#10003;</span>
                        Direccion
                    </span>
                    <span className="h-px flex-1 bg-neutral-200" />
                    <span className="flex items-center gap-2 text-sm font-medium text-neutral-500">
                        <span className="w-7 h-7 rounded-full bg-neutral-500 text-white flex items-center justify-center text-xs font-bold">2</span>
                        Envio
                    </span>
                    <span className="h-px flex-1 bg-neutral-200" />
                    <span className="flex items-center gap-2 text-sm text-neutral-300">
                        <span className="w-7 h-7 rounded-full bg-neutral-200 text-neutral-400 flex items-center justify-center text-xs font-bold">3</span>
                        Pago
                    </span>
                </div>

                <div className="flex gap-6">
                    {/* Options */}
                    <div className="flex-1">
                        <div className="bg-white rounded-2xl p-6">
                            <h1 className="text-2xl font-extrabold text-neutral-500 mb-6">Metodo de Envio</h1>

                            <form onSubmit={handleSubmit} className="flex flex-col gap-3">
                                {shippingOptions.map((option) => {
                                    const Icon = ICONS[option.id] || Package;
                                    const isSelected = selected === option.id;

                                    return (
                                        <label
                                            key={option.id}
                                            className={`flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-colors ${
                                                isSelected
                                                    ? 'border-neutral-500 bg-neutral-50'
                                                    : 'border-neutral-100 hover:border-neutral-200'
                                            }`}
                                        >
                                            <input
                                                type="radio"
                                                name="shipping_option"
                                                value={option.id}
                                                checked={isSelected}
                                                onChange={() => setSelected(option.id)}
                                                className="sr-only"
                                            />
                                            <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                                                isSelected ? 'bg-neutral-500 text-white' : 'bg-neutral-100 text-neutral-400'
                                            }`}>
                                                <Icon size={20} weight="bold" />
                                            </div>
                                            <div className="flex-1">
                                                <p className="text-base font-semibold text-neutral-500">{option.name}</p>
                                                <p className="text-sm text-neutral-400">{option.days}</p>
                                            </div>
                                            <span className="text-base font-semibold text-neutral-500">
                                                {option.price === 0 ? 'Gratis' : formatPrice(option.price)}
                                            </span>
                                        </label>
                                    );
                                })}

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full mt-4 py-3.5 bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors disabled:opacity-50"
                                >
                                    Continuar
                                </button>
                            </form>
                        </div>
                    </div>

                    {/* Summary */}
                    <div className="w-[340px] shrink-0 hidden lg:block">
                        <CheckoutSummary summary={summary} />
                    </div>
                </div>
            </div>
        </Template>
    );
}
