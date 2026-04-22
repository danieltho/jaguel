import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/react';
import { CaretDownIcon } from '@phosphor-icons/react';

const SORT_OPTIONS = [
    { value: 'newest', label: 'Orden predeterminado' },
    { value: 'price_asc', label: 'Precio: menor a mayor' },
    { value: 'price_desc', label: 'Precio: mayor a menor' },
    { value: 'name_asc', label: 'Nombre: A-Z' },
];

export default function SortDropdown({ value, onChange }) {
    const selected = SORT_OPTIONS.find((opt) => opt.value === value) ?? SORT_OPTIONS[0];

    return (
        <Listbox value={value} onChange={onChange}>
            <div className="relative">
                <ListboxButton className="flex cursor-pointer items-center gap-4 px-4 py-3 text-xs font-medium text-neutral-500">
                    <span className="w-47">{selected.label}</span>
                    <CaretDownIcon size={24} />
                </ListboxButton>

                <ListboxOptions
                    anchor="bottom end"
                    className="z-10 mt-1 w-64 rounded-xl border border-neutral-300 bg-white py-1 shadow-lg"
                >
                    {SORT_OPTIONS.map((option) => (
                        <ListboxOption
                            key={option.value}
                            value={option.value}
                            className="cursor-pointer px-4 py-2 text-xs font-medium text-neutral-500 hover:bg-neutral-50 data-[selected]:bg-neutral-100 data-[selected]:font-semibold"
                        >
                            {option.label}
                        </ListboxOption>
                    ))}
                </ListboxOptions>
            </div>
        </Listbox>
    );
}
