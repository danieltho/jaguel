export default function Breadcrumb({ items }) {
    return (
        <nav aria-label="Breadcrumb" className="flex items-center gap-2 min-w-0">
            {items.map((item, index) => {
                const isLast = index === items.length - 1;
                return (
                    <span
                        key={index}
                        className={`flex items-center gap-2 ${isLast ? 'min-w-0' : 'shrink-0'}`}
                    >
                        {index > 0 && (
                            <span className="shrink-0 text-xs font-normal text-neutral-400">/</span>
                        )}
                        {item.href && !isLast ? (
                            <a
                                href={item.href}
                                className="text-xs font-normal text-neutral-400 transition-colors hover:text-neutral-500"
                            >
                                {item.label}
                            </a>
                        ) : (
                            <span className="truncate text-sm font-semibold text-neutral-500">
                                {item.label}
                            </span>
                        )}
                    </span>
                );
            })}
        </nav>
    );
}
