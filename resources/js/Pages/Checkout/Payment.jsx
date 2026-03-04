import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { ShieldCheck } from '@phosphor-icons/react';
import Template from '../../Shared/components/layout';
import Breadcrumb from '../../Shared/components/Breadcrumb/Breadcrumb';
import { formatPrice } from '../../Shared/utils/formatPrice';

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

export default function Payment({ summary, mercadoPagoPublicKey }) {
    const [processing, setProcessing] = useState(false);

    const breadcrumbItems = [
        { label: 'Home', href: '/' },
        { label: 'Carrito', href: '/carrito' },
        { label: 'Direccion', href: '/checkout/direccion' },
        { label: 'Envio', href: '/checkout/datos-envio' },
        { label: 'Metodo de Pago' },
    ];

    const handlePlaceOrder = () => {
        setProcessing(true);
        router.post('/checkout/pago', {}, {
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <Template>
            <Head title="Metodo de Pago" />
            <div className="max-w-[1080px] mx-auto px-4 py-6">
                <Breadcrumb items={breadcrumbItems} />

                {/* Steps indicator */}
                <div className="flex items-center gap-3 mt-6 mb-8">
                    <span className="flex items-center gap-2 text-sm text-neutral-400">
                        <span className="w-7 h-7 rounded-full bg-moss-300 text-white flex items-center justify-center text-xs font-bold">&#10003;</span>
                        Direccion
                    </span>
                    <span className="h-px flex-1 bg-neutral-200" />
                    <span className="flex items-center gap-2 text-sm text-neutral-400">
                        <span className="w-7 h-7 rounded-full bg-moss-300 text-white flex items-center justify-center text-xs font-bold">&#10003;</span>
                        Envio
                    </span>
                    <span className="h-px flex-1 bg-neutral-200" />
                    <span className="flex items-center gap-2 text-sm font-medium text-neutral-500">
                        <span className="w-7 h-7 rounded-full bg-neutral-500 text-white flex items-center justify-center text-xs font-bold">3</span>
                        Pago
                    </span>
                </div>

                <div className="flex gap-6">
                    {/* Payment */}
                    <div className="flex-1">
                        <div className="bg-white rounded-2xl p-6">
                            <h1 className="text-2xl font-extrabold text-neutral-500 mb-6">Metodo de Pago</h1>

                            {/* MercadoPago info */}
                            <div className="flex flex-col items-center text-center py-8 gap-4">
                                <div className="w-16 h-16 rounded-full bg-sky-50 flex items-center justify-center">
                                    <ShieldCheck size={32} weight="fill" className="text-sky-500" />
                                </div>
                                <h2 className="text-lg font-semibold text-neutral-500">Pago Seguro con MercadoPago</h2>
                                <p className="text-neutral-400 max-w-sm">
                                    Al hacer clic en &quot;Pagar y Finalizar&quot; seras redirigido a MercadoPago para completar tu pago de forma segura.
                                </p>
                                <div className="flex items-center gap-3 mt-2">
                                    <img
                                        src="https://http2.mlstatic.com/frontend-assets/mp-web-navigation/ui-navigation/6.6.92/mercadopago/logo__large@2x.png"
                                        alt="MercadoPago"
                                        className="h-8"
                                    />
                                </div>
                            </div>

                            <button
                                onClick={handlePlaceOrder}
                                disabled={processing}
                                className="w-full py-3.5 bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors disabled:opacity-50"
                            >
                                {processing ? 'Procesando...' : 'Pagar y Finalizar'}
                            </button>
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
