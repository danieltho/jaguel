import { Link } from '@inertiajs/react';
import { CaretLeft } from '@phosphor-icons/react';

export function PrimaryButton({ children, disabled, className = '', ...props }) {
    return (
        <button
            disabled={disabled}
            className={`w-full py-3.5 bg-oxido-300 text-oxido-50 font-medium text-sm rounded-[8px]
                hover:opacity-90 transition-opacity disabled:opacity-50 ${className}`}
            {...props}
        >
            {children}
        </button>
    );
}

export function BackButton({ href, children = 'Volver' }) {
    return (
        <Link
            href={href}
            className="inline-flex items-center gap-2 px-4 py-2 border border-oxido-300 text-oxido-300 rounded-[8px] text-sm font-medium hover:bg-oxido-50 transition-colors"
        >
            <CaretLeft size={16} weight="bold" />
            {children}
        </Link>
    );
}
