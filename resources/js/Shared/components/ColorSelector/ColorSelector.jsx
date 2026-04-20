import { X } from '@phosphor-icons/react';

export default function ColorSelector({ colors, selectedId, onSelect, label = 'Color' }) {
    if (!colors?.length) return null;

    return (
        <div className="flex flex-col gap-4">
            <span className="text-base font-semibold text-neutral-500 leading-normal">
                {label}
            </span>
            <div className="flex items-center gap-2.5 flex-wrap">
                {colors.map((color) => {
                    const isSelected = color.id === selectedId;
                    const isOutOfStock = color.inStock === false;

                    const borderClass = isOutOfStock
                        ? 'border-2 border-moss-50'
                        : isSelected
                            ? 'border-2 border-success-bg-100'
                            : 'border border-transparent';

                    return (
                        <button
                            key={color.id}
                            type="button"
                            disabled={isOutOfStock}
                            onClick={() => !isOutOfStock && onSelect?.(color)}
                            title={color.name}
                            style={{ backgroundColor: color.hex || '#ccc' }}
                            className={`relative size-8 rounded-[5px] transition-all ${borderClass} ${
                                isOutOfStock ? 'cursor-not-allowed' : 'cursor-pointer'
                            }`}
                        >
                            {isOutOfStock && (
                                <X
                                    size={16}
                                    weight="bold"
                                    className="absolute inset-0 m-auto text-moss-50"
                                />
                            )}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
