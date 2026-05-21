const Logo = ({ variant = 'default' }) => {
  const src = variant === 'white' ? '/images/logo-white.svg' : '/images/logo.svg';

  return (
    <a href="/">
      <img src={src} className='h-9 sm:h-7.75' alt="El Jaguel" />
    </a>
  );
};

export default Logo;