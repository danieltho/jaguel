export default function CheckoutInput({ error, className = '', ...props }) {
    return (
        <div>
            <input
                className={`w-full px-4 py-3 rounded-[8px] border border-neutral-300 text-neutral-500 text-sm font-sans
                    placeholder:text-neutral-300 focus:outline-none focus:border-oxido-300 transition-colors
                    ${error ? 'border-carmesi-300' : ''} ${className}`}
                {...props}
            />
            {error && <p className="text-carmesi-300 text-xs mt-1 pl-1">{error}</p>}
        </div>
    );
}
