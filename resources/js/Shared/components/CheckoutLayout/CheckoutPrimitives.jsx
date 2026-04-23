export function SectionHeading({ children }) {
    return (
        <div className="flex w-full justify-center py-3">
            <p className="text-lg font-semibold text-neutral-500">{children}</p>
        </div>
    );
}

export function RadioOption({
    checked,
    onChange,
    title,
    description,
    icon: Icon,
    priceLabel,
    name = 'radio-option',
}) {
    return (
        <label className="flex w-full cursor-pointer items-center gap-6 px-5 py-2.5">
            <input
                type="radio"
                name={name}
                checked={checked}
                onChange={onChange}
                className="sr-only"
            />
            <span
                aria-hidden="true"
                className={`flex size-[19px] shrink-0 items-center justify-center rounded-full border-2 ${
                    checked ? 'border-moss-300' : 'border-neutral-300'
                }`}
            >
                {checked && <span className="size-2.5 rounded-full bg-moss-300" />}
            </span>
            {Icon && <Icon size={24} className="shrink-0 text-neutral-500" />}
            <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                <p className="text-sm font-semibold text-neutral-500">{title}</p>
                {description && <p className="text-xs text-neutral-500">{description}</p>}
            </div>
            {priceLabel !== undefined && (
                <p className="shrink-0 text-sm font-semibold text-neutral-500">{priceLabel}</p>
            )}
        </label>
    );
}

export function Checkbox({ checked, onChange, label, id }) {
    return (
        <label className="flex cursor-pointer items-center gap-2" htmlFor={id}>
            <input
                id={id}
                type="checkbox"
                checked={checked}
                onChange={onChange}
                className="size-4 rounded border border-neutral-500 bg-transparent accent-oxido-300"
            />
            <span className="text-xs text-neutral-500">{label}</span>
        </label>
    );
}
