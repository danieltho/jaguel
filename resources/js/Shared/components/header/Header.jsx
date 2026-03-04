import { Link, usePage } from '@inertiajs/react';
import NavBarLink from './navbar/NavBarLink';
import { ListIcon, ShoppingBagIcon, UserIcon, MagnifyingGlassIcon } from '@phosphor-icons/react';
import SideBar from './sidebar/feature/SideBar';
import { useSidebar } from '../../hook/useSidebar';
import Logo from '../logo/Logo';
import MenuOverlay from './MenuOverlay';

const Header = ({ isHomePage = true }) => {
    const { openMenu } = useSidebar();
    const { cartCount, customer } = usePage().props;
    const styled = isHomePage ? 'absolute z-10 w-full h-[80px] mt-[20px] px-15' : 'z-10 w-full h-[80px]';

    return (
        <>
            <MenuOverlay />
            <SideBar />
            <header className={styled}>
                <div className="px-15 rounded-xl bg-oxido-50 flex justify-between items-center h-full w-full mx-auto">
                    <NavBarLink>
                        <a href="#" onClick={openMenu}>
                            <ListIcon size={24} />
                        </a>
                        <Link href="/buscar">
                            <MagnifyingGlassIcon size={24} />
                        </Link>
                    </NavBarLink>

                    <Logo />

                    <NavBarLink>
                        <Link href={customer ? '/cuenta' : '/cuenta/login'}>
                            <UserIcon size={24} />
                        </Link>
                        <Link href="/carrito" className="relative">
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
