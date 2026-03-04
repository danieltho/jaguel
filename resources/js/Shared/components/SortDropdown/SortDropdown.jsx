import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/react';
import { CaretDownIcon } from '@phosphor-icons/react';

const SORT_OPTIONS = [
    { value: 'newest', label: 'Más nuevos' },
    { value: 'price_asc', label: 'Precio: menor a mayor' },
    { value: 'price_desc', label: 'Precio: mayor a menor' },
    { value: 'name_asc', label: 'Nombre: A-Z' },
];

export default function SortDropdown({ value, onChange }) {
    const selected = SORT_OPTIONS.find(opt => opt.value === value) ?? SORT_OPTIONS[0];

    return (
        <Listbox value={value} onChange={onChange}>
            <div className="relative">
                <ListboxButton className="flex items-center gap-2 border border-neutral-300 rounded-xl px-4 py-2.5 text-sm text-neutral-500 bg-white hover:bg-neutral-50 transition-colors cursor-pointer">
                    <span>{selected.label}</span>
                    <CaretDownIcon size={16} />
                </ListboxButton>

                <ListboxOptions className="absolute right-0 mt-1 w-56 bg-white border border-neutral-300 rounded-xl shadow-lg z-10 py-1">
                    {SORT_OPTIONS.map((option) => (
                        <ListboxOption
                            key={option.value}
                            value={option.value}
                            className="px-4 py-2 text-sm text-neutral-500 cursor-pointer hover:bg-neutral-50 data-[selected]:font-semibold data-[selected]:bg-neutral-100"
                        >
                            {option.label}
                        </ListboxOption>
                    ))}
                </ListboxOptions>
            </div>
        </Listbox>
    );
}
