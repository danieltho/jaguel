export default function TagButton({ label, href, isActive = false, onClick }) {
    const base = 'p-2.5 text-xs font-medium text-neutral-500 text-center cursor-pointer transition-colors';
    const state = isActive ? 'underline underline-offset-4' : 'hover:underline hover:underline-offset-4';
    const classes = `${base} ${state}`;

    if (href) {
        return (
            <a href={href} className={classes}>
                {label}
            </a>
        );
    }

    return (
        <button type="button" onClick={onClick} className={classes}>
            {label}
        </button>
    );
}
