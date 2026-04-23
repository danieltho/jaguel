import { Link } from '@inertiajs/react';
import {
    ShoppingCart,
    Truck,
    CreditCard,
    InstagramLogo,
    WhatsappLogo,
    ArrowsClockwise,
    House,
} from '@phosphor-icons/react';
import logoImg from '../logo/img/logo.png';

const STEPS = [
    { label: 'Carrito', icon: ShoppingCart },
    { label: 'Entrega', icon: Truck },
    { label: 'Pago', icon: CreditCard },
];

function StepIndicator({ currentStep }) {
    return (
        <div className="opacity-60 px-15 py-[30px]">
            <div className="mx-auto flex w-full max-w-[696px] items-center px-6">
                {STEPS.map((step, i) => {
                    const isActive = i + 1 === currentStep;
                    const isCompleted = i + 1 < currentStep;
                    const isHighlighted = isActive || isCompleted;
                    const Icon = step.icon;

                    return (
                        <div key={step.label} className="flex flex-1 items-center last:flex-none">
                            <div
                                className={`flex size-[46px] shrink-0 items-center justify-center rounded-full border-2 ${
                                    isHighlighted
                                        ? 'border-moss-300 text-moss-300'
                                        : 'border-moss-100 text-moss-100'
                                }`}
                                aria-current={isActive ? 'step' : undefined}
                                aria-label={step.label}
                            >
                                <Icon size={22} weight={isHighlighted ? 'regular' : 'regular'} />
                            </div>
                            {i < STEPS.length - 1 && (
                                <div
                                    className={`h-0.5 flex-1 ${
                                        isCompleted ? 'bg-moss-300' : 'bg-moss-100'
                                    }`}
                                />
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

function InfoBar() {
    const items = [
        { icon: ArrowsClockwise, title: 'CAMBIOS Y DEVOLUCIONES', body: 'Tenés 30 días para cambiar tu pedido.' },
        { icon: CreditCard, title: 'MEDIOS DE PAGO', body: 'Transferencia Bancaria o Mercado Pago.' },
        { icon: House, title: 'RETIROS', body: 'Retirá tu pedido gratis en nuestro punto.' },
        { icon: Truck, title: 'ENVÍO GRATIS', body: 'Con un monto mínimo de $80.000.' },
    ];

    return (
        <div className="px-15 py-15">
            <div className="mx-auto flex max-w-[1320px] flex-wrap items-center justify-center gap-x-[50px] gap-y-6">
                {items.map((item) => {
                    const Icon = item.icon;
                    return (
                        <div key={item.title} className="flex min-w-0 flex-1 items-center gap-4">
                            <Icon size={24} className="shrink-0 text-neutral-500" />
                            <div className="flex min-w-0 flex-col gap-[5px] text-neutral-500">
                                <p className="text-sm font-bold">{item.title}</p>
                                <p className="text-sm">{item.body}</p>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

function CheckoutFooter() {
    return (
        <footer className="px-15 py-5">
            <div className="mx-auto flex max-w-[1320px] items-center justify-between gap-4 text-moss-300">
                <p className="w-32 text-xs">© 2026 El Jaguel. All rights reserved</p>
                <p className="text-center text-xs whitespace-nowrap">
                    A product of{' '}
                    <span className="text-sm font-semibold">Ophelia Studio</span>
                </p>
                <div className="flex items-center gap-2">
                    <a
                        href="#"
                        aria-label="WhatsApp"
                        className="flex size-[35px] items-center justify-center rounded-full border border-moss-300 transition-colors hover:bg-moss-300/10"
                    >
                        <WhatsappLogo size={18} />
                    </a>
                    <a
                        href="#"
                        aria-label="Instagram"
                        className="flex size-[35px] items-center justify-center rounded-full border border-moss-300 transition-colors hover:bg-moss-300/10"
                    >
                        <InstagramLogo size={18} />
                    </a>
                </div>
            </div>
        </footer>
    );
}

export default function CheckoutLayout({ children, currentStep = 1, showInfoBar = true }) {
    return (
        <div className="flex min-h-screen flex-col bg-neutral-50">
            <header className="flex justify-center px-15 py-[30px]">
                <Link href="/" aria-label="El Jaguel">
                    <img src={logoImg} className="h-[54px] w-auto object-contain" alt="El Jaguel" />
                </Link>
            </header>

            <StepIndicator currentStep={currentStep} />

            <main className="flex-1 pb-10">{children}</main>

            {showInfoBar && <InfoBar />}

            <CheckoutFooter />
        </div>
    );
}
