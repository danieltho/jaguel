import { Head, Link } from '@inertiajs/react';
import { CheckCircle, XCircle, Clock } from '@phosphor-icons/react';
import Template from '../../Shared/components/layout';

const STATUS_CONFIG = {
    approved: {
        icon: CheckCircle,
        iconColor: 'text-moss-300',
        iconBg: 'bg-moss-50',
        title: 'Pago Aprobado',
        message: 'Tu pedido fue procesado exitosamente. Recibirás un email con los detalles de tu compra.',
    },
    pending: {
        icon: Clock,
        iconColor: 'text-amber-500',
        iconBg: 'bg-amber-50',
        title: 'Pago Pendiente',
        message: 'Tu pago esta siendo procesado. Te notificaremos cuando se confirme.',
    },
    rejected: {
        icon: XCircle,
        iconColor: 'text-carmesi-300',
        iconBg: 'bg-carmesi-50',
        title: 'Pago Rechazado',
        message: 'No pudimos procesar tu pago. Por favor, intenta nuevamente o usa otro metodo de pago.',
    },
};

export default function Result({ status, orderId }) {
    const config = STATUS_CONFIG[status] || STATUS_CONFIG.pending;
    const Icon = config.icon;

    return (
        <Template>
            <Head title={config.title} />
            <div className="max-w-[600px] mx-auto px-4 py-16">
                <div className="bg-white rounded-2xl p-10 flex flex-col items-center text-center gap-5">
                    <div className={`w-20 h-20 rounded-full ${config.iconBg} flex items-center justify-center`}>
                        <Icon size={40} weight="fill" className={config.iconColor} />
                    </div>

                    <h1 className="text-2xl font-extrabold text-neutral-500">{config.title}</h1>

                    {orderId && (
                        <p className="text-sm text-neutral-400">
                            Pedido #{orderId}
                        </p>
                    )}

                    <p className="text-neutral-400 max-w-md">{config.message}</p>

                    <div className="flex gap-3 mt-4">
                        <Link
                            href="/"
                            className="px-8 py-3 bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors"
                        >
                            Volver al Inicio
                        </Link>
                        <Link
                            href="/productos"
                            className="px-8 py-3 bg-neutral-100 text-neutral-500 font-medium rounded-full hover:bg-neutral-200 transition-colors"
                        >
                            Seguir Comprando
                        </Link>
                    </div>
                </div>
            </div>
        </Template>
    );
}
