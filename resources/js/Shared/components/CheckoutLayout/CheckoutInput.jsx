import { useId } from 'react';

export default function CheckoutInput({ label, error, className = '', id, ...props }) {
    const autoId = useId();
    const inputId = id || autoId;

    return (
        <div className="flex w-full flex-col gap-2.5">
            {label && (
                <label htmlFor={inputId} className="text-xs text-neutral-500">
                    {label}
                </label>
            )}
            <input
                id={inputId}
                className={`h-14 w-full rounded-lg border bg-neutral-100 px-4 py-3 text-xs text-neutral-500 placeholder:text-neutral-300 focus:border-oxido-300 focus:outline-none transition-colors ${
                    error ? 'border-carmesi-300' : 'border-neutral-300'
                } ${className}`}
                {...props}
            />
            {error && <p className="pl-1 text-xs text-carmesi-300">{error}</p>}
        </div>
    );
}
