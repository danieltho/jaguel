import { Link, usePage } from '@inertiajs/react';
import { ListIcon, ShoppingBagIcon } from '@phosphor-icons/react';
import NavBarLink from './navbar/NavBarLink';
import SideBar from './sidebar/feature/SideBar';
import MenuOverlay from './MenuOverlay';
import { useSidebar } from '../../hook/useSidebar';
import Logo from '../logo/Logo';

const Header = () => {
    const { openMenu } = useSidebar();
    const { cartCount } = usePage().props;

    return (
        <>
            <MenuOverlay />
            <SideBar />
            <header className="relative z-10 w-full px-4 pt-4 sm:px-8 sm:pt-5 lg:px-15">
                <div className="mx-auto flex h-16 items-center justify-between rounded-2xl bg-oxido-50 px-5 shadow-[0px_4px_10px_0px_rgba(0,0,0,0.25)] sm:h-20 sm:px-10 lg:px-15">
                    <NavBarLink>
                        <button
                            type="button"
                            onClick={openMenu}
                            aria-label="Abrir menú"
                            className="cursor-pointer text-neutral-500"
                        >
                            <ListIcon size={28} className="sm:hidden" />
                            <ListIcon size={24} className="hidden sm:block" />
                        </button>
                    </NavBarLink>

                    <Logo />

                    <NavBarLink>
                        <Link
                            href="/carrito"
                            className="relative text-neutral-500"
                            aria-label="Carrito"
                        >
                            <ShoppingBagIcon size={28} className="sm:hidden" />
                            <ShoppingBagIcon size={24} className="hidden sm:block" />
                            {cartCount > 0 && (
                                <span className="absolute -top-1.5 -right-1.5 bg-carmesi-300 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                                    {cartCount > 99 ? '99+' : cartCount}
                                </span>
                            )}
                        </Link>
                    </NavBarLink>
                </div>
            </header>
        </>
    );
};

export default Header;
