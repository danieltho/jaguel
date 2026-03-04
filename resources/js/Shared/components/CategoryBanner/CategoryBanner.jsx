export default function CategoryBanner({ group }) {
    return (
        <a
            href={`/productos/${group.slug}`}
            className="relative flex-1 h-[280px] rounded-[20px] overflow-hidden block group"
        >
            {group.image ? (
                <img
                    src={group.image}
                    alt={group.name}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    loading="lazy"
                />
            ) : (
                <div className="w-full h-full bg-neutral-100" />
            )}
            <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-black/10 flex items-end p-6">
                <span className="text-oxido-50 font-medium text-2xl">{group.name}</span>
            </div>
        </a>
    );
}
