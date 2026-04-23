import { Link } from '@inertiajs/react';
import { CaretLeft } from '@phosphor-icons/react';

export function PrimaryButton({ children, disabled, className = '', ...props }) {
    return (
        <button
            disabled={disabled}
            className={`h-10 rounded-lg border border-oxido-300 bg-oxido-300 px-6 text-xs font-medium text-oxido-50 transition-opacity hover:opacity-90 disabled:opacity-50 ${className}`}
            {...props}
        >
            {children}
        </button>
    );
}

export function OutlineButton({ children, className = '', as: Component = 'button', ...props }) {
    return (
        <Component
            className={`inline-flex h-9 items-center justify-center rounded-lg border border-oxido-300 px-4 text-sm font-medium text-oxido-300 transition-colors hover:bg-oxido-50 ${className}`}
            {...props}
        >
            {children}
        </Component>
    );
}

export function BackButton({ href, onClick, children = 'Volver' }) {
    const content = (
        <>
            <CaretLeft size={16} weight="bold" />
            {children}
        </>
    );

    const className =
        'inline-flex h-9 items-center gap-2.5 rounded-lg border border-oxido-300 px-4 text-sm font-medium text-oxido-300 transition-colors hover:bg-oxido-50';

    if (onClick) {
        return (
            <button type="button" onClick={onClick} className={className}>
                {content}
            </button>
        );
    }

    return (
        <Link href={href} className={className}>
            {content}
        </Link>
    );
}
