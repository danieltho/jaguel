export default function TagButton({ label, href, isActive = false, onClick }) {
    const base = 'px-4 py-2 rounded-full text-sm font-medium transition-colors cursor-pointer';
    const active = 'bg-moss-300 text-oxido-50';
    const inactive = 'bg-moss-50 text-moss-300 hover:bg-moss-200';
    const classes = `${base} ${isActive ? active : inactive}`;

    if (href) {
        return (
            <a href={href} className={classes}>
                {label}
            </a>
        );
    }

    return (
        <button onClick={onClick} className={classes}>
            {label}
        </button>
    );
}
