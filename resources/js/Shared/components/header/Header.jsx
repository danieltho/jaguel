import { Link, usePage } from '@inertiajs/react';
import NavBarLink from './navbar/NavBarLink';
import { ListIcon, ShoppingBagIcon } from '@phosphor-icons/react';
import SideBar from './sidebar/feature/SideBar';
import { useSidebar } from '../../hook/useSidebar';
import Logo from '../logo/Logo';
import MenuOverlay from './MenuOverlay';

const Header = ({ isHomePage = true }) => {
    const { openMenu } = useSidebar();
    const { cartCount, customer } = usePage().props;
    const styled = isHomePage ? 'h-[80px] px-15' : 'w-full h-[80px]';
    const styledSection = isHomePage ? 'px-[60px] h-[100px] bg-red' : '';

    return (
        <>
            <MenuOverlay />
            <SideBar />
            <div className={styledSection}>
                <header className={styled}>
                    <div className="px-15 rounded-2xl bg-oxido-50 flex justify-between items-center h-full w-full mx-auto shadow-[0px_4px_10px_0px_rgba(0,0,0,0.25)]">
                        <NavBarLink>
                            <a href="#" onClick={openMenu}>
                                <ListIcon size={24} />
                            </a>
                        </NavBarLink>

                        <Logo />

                        <NavBarLink>
                            {/*<Link href={customer ? '/cuenta' : '/cuenta/login'}>
                                <UserIcon size={24} />
                            </Link>*/}
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
            </div>
        </>
    );
};

export default Header;
