import { useState } from 'react';
import {
    XIcon,
    MagnifyingGlassIcon,
    ShoppingBagIcon,
    CaretDownIcon,
} from '@phosphor-icons/react';
import { Link, usePage } from '@inertiajs/react';
import { useSidebar } from '../../../../hook/useSidebar';
import Logo from '../../../logo/Logo';
import { useCategoryContext } from '../../../../context/CategoryContext';

export default function SideBar() {
    const { isMenuOpen, closeMenu } = useSidebar();
    const { categories, loading, baseUrl } = useCategoryContext();
    const { customer } = usePage().props;
    const [openCategoryId, setOpenCategoryId] = useState(null);

    if (!isMenuOpen) return null;

    const toggleCategory = (id) => {
        setOpenCategoryId((current) => (current === id ? null : id));
    };

    return (
        <div className="fixed top-0 left-0 z-30 flex h-screen items-start gap-4.25 p-4.25">
            <aside className="flex h-full w-20 flex-col items-start gap-6 rounded-xl bg-oxido-50 p-7">
                <button
                    type="button"
                    onClick={closeMenu}
                    aria-label="Cerrar menú"
                    className="cursor-pointer text-neutral-500"
                >
                    <XIcon size={24} />
                </button>
                <Link
                    href="/carrito"
                    onClick={closeMenu}
                    aria-label="Carrito"
                    className="text-neutral-500"
                >
                    <ShoppingBagIcon size={24} />
                </Link>
            </aside>

            <aside className="flex h-full w-75 flex-col justify-between rounded-xl bg-oxido-50 px-6 py-7">
                <div className="flex flex-col items-center gap-8.25">
                    <Logo />

                    <nav className="flex w-full flex-col">
                        <Link
                            href="/buscar"
                            onClick={closeMenu}
                            aria-label="Buscar"
                            className="flex items-center rounded-[10px] px-2 py-2.5 text-neutral-500"
                        >
                            <MagnifyingGlassIcon size={24} />
                        </Link>

                        <NavItem href="/" label="Inicio" onNavigate={closeMenu} />

                        {loading && (
                            <p className="px-4 py-3 text-xs font-medium text-neutral-400">
                                Cargando categorías…
                            </p>
                        )}

                        {categories.map((category) =>
                            category.children?.length ? (
                                <NavDropdown
                                    key={category.id}
                                    category={category}
                                    baseUrl={baseUrl}
                                    isOpen={openCategoryId === category.id}
                                    onToggle={() => toggleCategory(category.id)}
                                    onNavigate={closeMenu}
                                />
                            ) : (
                                <NavItem
                                    key={category.id}
                                    href={`${baseUrl}${category.path}`}
                                    label={category.name}
                                    onNavigate={closeMenu}
                                />
                            )
                        )}
                    </nav>
                </div>

                {!customer && (
                    <div className="flex w-full flex-col">
                        <NavItem
                            href="/cuenta/login"
                            label="Iniciar Sesión"
                            onNavigate={closeMenu}
                        />
                        <NavItem
                            href="/cuenta/registro"
                            label="Crear Cuenta"
                            onNavigate={closeMenu}
                        />
                    </div>
                )}
            </aside>
        </div>
    );
}

function NavItem({ href, label, onNavigate }) {
    return (
        <Link
            href={href}
            onClick={onNavigate}
            className="flex h-12 items-center px-4 py-3 text-xs font-medium text-neutral-500"
        >
            {label}
        </Link>
    );
}

function NavDropdown({ category, baseUrl, isOpen, onToggle, onNavigate }) {
    const panelId = `sidebar-category-${category.id}`;

    return (
        <div className="flex w-full flex-col gap-1">
            <button
                type="button"
                onClick={onToggle}
                aria-expanded={isOpen}
                aria-controls={panelId}
                className="flex h-11 w-full cursor-pointer items-center justify-between rounded-lg px-4 text-xs font-medium text-neutral-500"
            >
                <span>{category.name}</span>
                <CaretDownIcon
                    size={24}
                    className={`transition-transform ${isOpen ? 'rotate-180' : ''}`}
                />
            </button>

            {isOpen && (
                <div id={panelId} className="flex flex-col">
                    {category.children.map((child) => (
                        <Link
                            key={child.id}
                            href={`${baseUrl}${child.path}`}
                            onClick={onNavigate}
                            className="flex h-11 items-center px-4 text-xs font-medium text-neutral-400"
                        >
                            {child.name}
                        </Link>
                    ))}
                </div>
            )}
        </div>
    );
}
