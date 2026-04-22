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
            <header className="relative z-10 w-full px-15 pt-5">
                <div className="mx-auto flex h-20 max-w-360 items-center justify-between rounded-2xl bg-oxido-50 px-15 shadow-[0px_4px_10px_0px_rgba(0,0,0,0.25)]">
                    <NavBarLink>
                        <button
                            type="button"
                            onClick={openMenu}
                            aria-label="Abrir menú"
                            className="cursor-pointer text-neutral-500"
                        >
                            <ListIcon size={24} />
                        </button>
                    </NavBarLink>

                    <Logo />

                    <NavBarLink>
                        <Link
                            href="/carrito"
                            className="relative text-neutral-500"
                            aria-label="Carrito"
                        >
                            <ShoppingBagIcon size={24} />
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
