import logoImg from './img/logo.png';

const Logo = () => {
  return (
    <a href="/">
      <img src={logoImg} className='h-[31px]' />
    </a>
  );
};

export default Logo;