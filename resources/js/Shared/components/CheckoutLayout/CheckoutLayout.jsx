import { Link } from '@inertiajs/react';
import { ShoppingCart, Truck, CreditCard, Check } from '@phosphor-icons/react';
import logoImg from '../logo/img/logo.png';

const STEPS = [
    { label: 'Carrito', icon: ShoppingCart, coversSteps: [1, 2] },
    { label: 'Entrega', icon: Truck, coversSteps: [3] },
    { label: 'Pago', icon: CreditCard, coversSteps: [4] },
];

function StepIndicator({ step, currentStep, isLast }) {
    const isActive = step.coversSteps.includes(currentStep);
    const isCompleted = step.coversSteps.every((s) => s < currentStep);
    const isFuture = !isActive && !isCompleted;

    const Icon = isCompleted ? Check : step.icon;

    let circleClasses = 'w-[46px] h-[46px] rounded-full border-2 flex items-center justify-center shrink-0';
    if (isCompleted) {
        circleClasses += ' border-moss-300 bg-moss-300 text-white';
    } else if (isActive) {
        circleClasses += ' border-moss-300 text-moss-300';
    } else {
        circleClasses += ' border-neutral-300 text-neutral-300 opacity-40';
    }

    let lineClasses = 'flex-1 h-[2px]';
    if (isCompleted) {
        lineClasses += ' bg-moss-300';
    } else {
        lineClasses += ' bg-neutral-300 opacity-40';
    }

    return (
        <>
            <div className="flex flex-col items-center gap-1">
                <div className={circleClasses}>
                    <Icon size={22} weight={isCompleted ? 'bold' : 'regular'} />
                </div>
            </div>
            {!isLast && <div className={lineClasses} />}
        </>
    );
}

export default function CheckoutLayout({ children, currentStep = 1 }) {
    return (
        <div className="min-h-screen bg-neutral-50">
            {/* Logo header */}
            <header className="w-full py-6 flex justify-center">
                <Link href="/">
                    <img src={logoImg} className="h-[31px]" alt="El Jaguel" />
                </Link>
            </header>

            {/* Step indicators */}
            <div className="max-w-[500px] mx-auto px-4 pb-6">
                <div className="flex items-center gap-3">
                    {STEPS.map((step, i) => (
                        <StepIndicator
                            key={step.label}
                            step={step}
                            currentStep={currentStep}
                            isLast={i === STEPS.length - 1}
                        />
                    ))}
                </div>
            </div>

            {/* Content */}
            <main className="pb-12">
                {children}
            </main>
        </div>
    );
}
