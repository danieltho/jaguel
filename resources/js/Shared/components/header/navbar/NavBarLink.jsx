const NavBarLink = ({children}) => {
  return (
    <nav className='
      flex
      gap-6
      items-center
    '>
      {children}
    </nav>
  );
};

export default NavBarLink;