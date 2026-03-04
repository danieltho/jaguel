export default function Breadcrumb({ items }) {
    return (
        <nav aria-label="Breadcrumb" className="flex items-center gap-2 text-sm">
            {items.map((item, index) => (
                <span key={index} className="flex items-center gap-2">
                    {index > 0 && <span className="text-neutral-400">/</span>}
                    {item.href && index < items.length - 1 ? (
                        <a href={item.href} className="text-neutral-400 hover:text-neutral-500 transition-colors">
                            {item.label}
                        </a>
                    ) : (
                        <span className="text-neutral-500 font-medium">{item.label}</span>
                    )}
                </span>
            ))}
        </nav>
    );
}
