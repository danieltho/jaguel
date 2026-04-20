export default function SizeSelector({ sizes, selectedId, onSelect, label = 'Talle' }) {
    if (!sizes?.length) return null;

    return (
        <div className="flex flex-col gap-4">
            <span className="text-base font-semibold text-neutral-500 leading-normal">
                {label}
            </span>
            <div className="flex items-center gap-2.5 flex-wrap">
                {sizes.map((size) => {
                    const isSelected = size.id === selectedId;
                    const isOutOfStock = size.inStock === false;

                    const base =
                        'size-8 rounded-md flex items-center justify-center text-sm font-medium leading-normal transition-colors';
                    const stateClass = isOutOfStock
                        ? 'bg-neutral-200 text-neutral-300 cursor-not-allowed'
                        : 'bg-oxido-50 text-neutral-500 cursor-pointer hover:border hover:border-moss-300';
                    const selectedClass =
                        isSelected && !isOutOfStock ? 'border-2 border-moss-300' : '';

                    return (
                        <button
                            key={size.id}
                            type="button"
                            disabled={isOutOfStock}
                            onClick={() => !isOutOfStock && onSelect?.(size)}
                            className={`${base} ${stateClass} ${selectedClass}`.trim()}
                        >
                            {size.name.toUpperCase()}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
