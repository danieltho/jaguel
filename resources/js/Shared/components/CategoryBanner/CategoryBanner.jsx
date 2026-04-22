export default function CategoryBanner({ group }) {
    return (
        <a
            href={`/productos/${group.slug}`}
            className="relative flex h-78 flex-1 flex-col items-start justify-end overflow-hidden p-6 group"
        >
            {group.image ? (
                <img
                    src={group.image}
                    alt=""
                    aria-hidden="true"
                    className="pointer-events-none absolute inset-0 size-full object-cover transition-transform duration-300 group-hover:scale-105"
                    loading="lazy"
                />
            ) : (
                <div className="absolute inset-0 bg-neutral-100" />
            )}
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 bg-linear-to-b from-black/10 to-black/60"
            />
            <span className="relative text-lg font-semibold uppercase text-oxido-50 underline">
                {group.name}
            </span>
        </a>
    );
}
