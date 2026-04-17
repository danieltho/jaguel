import { Head, Link } from '@inertiajs/react';
import logoImg from '../../Shared/components/logo/img/logo.png';

const STATUS_CONFIG = {
    approved: {
        title: '!Gracias por su compra!',
        message: 'Su orden fue confirmada, recibira novedades del estado de su compra por via mail.',
    },
    pending: {
        title: '!Gracias por su compra!',
        message: 'Su pago esta siendo procesado. Recibira novedades del estado de su compra por via mail.',
    },
    rejected: {
        title: 'Pago Rechazado',
        message: 'No pudimos procesar tu pago. Por favor, intenta nuevamente o usa otro metodo de pago.',
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
            <div className="min-h-screen bg-neutral-50 flex flex-col">
                {/* Logo header */}
                <header className="w-full py-6 flex justify-center">
                    <Link href="/">
                        <img src={logoImg} className="h-[31px]" alt="El Jaguel" />
                    </Link>
                </header>

                {/* Centered content */}
                <div className="flex-1 flex items-center justify-center">
                    <div className="text-center max-w-md px-4">
                        <h1 className="text-[32px] font-medium text-neutral-500">
                            {config.title}
                        </h1>

                        {orderId && (
                            <p className="text-sm text-neutral-400 mt-2">
                                Orden #{orderId}
                            </p>
                        )}

                        <p className="text-sm text-neutral-500 mt-4">
                            {config.message}
                        </p>

                        {paymentMethod?.description && status === 'pending' && (
                            <div className="mt-6 p-4 bg-white border border-neutral-200 rounded-[8px] text-left">
                                <p className="text-sm font-semibold text-neutral-500 mb-2">{paymentMethod.title}</p>
                                <p className="text-sm text-neutral-500 whitespace-pre-line">{paymentMethod.description}</p>
                            </div>
                        )}

                        <Link
                            href="/"
                            className="inline-block mt-8 px-6 py-2.5 border border-oxido-300 text-oxido-300 rounded-[8px] text-sm font-medium hover:bg-oxido-50 transition-colors"
                        >
                            Volver al Inicio
                        </Link>

                        {showRetry && (
                            <Link
                                href="/checkout/pago"
                                className="inline-block mt-3 ml-3 px-6 py-2.5 bg-oxido-300 text-oxido-50 rounded-[8px] text-sm font-medium hover:opacity-90 transition-opacity"
                            >
                                Intentar de Nuevo
                            </Link>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
