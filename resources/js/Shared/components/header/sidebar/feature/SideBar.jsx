import { XIcon,MagnifyingGlassIcon,ShoppingBagIcon, UserIcon, CaretDownIcon } from '@phosphor-icons/react';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/react';
import {useSidebar} from '../../../../hook/useSidebar';
import Logo from '../../../logo/Logo';
import { useCategoryContext } from '../../../../context/CategoryContext';

export default function SideBar() {
  
  const { isMenuOpen, closeMenu } = useSidebar();
  const { categories, loading, baseUrl }  = useCategoryContext();
 
  if (!isMenuOpen) return null;

  return (
    <div className='absolute top-0 left-0 w-[405px]  rounded-r-xl  grid grid-rows-1 grid-cols-3  gap-[17px]  h-screen  z-30 bg-white'>
      <div className='bg-oxido-50 rounded-r-xl overflow-hidden  col-span-1  p-7  flex flex-col items-center  gap-6'>
        <button className='hover:cursor-pointer' onClick={closeMenu} >
          <XIcon size={24} />
        </button>
        <a href="">
          <MagnifyingGlassIcon size={24} />
        </a>
        <a href="">
          <UserIcon size={24} />
        </a>
        <a href="">
          <ShoppingBagIcon size={24} />
        </a>
      </div>
      <div className='bg-oxido-50 rounded-xl overflow-hidden  col-span-2 px-6 py-7 flex flex-col justify-start gap-6'>
        <div className='flex justify-center w-full mb-8.25'>
          <Logo/>
        </div>
        <nav className='flex flex-col gap-3'>
          <a href="#" className=''>Inicio</a>
          {loading && <p>Cargando categorías...</p>}
          {/* {categories.map((category) => (
            <a key={category.id} href={`${baseUrl}${category.path}`} className=''>{category.name}</a>
          ))} */}

          {categories.map((category) => 
            category.children ? (
              <NavItemDropdown 
                key={category.id}
                category={category}
              />
            ) : (
              <NavItem 
                key={category.id} 
                label={category.name} 
                href={`${baseUrl}${category.path}`} 
              />
            )
          )}
        </nav>
      </div>
    </div>
    
  );
};

const NavItem = ({ label, href }) => {
  return (<a href={href} className=''>{label}</a>);
};

const NavItemDropdown = ({ category }) => {
  return (
    <Disclosure>
      {({ open }) => (
        <>
          <DisclosureButton className="flex w-full justify-between hover:cursor-pointer">
            {category.name}
            <CaretDownIcon
              size={24}
              className={`transition ${open ? "rotate-180" : ""}`}
            />
          </DisclosureButton>

          <DisclosurePanel >
            {category.children.map((child, j) => (
              <a
                key={j}
                href={child.path}
                className="block px-8 py-2 hover:underline hover:cursor-pointer"
              >
                {child.name}
              </a>
            ))}
          </DisclosurePanel>
        </>
      )}
    </Disclosure>
  );
};