import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/react';
import { CaretDownIcon } from '@phosphor-icons/react';
import { router } from '@inertiajs/react';

/**
 * Selector desplegable de categorías para mobile.
 * Recibe una lista de opciones { label, href, isActive } y al elegir una
 * navega a su href con Inertia. La opción activa se muestra como valor actual.
 */
export default function CategorySelect({ options }) {
    const selected = options.find((opt) => opt.isActive) ?? options[0];

    return (
        <Listbox
            value={selected?.href ?? null}
            onChange={(href) => {
                if (href) router.get(href);
            }}
        >
            <div className="relative w-full">
                <ListboxButton className="flex w-full cursor-pointer items-center justify-between gap-4 rounded-lg border border-neutral-300 px-4 py-3 text-xs font-medium text-neutral-500">
                    <span className="truncate">{selected?.label}</span>
                    <CaretDownIcon size={20} className="shrink-0" />
                </ListboxButton>

                <ListboxOptions
                    anchor="bottom"
                    className="z-10 mt-1 w-[var(--button-width)] rounded-xl border border-neutral-300 bg-white py-1 shadow-lg"
                >
                    {options.map((option) => (
                        <ListboxOption
                            key={option.href}
                            value={option.href}
                            className={`cursor-pointer px-4 py-2 text-xs font-medium text-neutral-500 hover:bg-neutral-50 ${
                                option.isActive ? 'bg-neutral-100 font-semibold' : ''
                            }`}
                        >
                            {option.label}
                        </ListboxOption>
                    ))}
                </ListboxOptions>
            </div>
        </Listbox>
    );
}
