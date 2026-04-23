import { Head, Link } from '@inertiajs/react';
import logoImg from '../../Shared/components/logo/img/logo.png';
import { OutlineButton } from '../../Shared/components/CheckoutLayout/CheckoutButton';

const STATUS_CONFIG = {
    approved: {
        title: '¡Gracias por su compra!',
        message: 'Su orden fue confirmada, recibirá novedades del estado de su compra por vía mail.',
    },
    pending: {
        title: '¡Gracias por su compra!',
        message: 'Su pago está siendo procesado. Recibirá novedades del estado de su compra por vía mail.',
    },
    rejected: {
        title: 'Pago Rechazado',
        message: 'No pudimos procesar tu pago. Por favor, intentá nuevamente o usá otro medio de pago.',
    },
    cancelled: {
        title: 'Compra Cancelada',
        message: 'Tu compra fue cancelada. Tu carrito sigue disponible para cuando quieras reintentar.',
    },
};

export default function Result({ status, orderId, paymentMethod }) {
    const config = STATUS_CONFIG[status] || STATUS_CONFIG.pending;
    const showRetry = status === 'rejected' || status === 'cancelled';

    return (
        <>
            <Head title={config.title} />
            <div className="flex min-h-screen flex-col bg-neutral-50">
                <header className="flex justify-center px-15 py-[30px]">
                    <Link href="/" aria-label="El Jaguel">
                        <img src={logoImg} className="h-[54px] w-auto object-contain" alt="El Jaguel" />
                    </Link>
                </header>

                <main className="flex flex-1 items-center justify-center px-4">
                    <div className="flex flex-col items-center gap-4">
                        <div className="flex flex-col items-center gap-2.5 px-4 py-[30px]">
                            <h1 className="text-center text-lg font-semibold text-neutral-500">
                                {config.title}
                            </h1>
                            <p className="max-w-[338px] text-center text-xs text-neutral-500">
                                {config.message}
                            </p>
                            {orderId && (
                                <p className="text-xs text-neutral-400">Orden #{orderId}</p>
                            )}
                        </div>

                        {paymentMethod?.description && status === 'pending' && (
                            <div className="max-w-md rounded-lg border border-neutral-200 bg-white p-4 text-left">
                                <p className="mb-2 text-sm font-semibold text-neutral-500">
                                    {paymentMethod.title}
                                </p>
                                <p className="whitespace-pre-line text-sm text-neutral-500">
                                    {paymentMethod.description}
                                </p>
                            </div>
                        )}

                        <div className="flex items-center gap-3">
                            <OutlineButton as={Link} href="/">
                                Volver al Inicio
                            </OutlineButton>
                            {showRetry && (
                                <Link
                                    href="/checkout/pago"
                                    className="inline-flex h-10 items-center justify-center rounded-lg border border-oxido-300 bg-oxido-300 px-6 text-xs font-medium text-oxido-50 transition-opacity hover:opacity-90"
                                >
                                    Intentar de Nuevo
                                </Link>
                            )}
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
